<?php
/**
 * API realization for Work-plan
 * 
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require(__DIR__ . '/classes/chlbt.php');

\Bitrix\Main\Loader::includeModule('highloadblock');
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$q = $request["q"];
$params = isset($request["params"]) ? $request["params"] : false;
$data = false;
$valid = true;
switch ($q){
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
        $data = places();
        break;
    case "user":
        $data = user();
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


function hlbtByName($name){
    $args = array(
        'select' => array('ID'),
        'filter' => array('TABLE_NAME' => $name)
    );
    $res = -1;
    $hlblock = HLBT::getList($args);
    if ( $row = $hlblock->fetch() ){
        $res = $row['ID'];
    }
    return $res;
}   //listTables


function user(){
    global $USER;
    if ( $USER->IsAuthorized() ){
        return array(
            "id"    => $USER->GetID(),
            "name"  => $USER->GetFullName(),
            "adm"   => $USER->IsAdmin(),
            "groups"=> $USER->GetUserGroupArray()
        );
    }
    return array( "id" => -1 );
}   //user

function divisions($params = false){
    $hlbtId = hlbtByName('department_codes');
    $res = array();
    if ($hlbtId < 1){
        return array("success" => false, "error" => "No high-load table department_codes exists");
    }
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();

    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                unset($item["isTrusted"]);
                
                $obResult = ( intval($item['ID']) > 0 ) 
                                ? $entity_data_class::update($item['ID'], $item) 
                                : $entity_data_class::add($item);
                
                $res = array("success" => $obResult->isSuccess(), "ID"=> $obResult->getID() );
                break;
            case "del":
                $id = intval($params['ID']);
                $res = ( $id > 0 ) ? $entity_data_class::delete($id) : false;
                if (!!$res){
                    if ( $res->isSuccess() ){
                        $res = array("id" => $id, "success"=> true);
                    } else {
                        $res = array("id" => $id, "error"=>$res->getErrorMessages());
                    }
                } else {
                    $res = array("success" => false, "error"=>"Unknown item #");
                }
                break;
        }
    } else {
        $rsData = $entity_data_class::getList(array(
                    'select' => array('*'),
                    'order' => array('UF_SORT' => 'ASC'),
        ));
        while($el = $rsData->fetch()){
            $res[] = $el;
        }
    }
    return $res;
}

/**
 * Users oops
 * @param array params for oops
 */
function users($params = false){
    $res = array(); //returning
    
    //Check group 
    $groupId = "WP_PLANNING";
    $f = "ID";
    $sort = "ASC";
    $filter = array("STRING_ID"=>$groupId);
    $group = CGroup::GetList($f, $sort, $filter)->fetch();
    $planningGroupId = (!!$group) ? $group["ID"] : -1;
    
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
                    $res = array("success" => true, "ID" => $res);
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
        $params = array("ID", "ACTIVE", "LOGIN", "NAME", "SECOND_NAME", "LAST_NAME", "EMAIL", "PERSONAL_PHONE", "PERSONAL_NOTES", "IS_ONLINE");
        $filter = array();
        $rsUsers = CUser::GetList($order, $tmp, $filter, $params);
        
        while( $el = $rsUsers->Fetch() ) {
            $el["WP_PLANNING"] = in_array($planningGroupId, CUser::GetUserGroup($el["ID"])) ? "Y" : null;
            $res[] = $el;
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

/**
    $entity = new CHLBTEntity('staffing');
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                break;
            case "del": 
                break;
        }
    } else {
        return $entity->list(array('*'), false, array('UF_SORT' => 'ASC', 'UF_NAME' => 'ASC'));
    }
 */    
    $hlbtId = hlbtByName('staffing');
    if ($hlbtId < 1){
        return false;
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                $fields = array( 
                    'UF_NAME' => $item["UF_NAME"],
                    'UF_DISABLE' => !!$item["UF_DISABLE"] ? 1 : 0,
                    'UF_SORT' => intval($item["UF_SORT"])
                );
                
                $obResult = ( intval($item['ID']) > 0 ) 
                                ? $entity_data_class::update($item['ID'], $fields) 
                                : $entity_data_class::add($fields);
                
                $res = array("success" => $obResult->isSuccess(), "ID"=> $obResult->getID() );
                break;
            case "del":
                $id = intval($params['ID']);
                $res = ( $id > 0 ) ? $entity_data_class::delete($id) : false;
                if (!!$res){
                    if ( $res->isSuccess() ){
                        $res = array("id" => $id, "success"=> true);
                    } else {
                        $res = array("id" => $id, "error"=>$res->getErrorMessages());
                    }
                } else {
                    $res = array("success" => false, "error"=>"Unknown item #");
                }
                break;
        }
    } else {
        $rsData = $entity_data_class::getList(array(
                        'select' => array('*'),
                        'order' => array('UF_SORT' => 'ASC', 'UF_NAME' => 'ASC')
        ));
        while($el = $rsData->fetch()){
            $res[] = $el;
        }
    }
    return $res;
}   //staffing

