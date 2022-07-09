<?php
/**
 *  see /bitrix/admin/highloadblock_entity_edit.php
 *  (info at: https://mattweb.ru/moj-blog/bitriks/item/185-sozdanie-hl-bloka-s-pomoshchyu-api-bitrix)
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
\Bitrix\Main\Loader::includeModule('highloadblock');
use Bitrix\Highloadblock as HL;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;

if (!$USER->IsAdmin()){
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}
global $USER_FIELD_MANAGER;
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

switch ( $request->get("q") ) {
    case "list":
        header("Content-Type: text/plain;charset=UTF-8");
        $tabs = listTables();
        foreach ($tabs as $t){
            $hlblock = $t['ID'];
            echo sprintf('#%d %s: %s', $hlblock, $t['NAME'], $t['TABLE_NAME']) . PHP_EOL;
            $entity = HLBT::compileEntity($hlblock);
            $fields = $USER_FIELD_MANAGER->GetUserFields('HLBLOCK_'.$hlblock);
            foreach ($fields as $f){
                echo '     ' . $f['FIELD_NAME'] . ' #' . $f['ID']. PHP_EOL;
            }
        }
        break;
    case "install":
        header("Content-Type: text/plain;charset=UTF-8");
        install();
        break;
    default:
        header('HTTP/1.1 400 Bad Request', true, 400);
        break;
}

function listTables(){
    $args = array('select' => array('ID', 'NAME', 'TABLE_NAME'));
    $res = array();
    $hlblock = HLBT::getList($args);
    while ( $row = $hlblock->fetch() ){
        $res[] = $row;
    }
    return $res;
}   //listTables

function exits($name){
    $tabs = listTables();
    $res   = false;
    foreach ($tabs as $t){
        if ( strcasecmp($name, $t["TABLE_NAME"]) == 0 ){
            $res = $t['ID'];
            break;
        }
    }
    return $res;
}   //exits

function fieldIds($table, $field){
    global $USER_FIELD_MANAGER;
    $res = new stdClass();
    $res->exists = false;
    $tabs = listTables();
    foreach ($tabs as $t){
        if ( strcasecmp($table, $t["TABLE_NAME"]) == 0 ){
            $res->tblId = $t['ID'];
            $fields = $USER_FIELD_MANAGER->GetUserFields('HLBLOCK_'.$res->tblId);
            foreach ($fields as $f){
                if ( strcasecmp($field, $f["FIELD_NAME"]) == 0 ){
                    $res->fieldId = $f['ID'];
                    $res->exists = true;
                    break;
                }
            }
            break;
        }
    }
    return $res;
}   //fieldIds

function install(){
    /**
     * HLBT: Staffing
     */
    if ( !exits('staffing') ){
        $arLangs = Array(
            'ru' => 'Должности',
            'en' => 'Staffing'
        );
        $result = HLBT::add(array(
            'NAME' => 'Staffing',
            'TABLE_NAME' => 'staffing'
        ));
        if ($result->isSuccess()) {
            $id = $result->getId();
            foreach($arLangs as $lang_key => $lang_val){
                HL\HighloadBlockLangTable::add(array(
                    'ID' => $id,
                    'LID' => $lang_key,
                    'NAME' => $lang_val
                ));
            }
            echo sprintf('#%d %s successed', $id, 'Staffing') . PHP_EOL;
            $UFObject = 'HLBLOCK_'.$id;
            $arFields = Array(
                'UF_NAME'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_NAME',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Должность', 'en'=>'Job title'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Должность', 'en'=>'Job title'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Должность', 'en'=>'Job title'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ),
                'UF_DISABLE'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_DISABLE',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Не использовать', 'en'=>'Not used'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Не использовать', 'en'=>'Not used'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Не использовать', 'en'=>'Not used'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                )
            );
            $arSavedFields = array();
            foreach($arFields as $arField){
                $obUserField  = new CUserTypeEntity();
                $fieldId = $obUserField->Add($arField);
                $arSavedFields[] = $fieldId;
            }
            echo 'Staffing fields ids:' . PHP_EOL;
            print_r($arSavedFields);
        } else {
            $errors = $result->getErrorMessages();
            echo 'ERR (Staffing)' . PHP_EOL;
            print_r($errors);
            return;
        }
    } else {
        echo 'Staffing HLBL already exists' . PHP_EOL;
    } //staffing--------------------------------------------------------------
    
    /**
     * Eployees HLBT
     */
    if ( !exits('Employees') ){
        $arLangs = Array(
            'ru' => 'Сотрудники',
            'en' => 'Employees'
        );
        $result = HLBT::add(array(
            'NAME' => 'Employees',
            'TABLE_NAME' => 'employees'
        ));
        if ($result->isSuccess()) {
            $id = $result->getId();
            foreach($arLangs as $lang_key => $lang_val){
                HL\HighloadBlockLangTable::add(array(
                    'ID' => $id,
                    'LID' => $lang_key,
                    'NAME' => $lang_val
                ));
            }
            echo sprintf('#%d %s successed', $id, 'Employees') . PHP_EOL;
            
            $dvsIds = fieldIds('DepartmentCode', 'UF_NAME');
            $stfIds = fieldIds('Staffing', 'UF_NAME');
            
            $UFObject = 'HLBLOCK_'.$id;
            $arFields = Array(
                'UF_ADDED'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_ADDED',
                    'USER_TYPE_ID' => 'date',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ),
                'UF_USER'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_UID',
                    'USER_TYPE_ID' => 'integer',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Пользователь #', 'en'=>'User'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Пользователь #', 'en'=>'User Id'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Пользователь #', 'en'=>'User Id'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ),
                'UF_TITLE'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_TITLE',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Пользователь', 'en'=>'User'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Пользователь', 'en'=>'User'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Пользователь', 'en'=>'User'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ),
                'UF_DVS'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_DVS',
                    'USER_TYPE_ID' => 'hlblock',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Подразделение #', 'en'=>'Division #'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Подразделение #', 'en'=>'Division Id'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Подразделение #', 'en'=>'Division Id'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    'SETTINGS' => array(
                        'HLBLOCK_ID' => $dvsIds->tblId,
                        'HLFIELD_ID' => $dvsIds->fieldId,
                        'DISPLAY' => 'LIST'
                    )
                ),
                'UF_STAFF'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_STAFF',
                    'USER_TYPE_ID' => 'hlblock',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Должность #', 'en'=>'Staff #'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Должность #', 'en'=>'Staff Id'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Должность #', 'en'=>'Staff Id'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    'SETTINGS' => array(
                        'HLBLOCK_ID' => $stfIds->tblId,
                        'HLFIELD_ID' => $stfIds->fieldId,
                        'DISPLAY' => 'LIST'
                    )
                ),
                'UF_END'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_END',
                    'USER_TYPE_ID' => 'date',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Дата увольнения', 'en'=>'Date end'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Дата увольнения', 'en'=>'Date end'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Дата увольнения', 'en'=>'Date end'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                )
            );
            $arSavedFields = array();
            foreach($arFields as $arField){
                $obUserField  = new CUserTypeEntity();
                $fieldId = $obUserField->Add($arField);
                $arSavedFields[] = $fieldId;
            }
            echo 'Employees fields ids:' . PHP_EOL;
            print_r($arSavedFields);
        } else {
            $errors = $result->getErrorMessages();
            echo 'ERR (employees)' . PHP_EOL;
            print_r($errors);
            return;
        }
    } else {
        echo 'Employees HLBT already exists' . PHP_EOL;
    }
}