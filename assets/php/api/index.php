<?php
/**
 * API realization for Work-plan
 * 
 */
if (!defined('PUBLIC_AJAX_MODE')) {
    define('PUBLIC_AJAX_MODE', true);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require(__DIR__ . '/classes/chlbt.php');
require(__DIR__ . '/classes/import.php');

define("WP_GROUP",  "WP_PLANNING");
define("WP_GROUPMOD", "WP_PLANNINGMOD");

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$q = $request["q"];
$params = isset($request["params"]) ? $request["params"] : false;
$data = false;
$valid = true;
switch ($q){
    case "auth":
        $data = doauth( $params );
        break;
    case "acts":
        $data = acts( $params );
        break;
    case "reds":
        $data = reds( $params );
        break;
    case "divisions":
        $data = divisions( $params );
        break;
    case "users":
        $data = users( $params );
        break;
    case "staffing":
        $data = staffing( $params );
        break;
    case "employees":
        $data = employees( $params );
        break;
    case "places":
        $data = places( $params );
        break;
    case "user":
        $data = user();
        break;
    case "imp":
        $data = imp( $params );
        break;
    case "ping":
        $data = array("pong" => (new DateTime())->getTimestamp() );
        break;
    case "publish":
        $data = do_publish( $params );
        break;    
    case "report":
        require './exp-doc.php';
        break;
        
    default:
        $valid = false;
}   //switch ($q...

header('Content-Type: text/json; charset=UTF-8');
if ( 
        ($valid)
     && (is_object($data) || is_array($data)) 
   ){
    $content = json_encode($data);
    $jsonerr = json_last_error();
    if ($jsonerr !== JSON_ERROR_NONE){
        echo '{"success":"0", "error":"'.$jsonerr.'", "message":"'.json_last_error_msg().'"}';
    } else {
        $length = strlen($content);
        header('Content-Length: '.$length);
        echo $content;
    }
} else {
    if ($valid){
        //TODO:
    } else {
        header('HTTP/1.1 400 Bad Request');
    }
}

function user(){
  
    global $USER;

/*    $USER->Authorize(10); */

    if ( $USER->IsAuthorized() ){
        
        $res = array(
            "id"    => $USER->GetID(),
            "name"  => $USER->GetFullName(),
            "adm"   => $USER->IsAdmin(),
            "login" => $USER->GetLogin()
        );
        if ( $res["haswp"] ){
            $adds = new CHLBTAdds("USER", $USER->GetID());
            $res["DVSS"] = $adds->list(); 
        }
        
        //Check group`s
        $f = "ID";
        $sort = "ASC";
        $filter = array("STRING_ID" => WP_GROUP);
        $group = CGroup::GetList($f, $sort, $filter)->fetch();
        $planningGroupId = (!!$group) ? $group["ID"] : -1;
        $res["haswp"] = $USER->IsAdmin() || array_search($planningGroupId, $USER->GetUserGroupArray());
        
        $filter = array("STRING_ID" => WP_GROUPMOD);
        $group = CGroup::GetList($f, $sort, $filter)->fetch();
        $planningGroupId = (!!$group) ? $group["ID"] : -1;
        $res["hasmod"] = $USER->IsAdmin() || array_search($planningGroupId, $USER->GetUserGroupArray());
        
        
        return $res;
    }
    return array( "id" => -1 );
}   //user

function doauth($params ){
    global $APPLICATION;
    global $USER;
    session_start();
    $APPLICATION->RestartBuffer();
    if (!is_object($USER)) $USER = new CUser;
    $arAuthResult = $USER->Login($params["u"], $params["p"], "Y");
    $APPLICATION->arAuthResult = $arAuthResult;
    return user();
}


function divisions($params = false){
    $res = array();
    $dvss = new CHLBTEntity('WpDivisions');
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                $fields = array( 
                    "ID" => $item["ID"],
                    "UF_NAME" => $item["UF_NAME"],
                    "UF_CODE" => $item["UF_CODE"],
                    "UF_ACTIVE" => !!$item["UF_ACTIVE"] ? 1 : 0,
                    "UF_SORT"   => intval($item["UF_SORT"])
                );
                $res = $dvss->save($fields);
                break;
            case "del":
                $dvss->del(intval($params['ID']));
                break;
        }
    } else {
        $args = array(
            "order" => array('UF_SORT' => 'ASC')
        );
        $res = $dvss->list( $args );
    }
    return $res;
}   //divisions

