<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 05.10.2018
 * Time: 11:58
 */

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader,
    Bitrix\Main\Web\Json,
    Bitrix\Iblock,
    Bitrix\Catalog,
    Bitrix\Currency;
//use CEventType;
//use CEventMEssage;
use Bitrix\Main\Config\Option;
use Bitrix\Iblock\IblockFieldTable;

$module_id = 'interlabs.feedbackform';

if (!Loader::includeModule('iblock') || !Loader::includeModule($module_id)) {
    return;
}

//iblock
$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);
$arIBlockType = CIBlockParameters::GetIBlockTypes();


$arIBlock = array();
$iblockFilter = !empty($arCurrentValues['IBLOCK_TYPE'])
    ? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
    : array('ACTIVE' => 'Y');

$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch()) {
    $id = (int)$arr['ID'];
    if (isset($offersIblock[$id]))
        continue;
    $arIBlock[$id] = '[' . $id . '] ' . $arr['NAME'];
}

//IBLOCK_FIELDS_USE
$arFields = [];
if ((int)$arCurrentValues['IBLOCK_ID'] > 0) {
    $rsProperty = PropertyTable::getList(['filter' => ['IBLOCK_ID' => (int)$arCurrentValues['IBLOCK_ID']]]);
    /*
        array(26) { ["ID"]=> string(2) "37"
       ["IBLOCK_ID"]=> string(1) "7"
       ["NAME"]=> string(14) "телефон"
       ["ACTIVE"]=> string(1) "Y"
       ["SORT"]=> string(3) "500"
       ["CODE"]=> string(5) "PHONE"
       ["DEFAULT_VALUE"]=> string(0) ""
       ["PROPERTY_TYPE"]=> string(1) "S"
       ["ROW_COUNT"]=> string(1) "1"
       ["COL_COUNT"]=> string(2) "30"
       ["LIST_TYPE"]=> string(1) "L"
       ["MULTIPLE"]=> string(1) "N"
       ["XML_ID"]=> NULL
       ["FILE_TYPE"]=> string(0) ""
       ["MULTIPLE_CNT"]=> string(1) "5"
       ["TMP_ID"]=> NULL
       ["LINK_IBLOCK_ID"]=> string(1) "0"
       ["WITH_DESCRIPTION"]=> string(1) "N"
       ["SEARCHABLE"]=> string(1) "N"
       ["FILTRABLE"]=> string(1) "N"
       ["IS_REQUIRED"]=> string(1) "N"
       ["VERSION"]=> string(1) "1"
       ["USER_TYPE"]=> NULL
       ["USER_TYPE_SETTINGS"]=> NULL
       ["HINT"]=> string(0) "" }
        * */
    while ($arProperty = $rsProperty->fetch()) {
        $arFields[$arProperty['CODE']] = $arProperty['CODE'] . " [PROPERTY] " . ($arProperty['CODE'] !== $arProperty['NAME'] ? $arProperty['NAME'] : '');
    }

    //$res = IblockFieldTable::getList(['filter' => ['IBLOCK_ID' => (int)$arCurrentValues['IBLOCK_ID']]]);
    /**
     * array(4) {
     * ["IBLOCK_ID"]=> string(1) "7"
     * ["FIELD_ID"]=> string(4) "NAME"
     * ["IS_REQUIRED"]=> string(1) "Y"
     * ["DEFAULT_VALUE"]=> string(0) "" }
     */
    //while ($field = $res->Fetch()) {
    //    $arFields[$field['FIELD_ID']] = $field['FIELD_ID'] . " [FIELD]";
    //}


}


//messageType
$arEventType = [];
$rsIBlockType = CEventType::GetList(array("sort" => "asc"), array("ACTIVE" => "Y"));
while ($arr = $rsIBlockType->Fetch()) {
    if ($arr['LID'] != LANGUAGE_ID) {
        continue;
    }
    $arEventType[$arr["EVENT_NAME"]] = "[" . $arr["ID"] . "] " . $arr["NAME"];
}

