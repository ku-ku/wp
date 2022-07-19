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
                print_r($f);
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
     * User group
     */
    $groupId = "WP_PLANNING";
    $f = "ID";
    $sort = "ASC";
    $filter = array("STRING_ID"=>$groupId);
    $res = CGroup::GetList($f, $sort, $filter);

    if ( $res->SelectedRowsCount() < 1) {
        $group = new CGroup();

        $arFields = Array(
            "ACTIVE"       => "Y",
            "C_SORT"       => 555,
            "NAME"         => "Планирование мероприятий",
            "DESCRIPTION"  => "Пользователи планирования мероприятий",
            "STRING_ID"    => $groupId
        );
        $groupId = $group->Add($arFields);
        echo 'Group ' . $groupId . ' added: ' . $group->LAST_ERROR . PHP_EOL;
    } else {
        echo 'Group ' . $groupId . ' exists' . PHP_EOL;
    }

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
                ),
                'UF_SORT'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_SORT',
                    'USER_TYPE_ID' => 'integer',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Порядок', 'en'=>'Sort order'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Порядок', 'en'=>'Sort order'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Порядок', 'en'=>'Sort order'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "100"
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
            
            $dvsIds = fieldIds('department_codes', 'UF_NAME');
            $stfIds = fieldIds('Staffing', 'UF_NAME');
            
            $UFObject = 'HLBLOCK_'.$id;
            $arFields = Array(
                'UF_ADDED'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'USER_TYPE_ID' => 'date',
                    'FIELD_NAME' => 'UF_ADDED',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ),
                'UF_UID'=>Array(
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
                'UF_EMPNAME'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_EMPNAME',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'ФИО', 'en'=>'User Name'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'ФИО', 'en'=>'User Name'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'ФИО', 'en'=>'User Name'), 
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
    }   //-- Employees -------------------------------------------------------------------

    /** Actions & Reds HLBT */
    if ( !exits('WpActions') ){
        $arLangs = Array(
            'ru' => 'План мероприятий',
            'en' => 'Actions Plan'
        );
        $result = HLBT::add(array(
            'NAME' => 'WpActions',
            'TABLE_NAME' => 'wpactions'
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
            echo sprintf('#%d %s successed', $id, 'WpActions') . PHP_EOL;
            
            $dvsIds = fieldIds('department_codes', 'UF_NAME');
            $stfIds = fieldIds('Staffing', 'UF_NAME');
            $empIds = fieldIds('Employees', 'UF_EMPNAME');
            
            $UFObject = 'HLBLOCK_'.$id;
            $arFields = Array(
                'UF_ADT'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_ADT',
                    'USER_TYPE_ID' => 'datetime',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Дата проведения', 'en'=>'Action date'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Дата проведения', 'en'=>'Action date'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Дата проведения', 'en'=>'Action date'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>'')
                ),
                'UF_RED'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_RED',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Праздник', 'en'=>'Red day'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Праздник', 'en'=>'Red day'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Праздник', 'en'=>'Red day'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )                    
                ),
                'UF_DVS'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_DVS',
                    'USER_TYPE_ID' => 'hlblock',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Подразделение', 'en'=>'Division'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Подразделение', 'en'=>'Division'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Подразделение', 'en'=>'Division'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        'HLBLOCK_ID' => $dvsIds->tblId,
                        'HLFIELD_ID' => $dvsIds->fieldId,
                        'DISPLAY' => 'LIST'
                    )
                ),
                'UF_GRATTR'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_GRATTR',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Согласно графику', 'en'=>'on a schedule'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Согласно графику', 'en'=>'on a schedule'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Согласно графику', 'en'=>'on a schedule'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                ),
                'UF_DAYATTR'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_DAYATTR',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'В течении дня', 'en'=>'By day'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'В течении дня', 'en'=>'By day'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'В течении дня', 'en'=>'By day'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                ),
                'UF_YEARATTR'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_YEARATTR',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Ежегодный', 'en'=>'Yearly'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Ежегодный', 'en'=>'Yearly'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Ежегодный', 'en'=>'Yearly'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                ),
                'UF_SPECATTR'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_SPECATTR',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Особой важности', 'en'=>'Special'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Особой важности', 'en'=>'Special'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Особой важности', 'en'=>'Special'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                ),
                'UF_WWWATTR'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_WWWATTR',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'www-публикация', 'en'=>'Special'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'www-публикация', 'en'=>'Special'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'www-публикация', 'en'=>'Special'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                ),
                'UF_READY'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_READY',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'ГОТОВО', 'en'=>'Ready'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'ГОТОВО', 'en'=>'Ready'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'ГОТОВО', 'en'=>'Ready'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                ),
                'UF_TEXT'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_TEXT',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Наименование', 'en'=>'Name'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Наименование', 'en'=>'Name'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Наименование', 'en'=>'Name'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ),
                'UF_PLACE'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_PLACE',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Место проведения', 'en'=>'Place'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Место проведения', 'en'=>'Place'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Место проведения', 'en'=>'Place'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ),
                'UF_CHIEF'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_CHIEF',
                    'USER_TYPE_ID' => 'hlblock',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Руководитель', 'en'=>'The chief'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Руководитель', 'en'=>'The chief'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Руководитель', 'en'=>'The chief'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        'HLBLOCK_ID' => $empIds->tblId,
                        'HLFIELD_ID' => $empIds->fieldId,
                        'DISPLAY' => 'LIST'
                    )                    
                ),
                'UF_STATUS'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_STATUS',
                    'USER_TYPE_ID' => 'integer',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Статус', 'en'=>'Status'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Статус', 'en'=>'Status'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Статус', 'en'=>'Status'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "SETTINGS" => array(
                        "DEFAULT_VALUE" => "0"
                    )
                ),
                'UF_COMMENTS'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_COMMENTS',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Дополнительно', 'en'=>'Comments'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Дополнительно', 'en'=>'Comments'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Дополнительно', 'en'=>'Comments'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>'')
                ),
                'UF_AUTHOR'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_AUTHOR',
                    'USER_TYPE_ID' => 'integer',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Автор записи', 'en'=>'Author'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Автор записи', 'en'=>'Author'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Автор записи', 'en'=>'Author'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>'')
                ),
                'UF_INSTIME'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_INSTIME',
                    'USER_TYPE_ID' => 'datetime',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Время записи', 'en'=>'Ins time'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Время записи', 'en'=>'Ins time'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Время записи', 'en'=>'Ins time'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>'')
                ),
                'UF_META'=>Array(
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_META',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'Дополнительно', 'en'=>'Meta'), 
                    "LIST_COLUMN_LABEL" => Array('ru'=>'Дополнительно', 'en'=>'Meta'),
                    "LIST_FILTER_LABEL" => Array('ru'=>'Дополнительно', 'en'=>'Meta'), 
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>'')
                ),
            );  //fields
            $arSavedFields = array();
            foreach($arFields as $arField){
                $obUserField  = new CUserTypeEntity();
                $fieldId = $obUserField->Add($arField);
                $arSavedFields[] = $fieldId;
            }
            echo 'WpActions fields ids:' . PHP_EOL;
            print_r($arSavedFields);
        } else {
            $errors = $result->getErrorMessages();
            echo 'ERR (WpActions)' . PHP_EOL;
            print_r($errors);
            return;
        }
    } else {
        echo 'WpActions HLBT already exists' . PHP_EOL;
    }  //-- WpActions --------------------------------------------------------------------------------------------------------
}