/**
 * Users oops
 * @param array params for oops
 */
function users($params = false){
    $res = array(); //returning
    
    //Check group 
    $f = "ID";
    $sort = "ASC";
    $filter = array("STRING_ID" => WP_GROUP);
    $group = CGroup::GetList($f, $sort, $filter)->fetch();
    $planningGroupId = (!!$group) ? $group["ID"] : -1;
    
    $filter = array("STRING_ID" => WP_GROUPMOD);
    $group = CGroup::GetList($f, $sort, $filter)->fetch();
    $modGroupId = (!!$group) ? $group["ID"] : -1;
    
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                $groups = intval($item["ID"]) > 0 ? CUser::GetUserGroup($item["ID"]) : array();
                if (!!$item["WP_PLANNING"]){
                    $groups[] = $planningGroupId;
                } else {
                    $n = array_search($planningGroupId, $groups);
                    if ($n !== false){
                        unset($groups[$n]);
                    }
                }
                if (!!$item["WP_MODER"]){
                    $groups[] = $modGroupId;
                } else {
                    $n = array_search($modGroupId, $groups);
                    if ($n !== false){
                        unset($groups[$n]);
                    }
                }
                
                
                $item["GROUP_ID"] = $groups;
                if ( intval($item["ID"]) < 1 ){
                    $item["PASSWORD"] = "12345678";
                    $item["CONFIRM_PASSWORD"] = $item["PASSWORD"];
                } else {
                    unset($item["PASSWORD"]);
                    unset($item["CONFIRM_PASSWORD"]);
                }

                $user = new CUser();
                $res = false;
                if (intval($item["ID"]) > 0){
                    if ( $user->Update($item["ID"], $item) ){
                        $res = $item["ID"];
                    }
                } else {
                    $res = $user->Add($item);
                }
                
                if ( empty($user->LAST_ERROR) ){
                    $adds = new CHLBTAdds("USER", $res);
                    $dvss = $adds->addAll($item["DVSS"]);
                    $res = array("success" => true, "ID" => $res, "DVSS" => $dvss);
                } else {
                    $res = array("success" => false, "error" => $user->LAST_ERROR, "ID" => $item["ID"]);
                }
                break;
            case "del":
                $id = intval($params['ID']);
                if ( $id > 0 ){
                    $user = new CUser();
                    $res = $user->Delete($id);
                    if ($res!==false){
                        $adds = new CHLBTAdds("USER", $id);
                        $adds->delAll();
                        
                        $res = array("id" => $id, "success" => true);
                    } else {
                        $res = array("id" => $id, "error" => $user->LAST_ERROR);
                    }
                } else {
                    $res = array("success" => false, "error"=>"Unknown item #");
                }
                break;
        }
    } else {
        $order = array('sort' => 'asc');
        $tmp = 'sort';
        $params = array("ID", "ACTIVE", "LOGIN", "NAME", "SECOND_NAME", "LAST_NAME", "EMAIL", "PERSONAL_PHONE", "PERSONAL_NOTES", "IS_ONLINE", "WORK_NOTES");
        $filter = array();
        $rsUsers = CUser::GetList($order, $tmp, $filter, $params);
        
        while( $el = $rsUsers->Fetch() ) {
            if ( strpos($el["LOGIN"], "esia_") === false ) {
                $el["WP_PLANNING"] = in_array($planningGroupId, CUser::GetUserGroup($el["ID"])) ? "Y" : null;
                $adds = new CHLBTAdds("USER", $el["ID"]);
                $el["DVSS"] = $adds->list(); 
                $res[] = $el;
            }
        };
    }
    return $res;
}

/**
 * Staff`s oop`s
 * @param array $params
 * @return boolean
 */
function staffing($params = false){
    $res;
    $entity = new CHLBTEntity('WpStaffing');
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                $fields = array( 
                    "ID" => $item["ID"],
                    "UF_NAME" => $item["UF_NAME"],
                    "UF_DISABLE" => !!$item["UF_DISABLE"] ? 1 : 0,
                    "UF_SORT" => intval($item["UF_SORT"])
                );
                $res = $entity->save( $fields );
                break;
            case "del": 
                $res = $entity->del( intval($params['ID']) );
                break;
        }
    } else {
        return $entity->list(array("sort" => array('UF_SORT' => 'ASC', 'UF_NAME' => 'ASC')));
    }
    return $res;
}   //staffing

/**
 * Employees oop`s
 * @param array $params
 * @return boolean
 */