//CEventMEssage
$arEventMEssage = [];
$by = "id";
$order = array("sort" => "asc");
$filter = array();
$rsIBlockType = CEventMEssage::GetList($by, $order, $filter);
while ($arr = $rsIBlockType->Fetch()) {
    /*if ($arr['LID'] != LANGUAGE_ID) {
        continue;
    }*/
    $arEventMEssage[$arr["ID"]] = "[" . $arr["ID"] . "] " . $arr["EVENT_TYPE"];
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "EMAIL_FROM" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("EMAIL_FROM"),
            "TYPE" => "STRING",
            "DEFAULT" => Option::get($module_id, 'EMAIL_FROM', '')
        ],
        "EMAIL_TO" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("EMAIL_TO"),
            "TYPE" => "STRING",
            "DEFAULT" => Option::get($module_id, 'EMAIL_TO', '')
        ],
        "SUBJECT" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("SUBJECT"),
            "TYPE" => "STRING",
            "DEFAULT" => Option::get($module_id, 'subject', 'Interlabs - form')
        ],
        "USE_CAPTCHA" => [//
            "PARENT" => "BASE",
            "NAME" => GetMessage("USE_CAPTCHA"),
            'TYPE' => 'CHECKBOX',
            "DEFAULT" => "Y"
        ],
        "EVENT_TYPE" => [//
            "PARENT" => "BASE",
            "NAME" => GetMessage("EVENT_TYPE"),
            'TYPE' => 'LIST',
            "DEFAULT" => Option::get($module_id, 'EVENT_NAME', "INTERLABS_FEEDBACK"),
            "VALUES" => $arEventType,
            'REFRESH' => 'Y',
        ],
        "MESSAGE_ID" => [//
            "PARENT" => "BASE",
            "NAME" => GetMessage("MESSAGE_ID"),
            'TYPE' => 'LIST',
            "DEFAULT" => Option::get($module_id, 'MESSAGE_ID', ""),
            "VALUES" => $arEventMEssage,
            'REFRESH' => 'Y',
        ],

        'IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('IBLOCK_TYPE'),
            'TYPE' => 'LIST',
            'VALUES' => $arIBlockType,
            'REFRESH' => 'Y',
        ),
        'IBLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('IBLOCK_ID'),
            'TYPE' => 'LIST',
            'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arIBlock,
            'REFRESH' => 'Y',
        ),

        'IBLOCK_FIELDS_USE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('IBLOCK_FIELDS_USE'),//'Свойства инфоблока на форме'
            'TYPE' => 'LIST',
            'MULTIPLE' => 'Y',
            //'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arFields,
            //'REFRESH' => 'Y',
        ),

        'IBLOCK_FIELD_EMAIL' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('IBLOCK_FIELD_EMAIL'),//'Поле email',
            'TYPE' => 'LIST',
            'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arFields,
            //'REFRESH' => 'Y',
        ),
        'IBLOCK_FIELD_PHONE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('IBLOCK_FIELD_PHONE'),//'Поле телефон',
            'TYPE' => 'LIST',
            'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arFields,
            //'REFRESH' => 'Y',
        ),

        'FIELD_CHECK' => array(
            'PARENT' => 'SORT_SETTINGS',
            'NAME' => GetMessage("FIELD_CHECK"),
            'MULTIPLE' => 'Y',
            'TYPE' => 'LIST',
            'VALUES' => $arFields,
            'DEFAULT' => '',
            'ADDITIONAL_VALUES' => 'Y',
        ),

        "AGREE_PROCESSING" => [  //
            "PARENT" => "BASE",
            "NAME" => GetMessage("AGREE_PROCESSING"),
            'TYPE' => 'CHECKBOX',
            "DEFAULT" => "Y",
        ],
        "MAX_FILE_COUNT" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("MAX_FILE_COUNT"),
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ],
        "MAX_FILE_SIZE" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("MAX_FILE_SIZE"),
            "TYPE" => "STRING",
            "DEFAULT" => "5",
        ],
        "FORM_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("FORM_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "AJAX_MODE" => array(),

    ),
);