/**
 * Employees oop`s
 * @param array $params
 * @return boolean
 */
function employees($params = false){
   
    $hlbtId = hlbtByName('employees');
    
    if ($hlbtId < 1){
        return false;
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                /* User full-name */
                $order = array('sort' => 'asc');
                $tmp = 'sort';
                $params = array("ID", "LOGIN", "NAME", "SECOND_NAME", "LAST_NAME");
                $filter = array("=ID" => $item["UF_UID"]);
                $rsUser = CUser::GetList($order, $tmp, $filter, $params);
                if ($user = $rsUser->Fetch()) {
                    $empName = sprintf("%s %s %s", $user["LAST_NAME"], $user["NAME"], $user["SECOND_NAME"]);
                } else {
                    $empName = "Неизвестный";
                }

                $row  = array(
                    "UF_UID"  => $item["UF_UID"],
                    "UF_EMPNAME"=>$empName,
                    "UF_DVS"  => $item["UF_DVS"],
                    "UF_STAFF"=> $item["UF_STAFF"],
                    "UF_ADDED"=> Bitrix\Main\Type\Date::createFromTimestamp(strtotime($item["UF_ADDED"])),
                    "UF_END"  => !!$item["UF_END"] ? Bitrix\Main\Type\Date::createFromTimestamp(strtotime($item["UF_END"])) : null
                );
                
                $obResult = ( intval($item['ID']) > 0 ) 
                                ? $entity_data_class::update($item['ID'], $row) 
                                : $entity_data_class::add($row);
                $res = array(
                                "success" => $obResult->isSuccess(), 
                                "ID"=> $obResult->getID(),
                                "error" => $obResult->isSuccess() ? null : $obResult->getErrorMessages()
                            );
                
                if ( $obResult->isSuccess() ){
                    $res["item"] = employees(array("ID" => $obResult->getID()));
                }
                break;
            case "del":
                $id = intval($params['ID']);
                $res = ( $id > 0 ) ? $entity_data_class::delete($id) : false;
                if (!!$res){
                    if ( $res->isSuccess() ){
                        $res = array("id" => $id, "success"=> true);
                    } else {
                        $res = array("id" => $id, "error"=>$res->getErrorMessages());
                    }
                } else {
                    $res = array("success" => false, "error"=>"Unknown item #");
                }
                break;
        }
    } else {
        $dirs = new stdClass();
        $dirs->users = array_slice(users(false), 0);
        $dirs->staffs= array_slice(staffing(false), 0);
        $dirs->dvss  = array_slice(divisions(false), 0);
        
        $args = array( 'select' => array('*') );
        if (
                (!!$params) && (intval($params["ID"])>0)
           ){
            $args['filter'] = array('=ID' => $params["ID"]);
        }
        
        $rsData = $entity_data_class::getList($args);
        while($el = $rsData->fetch()){
            
            foreach ($dirs->users as $_u){
                if ($el["UF_UID"] == $_u["ID"]){
                    $el["UF_EMPNAME"] = sprintf("%s %s %s", $_u["LAST_NAME"], $_u["NAME"], $_u["SECOND_NAME"]);
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
    $hlbtId = hlbtByName('WpActions');
    
    if ($hlbtId < 1){
        return array(
                        "success" => false, 
                        "error" => "No HLBT WpActions exists"
               );
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    if ( ($params !== false) && (!!$params["action"]) ){
        global $USER;
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                $meta = (!!$item["UF_META"]) ? json_encode($item["UF_META"]) : null;

                $row  = array(
                    "UF_ADT"      => Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime($item["UF_ADT"])),
                    "UF_RED"      => 0,
                    "UF_DVS"      => $item["UF_DVS"],
                    "UF_GRATTR"   => (!!$item["UF_GRATTR"]) ? 1 : 0,
                    "UF_DAYATTR"  => (!!$item["UF_DAYATTR"]) ? 1 : 0,
                    "UF_YEARATTR" => (!!$item["UF_YEARATTR"]) ? 1 : 0,
                    "UF_SPECATTR" => (!!$item["UF_SPECATTR"]) ? 1 : 0,
                    "UF_WWWATTR"  => (!!$item["UF_WWWATTR"]) ? 1 : 0,
                    "UF_READY"    => (!!$item["UF_READY"]) ? 1 : 0,
                    "UF_TEXT"     => $item["UF_TEXT"],
                    "UF_PLACE"    => $item["UF_PLACE"],
                    "UF_CHIEF"    => $item["UF_CHIEF"],
                    "UF_STATUS"   => $item["UF_STATUS"],
                    "UF_COMMENTS" => $item["UF_COMMENTS"],
                    "UF_AUTHOR"   => 10, //$USER->GetID(),
                    "UF_META"     => $meta,
                    "UF_INSTIME"  => new Bitrix\Main\Type\DateTime()
                );
                
                $obResult = ( intval($item['ID']) > 0 ) 
                                ? $entity_data_class::update($item['ID'], $row) 
                                : $entity_data_class::add($row);
                $res = array(
                                "success" => $obResult->isSuccess(), 
                                "ID"=> $obResult->getID(),
                                "error" => $obResult->isSuccess() ? null : $obResult->getErrorMessages(),
                                "item" => $obResult->isSuccess() ? acts(array("ID" => $obResult->getID())) : null
                            );
                
                break;
            case "del":
                $id = intval($params['ID']);
                $res = ( $id > 0 ) ? $entity_data_class::delete($id) : false;
                if (!!$res){
                    if ( $res->isSuccess() ){
                        $res = array("id" => $id, "success"=> true);
                    } else {
                        $res = array("id" => $id, "error"=>$res->getErrorMessages());
                    }
                } else {
                    $res = array("success" => false, "error"=>"Unknown item #");
                }
                break;
        }
    } else {
        $dirs = new stdClass();
        $dirs->users= array_slice(users(false), 0);
        $dirs->emps = array_slice(employees(false), 0);
        $dirs->dvss = array_slice(divisions(false), 0);
        
        $args = array( 'select' => array('*') );
        if (
                (!!$params) && (intval($params["ID"])>0)
           ){
            $args['filter'] = array('=ID' => $params["ID"]);
        } else {
            $args['filter'] = array('=UF_RED' => 0);
            $period = $params["period"];
            if (!!period) {
                $start = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($period["start"]) );
                $end   = Bitrix\Main\Type\DateTime::createFromTimestamp( strtotime($period["end"]) );
                $args["filter"] = [
                    "LOGIC" => "AND",
                    ['=UF_RED' => 0],
                    ['>=UF_ADT' => $start],
                    ['<=UF_ADT' => $end]
                ];
            }
        }
        
        $rsData = $entity_data_class::getList($args);
        while( $el = $rsData->fetch() ){
            if (!!$el["UF_DVS"]){
                foreach ($dirs->dvss as $_d){
                    if ($el["UF_DVS"] == $_d["ID"]){
                        $el["DVS_NAME"] = $_d["UF_NAME"];
                        break;
                    }
                }
            }
            if (!!$el["UF_CHIEF"]){
                foreach ($dirs->emps as $_e){
                    if ($el["UF_CHIEF"] == $_e["ID"]){
                        $el["CHIEF_NAME"] = $_e["UF_EMPNAME"];
                        break;
                    }
                }
            }
            if (!!$el["UF_AUTHOR"]){
                foreach ($dirs->users as $_u){
                    if ($el["UF_AUTHOR"] == $_u["ID"]){
                        $el["UF_AUTHOR"] = $_u["LOGIN"];
                        break;
                    }
                }
            }
            $el["UF_ADT"] = (!!$el["UF_ADT"]) ? $el["UF_ADT"]->getTimestamp()*1000 : null;
            $el["UF_INSTIME"] = (!!$el["UF_INSTIME"]) ? $el["UF_INSTIME"]->getTimestamp()*1000 : null;
            if (!!$el["UF_META"]){
                $el["UF_META"] = json_decode($el["UF_META"]);
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
    $hlbtId = hlbtByName('WpActions');
    
    if ($hlbtId < 1){
        return array(
                        "success" => false, 
                        "error" => "No HLBT WpActions exists"
               );
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    if ( ($params !== false) && (!!$params["action"]) ){
        switch($params["action"]){
            case "save":
                global $USER;
                $item = $params["item"];
                
                $row  = array(
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
                    "UF_AUTHOR"   => 10, //$USER->GetID(),
                    "UF_INSTIME"  => new Bitrix\Main\Type\DateTime()
                );
                
                $obResult = ( intval($item['ID']) > 0 ) 
                                ? $entity_data_class::update($item['ID'], $row) 
                                : $entity_data_class::add($row);
                $res = array(
                                "success" => $obResult->isSuccess(), 
                                "ID"=> $obResult->getID(),
                                "error" => $obResult->isSuccess() ? null : $obResult->getErrorMessages(),
                                "item" => $obResult->isSuccess() ? reds(array("ID" => $obResult->getID())) : null
                            );
                
                break;
            case "del":
                $id = intval($params['ID']);
                $res = ( $id > 0 ) ? $entity_data_class::delete($id) : false;
                if (!!$res){
                    if ( $res->isSuccess() ){
                        $res = array("id" => $id, "success"=> true);
                    } else {
                        $res = array("id" => $id, "error"=>$res->getErrorMessages());
                    }
                } else {
                    $res = array("success" => false, "error"=>"Unknown item #");
                }
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
            if (!!period) {
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
        
        try {
            $rsData = $entity_data_class::getList($args);
            while($el = $rsData->fetch()){
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
        } catch(Exception $e) {
            $res = array("success" => false, "error" => $e->getMessage() );
        }
    }
    
    return $res;
}   //reds...   

function places(){
    global $DB;
    $rsData = $DB->Query("select distinct UF_PLACE from wpactions where UF_PLACE is not NULL order by 1");
    while( $el = $rsData->fetch() ){
        $res[] = $el['UF_PLACE'];
    }

    return $res;
}   //places
?>