function employees($params = false){
    $res;
    $entity = new CHLBTEntity('WpEmployees');
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];

                $row  = array(
                    "ID"      => $item["ID"],
                    "UF_UID"  => $item["UF_UID"],
                    "UF_EMPNAME"=>$item["UF_EMPNAME"],
                    "UF_DVS"  => $item["UF_DVS"],
                    "UF_STAFF"=> $item["UF_STAFF"],
                    "UF_ADDED"=> Bitrix\Main\Type\Date::createFromTimestamp(strtotime($item["UF_ADDED"])),
                    "UF_END"  => !!$item["UF_END"] ? Bitrix\Main\Type\Date::createFromTimestamp(strtotime($item["UF_END"])) : null
                );
                $res = $entity->save($row);
                if ($res["success"]) {
                    $res["item"] = employees( array("ID" => $res["item"][0]["ID"]) );
                }
                break;
            case "del":
                $res = $entity->del(intval($params['ID']));
                break;
        }
    } else {
        $res = array();
        $dirs = new stdClass();
        $dirs->users = array_slice(users(false), 0);
        $dirs->staffs= array_slice(staffing(false), 0);
        $dirs->dvss  = array_slice(divisions(false), 0);
        
        $args = array( 'select' => array('*') );
        if (
                (!!$params) 
             && (intval($params["ID"]) > 0)
           ){
            $args['filter'] = array('=ID' => $params["ID"]);
        }
        $data = $entity->list($args);
        foreach($data as $el){
            foreach ($dirs->users as $_u){
                if ($el["UF_UID"] == $_u["ID"]){
                    $el["UF_LOGIN"] = $_u["LOGIN"];
                    break;
                }
            }
            if (!!$el["UF_STAFF"]){
                foreach ($dirs->staffs as $_s){
                    if ($el["UF_STAFF"] == $_s["ID"]){
                        $el["STAFF_NAME"] = $_s["UF_NAME"];
                        break;
                    }
                }
            }
            if (!!$el["UF_DVS"]){
                foreach ($dirs->dvss as $_d){
                    if ($el["UF_DVS"] == $_d["ID"]){
                        $el["DVS_NAME"] = $_d["UF_NAME"];
                        break;
                    }
                }
            }
            
            $el["UF_ADDED"] = (!!$el["UF_ADDED"]) ? $el["UF_ADDED"]->getTimestamp()*1000 : null;
            $el["UF_END"] = (!!$el["UF_END"]) ? $el["UF_END"]->getTimestamp()*1000 : null;
            
            $res[] = $el;
        }
    }
   

    return $res;

}   //employees

