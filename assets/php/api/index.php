<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('highloadblock');

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
        $data = divisions();
        break;
    default:
        $valid = false;
}   //switch ($q...

header('Content-Type: text/plain; charset=UTF-8');
if ( ($valid)&&($data) ){
    //    header('Content-Type: application/json');
    //print \Bitrix\Main\Web\Json::encode($data);
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

function divisions(){
    $hlblock = HLBT::getById(2)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
	$rsData = $entity_data_class::getList(array(
   		'select' => array('*'),
		'order' => array('UF_SORT' => 'ASC'),
	));
    return $rsData;
}
?>