<?php
/**
 * API realization for Work-plan
 * 
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('highloadblock');
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$q = $request["q"];
$data = false;
$valid = true;
switch ($q){
    case "reds":
        if ($request->isPost()){
            $data = red_save();
        } else {
            $data = reds($request["id"]);
        }
        break;
    case "acts":
        $data = acts();
        break;
    case "user":
        $data = user();
        break;
    case "divisions":
        $data = divisions( isset($request["params"]) ? $request["params"] : false );
        break;
    case "users":
        $data = users( isset($request["params"]) ? $request["params"] : false );
        break;
    case "staffing":
        $data = staffing( isset($request["params"]) ? $request["params"] : false );
        break;
    case "employees":
        $data = employees( isset($request["params"]) ? $request["params"] : false );
        break;
    case "places":
        $data = places();
        break;
    
    default:
        $valid = false;
}   //switch ($q...

header('Content-Type: text/plain; charset=UTF-8');
if ( ($valid)&&($data) ){
    $content = json_encode($data);
    $jsonerr = json_last_error();
    if ($jsonerr !== JSON_ERROR_NONE){
        echo '{"success":"0", "error":"'.$jsonerr.'", "message":"'.json_last_error_msg().'"}';
    } else {
        $length = strlen($content);
        header('Content-Length: '.$length);
        echo $content;
        flush();
    }
} else {
    if ($valid){
        //TODO:
    } else {
        header('HTTP/1.1 400 Bad Request');
    }
}

function get_ib_id($code){
    $ib = CIBlock::GetList(
            Array(), 
            Array(
                'ACTIVE' => 'Y', 
                'CODE'   => $code
            ), false);
    
    $row = $ib->GetNext();
    
    return is_array($row) ? (int)$row["ID"] : 0;
}   //get_ib_id

function el_id_byex($ibId, $exId){
    $id = 0;
    if (isset($exId)){
        $res = CIBlockElement::getList(
                    Array('ACTIVE_FROM' => 'ASC'),
                    Array('IBLOCK_ID' => $ibId, 'EXTERNAL_ID' => $exId),
                    false,
                    false,
                    Array("ID", "EXTERNAL_ID")
        );
        if ($row = $res->GetNext()){
            $id = (int)$row["ID"];
        }
        
    } 
    return $id;
}   //el_id_byex


function reds($id){
    $rootId = get_ib_id('holidays');
    if($rootId > 0){
        $filter = Array('IBLOCK_ID' => $rootId, 'ACTIVE' => 'Y');
        if (isset($id)){
            $filter["ID"] = $id;
        }
        $res = CIBlockElement::getList(
                Array('ACTIVE_FROM' => 'ASC', 'NAME' => 'ASC'),
                $filter,
                false,
                false,
                Array("ID", "DETAIL_TEXT", "DATE_ACTIVE_FROM", "PROPERTY_REDYR")
        );
        $arr = Array();
        while($row = $res->GetNext()){
            array_push($arr, Array(
                "id"  => $row["ID"],
                "name"=> $row["DETAIL_TEXT"],
                "dt"  => (int)MakeTimeStamp($row["DATE_ACTIVE_FROM"]) * 1000,
                "yr"  => ($row["PROPERTY_REDYR_VALUE"]) ? $row["PROPERTY_REDYR_VALUE"] : 0,
                "red" => 1
            ));
        }
        return $arr;
    }
    return false;
}   //reds...   

function red_save(){
    global $request;
    global $USER;
    global $DB;
    
    $rootId = get_ib_id('holidays');
    if($rootId > 0){
        
        $id = isset($request["id"]) ? (int)$request["id"] : el_id_byex($rootId, $request["xid"]);
        
        $dt = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), (int)$request["dt"]/1000);
        $elVals = Array(
            "MODIFIED_BY"     => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"       =>  $rootId,
            "ACTIVE"          =>  "Y",
            "NAME"            =>  $request["nm"],
            "DETAIL_TEXT"     =>  $request["nm"],
            "CREATED_DATE"    => $dt,
            "DATE_ACTIVE_FROM"=> $dt
        );
        if (isset($request["xid"])){
            $elVals['EXTERNAL_ID'] = $request["xid"];
        }
        
        $el = new CIBlockElement();
        if ($id > 0){
            $el->Update($id, $elVals);
            $elId = $id;
        } else {
            $elId = $el->Add($elVals);
        }
        if ( $elId ){
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["yr"], "REDYR");
            $res = Array("success" => 1, "id" => $elId);
        } else {
            $res = Array("success" => 0, "message" => $el->LAST_ERROR, "dt" => $dt);
        }
    } else {
        $res = Array("success" => 0, "message" => "No IB-holidays found");
    }
    
    return $res;
}   //red_save

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

    if ( $params !== false ){
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
    
    if ( $params !== false ){
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
    
    $hlbtId = hlbtByName('staffing');
    if ($hlbtId < 1){
        return false;
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    
    if ( $params !== false ){
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
    if ( ($params !== false) && is_array($params["item"]) ){
        switch($params["action"]){
            case "save":
                $item = $params["item"];
                
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
                    $res["items"] = employees(array("ID" => $obResult->getID()));
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
        return false;
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    if ( ($params !== false) && is_array($params["item"]) ){
        switch($params["action"]){
            case "save":
                break;
        }
    } else {
        $dirs = new stdClass();
        $dirs->emps = array_slice(employees(false), 0);
        $dirs->dvss = array_slice(divisions(false), 0);
        
        $args = array( 'select' => array('*') );
        if (
                (!!$params) && (intval($params["ID"])>0)
           ){
            $args['filter'] = array('=ID' => $params["ID"]);
        } else {
            $args['filter'] = array('=UF_RED' => 0);
        }
        
        $rsData = $entity_data_class::getList($args);
        while($el = $rsData->fetch()){
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
            $el["UF_ADT"] = (!!$el["UF_ADT"]) ? $el["UF_ADT"]->getTimestamp()*1000 : null;
            $res[] = $el;            
        }
    }
}   //acts

function places(){
    $hlbtId = hlbtByName('WpActions');
    
    if ($hlbtId < 1){
        return false;
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();

    $args = array( 
                    'select' => array("UF_PLACE"),
                    'filter' => array("=UF_RED" => 0, "!=UF_PLACE" => NULL)
    );
    $rsData = $entity_data_class::getList($args);
    while($el = $rsData->fetch()){
        if (array_search($el['UF_PLACE'], $res) === false){
            $res[] = $el['UF_PLACE'];
        }
    }

    return $res;
}   //places
?>