function acts($params = false){
    $entity = new CHLBTEntity('WpActions');
    $res = array();
    if ( ($params !== false) && (!!$params["action"]) ){
        global $USER;
        switch($params["action"]){
            case "save":
                $item = $params["item"];

                $row  = array(
                    "ID"          => $item["ID"],
                    "UF_ADT"      => Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime($item["UF_ADT"])),
                    "UF_RED"      => 0,
                    "UF_DVS"      => $item["UF_DVS"],
                    "UF_GRATTR"   => (!!$item["UF_GRATTR"])   ? 1 : 0,
                    "UF_DAYATTR"  => (!!$item["UF_DAYATTR"])  ? 1 : 0,
                    "UF_YEARATTR" => (!!$item["UF_YEARATTR"]) ? 1 : 0,
                    "UF_SPECATTR" => (!!$item["UF_SPECATTR"]) ? 1 : 0,
                    "UF_WWWATTR"  => (!!$item["UF_WWWATTR"])  ? 1 : 0,
                    "UF_READY"    => (!!$item["UF_READY"])    ? 1 : 0,
                    "UF_ANNOUN"   => (!!$item["UF_ANNOUN"])   ? 1 : 0,
                    "UF_TEXT"     => $item["UF_TEXT"],
                    "UF_PLACE"    => is_array($item["UF_PLACE"]) ? $item["UF_PLACE"]["UF_PLACE"] : $item["UF_PLACE"],
                    "UF_CHIEF"    => $item["UF_CHIEF"],
                    "UF_STATUS"   => $item["UF_STATUS"],
                    "UF_COMMENTS" => $item["UF_COMMENTS"],
                    "UF_AUTHOR"   => 10,
                    "UF_INSTIME"  => new Bitrix\Main\Type\DateTime()
                );
                
                $res = $entity->save($row);
                
                if ($res["success"]){
                    //save add's
                    $id = $res["item"][0]["ID"];
                    
                    $adds1 = new CHLBTAdds("HEADS", $id);
                    $a1 = $adds1->addAll($item["HEADS"]);
                    
                    $adds2 = new CHLBTAdds("EMPS", $id);
                    $a2 = $adds2->addAll($item["EMPS"]);
                    
                    $res["item"] = acts( array("ID" => $id) );
                }
                
                break;
            case "del":
                $res = $entity->del( intval($params['ID']) );
                break;
        }
    } else {
        $fully= ($params !== false)&&(1==$params["fully"]);
        $byId = (!!$params) && (intval($params["ID"])>0);
        $announs=(!!$params) && (intval($params["announs"])>0);
        
        $dirs = new stdClass();
        $dirs->users= array_slice(users(false), 0);
        $dirs->emps = array_slice(employees(false), 0);
        $dirs->dvss = array_slice(divisions(false), 0);
        $args = array( 'select' => array('*') );
        if ( $byId ) {
            $args['filter'] = array('=ID' => $params["ID"]);
        } else {
            $args['filter'] = array('=UF_RED' => 0);
            $period = $params["period"];
            if (!!$period) {
                $start = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($period["start"]) );
                $end   = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($period["end"]) );
                $filter= [
                    "LOGIC" => "AND",
                    ['=UF_RED' => 0],
                    ['>=UF_ADT' => $start],
                    ['<=UF_ADT' => $end],
                ];
                if ( $announs ){
                    array_push($filter, ["=UF_ANNOUN" => 1]);
                }
                
                $args["filter"] = $filter;
            }
        }
        
        $res = array();
        $data = $entity->list($args);
        foreach($data as $el){
            
            $id = $el["ID"];
            
            if (!!$el["UF_DVS"]){
                foreach ($dirs->dvss as $_d){
                    if ($el["UF_DVS"] == $_d["ID"]){
                        $el["DVS_NAME"] = $_d["UF_NAME"];
                        $el["DVS_CODE"] = $_d["UF_CODE"];
                        break;
                    }
                }
            }
            if (!!$el["UF_CHIEF"]){
                foreach ($dirs->emps as $_e){
                    if ($el["UF_CHIEF"] == $_e["ID"]){
                        $el["CHIEF_NAME"] = $_e["UF_EMPNAME"];
                        $el["CHIEF"] = $_e;
                        break;
                    }
                }
            }
            $el["UF_ADT"] = (!!$el["UF_ADT"]) ? $el["UF_ADT"]->getTimestamp()*1000 : null;
            $el["UF_INSTIME"] = (!!$el["UF_INSTIME"]) ? $el["UF_INSTIME"]->getTimestamp()*1000 : null;
            
            //add's by id only or fully load (optimize)
            if ( $byId || $fully ){
                
                if (!!$el["UF_AUTHOR"]){
                    foreach ($dirs->users as $_u){
                        if ($el["UF_AUTHOR"] == $_u["ID"]){
                            $el["UF_AUTHOR"] = $_u["LOGIN"];
                            break;
                        }
                    }
                }

                $adds = new CHLBTAdds("HEADS", $id);
                $el["HEADS"] = $adds->list();
                
                $heads = array();
                foreach($el["HEADS"] as $_id){
                    foreach ($dirs->emps as $_e){
                        if ($_id == $_e["ID"]){
                            $heads[] = $_e["UF_EMPNAME"];
                            break;
                        }
                    }
                }
                $el["HEADNAMES"] = $heads;
                

                $adds2 = new CHLBTAdds("EMPS", $id);
                $el["EMPS"] = $adds2->list();
                
                $emps = array();
                foreach($el["EMPS"] as $_id){
                    foreach ($dirs->emps as $_e){
                        if ($_id == $_e["ID"]){
                            $emps[] = $_e["UF_EMPNAME"];
                            break;
                        }
                    }
                }
                $el["EMPNAMES"] = $emps;
                
            }
            
            $res[] = $el;
        }
    }
    
    return $res;
}   //acts

/**
 * Red days oop's
 * @param Array $params
 * @return Array
 */
