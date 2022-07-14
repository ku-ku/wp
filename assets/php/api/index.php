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
        if ($request->isPost()){
            $data = acts_save();
        } else {
            $data = acts($request["id"]);
        }
        break;
    case "user":
        $data = user();
        break;
    case "divisions":
        $data = divisions($request["params"]);
        break;
    case "users":
        $data = users( $request["params"] );
        break;
    case "staffing":
        $data = staffing( $request["params"] );
        break;
    case "employees":
        $data = employees( $request["params"] );
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

function acts($id){
    $rootId = get_ib_id('workPlan');
    if( $rootId > 0 ) {
        $filter = Array('IBLOCK_ID' => $rootId, 'ACTIVE' => 'Y');
        
        if (isset($id)){
            $filter["ID"] = $id;
        }
        $res = CIBlockElement::getList(
                Array('ACTIVE_FROM' => 'ASC', 'NAME' => 'ASC'),
                $filter,
                false,
                false,
                Array("ID", "DETAIL_TEXT", "DATE_ACTIVE_FROM", "PROPERTY_PLNDVS", 
                      "PROPERTY_GRATTR", "PROPERTY_DAYATTR", "PROPERTY_SPECATTR",
                      "PROPERTY_PLNPLACE", "PROPERTY_PLNHDR", "PROPERTY_EMPS", 
                      "PROPERTY_PLNSTATE", "PROPERTY_WWWATTR")
        );
        $arr = Array();
        while($row = $res->GetNext()){
            array_push($arr, Array(
                "id"  => $row["ID"],
                "name"=> $row["DETAIL_TEXT"],
                "dt"  => (int)MakeTimeStamp($row["DATE_ACTIVE_FROM"]) * 1000,
                "dvs" => $row["PROPERTY_PLNDVS_VALUE"],
                "grAttr"  => $row["PROPERTY_GRATTR_VALUE"],
                "dayAttr" => $row["PROPERTY_DAYATTR_VALUE"],
                "specAttr"=> $row["PROPERTY_SPECATTR_VALUE"],
                "place" => $row["PROPERTY_PLNPLACE_VALUE"],
                "hdr"   => $row["PROPERTY_PLNHDR_VALUE"],
                "emps"  => $row["PROPERTY_EMPS_VALUE"],
                "state" => $row["PROPERTY_PLNSTATE_VALUE"],
                "wwwAttr" => $row["PROPERTY_WWWATTR_VALUE"],
                "red"   => 0
            ));
        }
        return $arr;
    }
    return false;
}   //acts

function acts_save(){
    global $request;
    global $USER;
    global $DB;
    
    //TODO: $rootId = get_ib_id('workPlan');
    $rootId = 45;
    
    if( $rootId > 0 ){
        
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
            $elId = $el->Add($elVals);
        } else {
        }
        if ( $elId ){
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["dvs"], "PLNDVS");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["grAttr"], "GRATTR");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["dayAttr"], "DAYATTR");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["specAttr"], "SPECATTR");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["place"], "PLNPLACE");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["hdr"], "PLNHDR");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["emps"], "EMPS");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["state"], "PLNSTATE");
            CIBlockElement::SetPropertyValues($elId, $rootId, $request["wwwAttr"], "WWWATTR");
            $res = Array("success" => 1, "id" => $elId);
        } else {
            $res = Array("success" => 0, "message" => $el->LAST_ERROR);
        }
    } else {
        $res = Array("success" => 0, "message" => "No IB-workPlan found# ". $rootId);
    }
    
    return $res;
}   //act_save

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
        return array("success" => false, "error" => "No high-load table exists");
    }
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();

    if ( isset($params) ){
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
    
    if ( isset($params) ){
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
function staffing($params){
    
    $hlbtId = hlbtByName('staffing');
    if ($hlbtId < 1){
        return false;
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    
    if ( isset($params) ){
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
function employees($params){
    $hlbtId = hlbtByName('employees');
    if ($hlbtId < 1){
        return false;
    }
    
    $res = array();
    $hlblock = HLBT::getById($hlbtId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    if ( isset($params) ){
        switch($params["action"]){
            case "save":
                break;
        }
    } else {
        $arUsers = users();
        $rsData = $entity_data_class::getList(array(
            'select' => array('*'),
            'order' => array('UF_NAME' => 'ASC'),
        ));
        while($el = $rsData->fetch()){
            $u = false;
            foreach ($arUsers as $_u){
                if ($_u["ID"] === $el["UF_UID"]){
                    $u = $_u;
                    break;
                }
            }
            $el["USER"] = $u;
            $res[] = $el;
        }
    }

    return $res;

}   //employees
?>