function reds($params = false){
    $res;
    $entity = new CHLBTEntity('WpActions');
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                global $USER;
                $item = $params["item"];
                $row  = array(
                    "ID"          => $item["ID"],
                    "UF_ADT"      => Bitrix\Main\Type\Date::createFromTimestamp(strtotime($item["UF_ADT"])),
                    "UF_RED"      => 1,
                    "UF_DVS"      => null,
                    "UF_GRATTR"   => 0,
                    "UF_DAYATTR"  => 0,
                    "UF_YEARATTR" => (!!$item["UF_YEARATTR"]) ? 1 : 0,
                    "UF_SPECATTR" => 0,
                    "UF_WWWATTR"  => 1,
                    "UF_READY"    => 1,
                    "UF_TEXT"     => $item["UF_TEXT"],
                    "UF_AUTHOR"   => 10, //TODO: $USER->GetID(),
                    "UF_INSTIME"  => new Bitrix\Main\Type\DateTime()
                );
                
                $res = $entity->save($row);
                if ($res["success"]){
                    $res["item"] = reds( array("ID" => $res["item"][0]["ID"]) );
                }
                
                break;
            case "del":
                $res = $entity->del(intval($params['ID']));
                break;
        }
    } else {
        $args = array( 'select' => array('*') );
        $users= array_slice(users(false), 0);
        if (
                (!!$params) && (intval($params["ID"])>0)
           ){
            $args['filter'] = array('=ID' => $params["ID"]);
        } else {
            $args['filter'] = array('=UF_RED' => 1);
            $period = $params["period"];
            if (!!$period) {
                $start = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($period["start"]) );
                $end   = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($period["end"]) );
                $args["filter"] = [
                    "LOGIC" => "AND",
                    ['=UF_RED' => 1],
                    ['>=UF_ADT' => $start],
                    ['<=UF_ADT' => $end]
                ];
            }
        }
        
        $res = array();
        $data = $entity->list($args);
        foreach($data as $el){
            $el["UF_ADT"] = (!!$el["UF_ADT"]) ? $el["UF_ADT"]->getTimestamp()*1000 : null;
            $el["UF_INSTIME"] = (!!$el["UF_INSTIME"]) ? $el["UF_INSTIME"]->getTimestamp()*1000 : null;
            if (!!$el["UF_AUTHOR"]){
                    foreach ($users as $_u){
                        if ($el["UF_AUTHOR"] == $_u["ID"]){
                            $el["UF_AUTHOR"] = $_u["LOGIN"];
                            break;
                        }
                    }
            }
            $res[] = $el;
        }
    }
    
    return $res;
}   //reds...   

function places( $params = false ){
    global $DB;
    $res = array();
    if ( ($params !== false) && (!!$params["action"]) ){
        if ("save" == $params["action"]){
            $item = $params["item"];
            $rows = 0;
            $rsData = $DB->Query("select a1.ID from wpactions a1 where exists(select * from wpactions a2 where a2.ID=" . intval($item["ID"]) . " and a1.UF_PLACE=a2.UF_PLACE)");
            while( $el = $rsData->fetch() ){
                $DB->Update(
                               "wpactions", 
                                array("UF_PLACE" => "'" . $item["UF_PLACE"] . "'"),
                               "WHERE ID=" . intval($el["ID"])
                            );
                $rows++;
            }
            $res["success"] = true;
            $res["rows"] = $rows;
            $res["item"] = array("ID" => $item["ID"], "UF_PLACE" => $item["UF_PLACE"]);
            return $res;
        }
        return array("success"=>false, "error"=>"bad action: " . $params["action"]);
    }
    
    $rsData = $DB->Query("select min(ID) ID, count(*) NUMS, UF_PLACE from wpactions where (UF_PLACE is not NULL) group by UF_PLACE order by UF_PLACE");
    while( $el = $rsData->fetch() ){
        $res[] = array("ID" => $el["ID"], "NUMS" => $el["NUMS"], "UF_PLACE" => $el["UF_PLACE"]);
    }
    return $res;
}   //places

function imp($params){
    $imp = new WpImport($params["mn"], $params["yr"]);
    $res = array(
        "acts" => $imp->doPlan(),
        "reds" => $imp->doReds()
    );
    return $res;
}   //imp...

function do_publish( $params ){
    global $DB;
    $start = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($params["start"]) );
    $end   = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($params["end"]) );
    $rows  = $DB->Update("wpactions", array("UF_WWWATTR"  => 1), "WHERE (UF_WWWATTR!=1) and (UF_READY=1) and UF_ADT between '" 
             . $start->format('Y-m-d 00:00:00') . "' and '" . $end->format('Y-m-d 23:59:59') . "'");
    return array("success"=>true, "rows"=>$rows);
}   //do_publish

?>