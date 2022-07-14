<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 19.10.2018
 * Time: 10:03
 */

/*
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}*/

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
/*use CBitrixComponent;
use CUser;*/
use Bitrix\Iblock\IblockFieldTable;
use Bitrix\Main\Mail\Event;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

/*if (!class_exists('PropertyTable')) {
  require_once ("/bitrix/modules/iblock/lib/property.php");
}*/
if (!class_exists('PropertyEnumerationTable')) {
  require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/lib/propertyenumeration.php";
}


class Feedbackform extends CBitrixComponent
{
    public function __construct($component = null)
    {
        parent::__construct($component);


    }

    /**
     * @param string $module_id
     * @param $APPLICATION
     * @param $USER
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    protected function prepareParams($module_id = '', $APPLICATION, $USER)
    {
        if (isset($this->arParams["FIELD_CHECK"])) {
        } else {
            $this->arParams["FIELD_CHECK"] = [];
        }

        $this->arResult["CAPTCHA_CODE"] = null;
        if (!isset($this->arParams['USE_CAPTCHA'])) {
            $this->arParams['USE_CAPTCHA'] = 'N';
        }
        if ($this->arParams['USE_CAPTCHA'] === 'Y') {
            $this->arResult["CAPTCHA_CODE"] = htmlspecialchars($APPLICATION->CaptchaGetCode());
        }


        if (!isset($this->arParams['AJAX_MODE'])) {
            $this->arParams['AJAX_MODE'] = 'N';
        }
        $this->arResult['AJAX_MODE'] = $this->arParams['AJAX_MODE'];

        if (!isset($this->arParams['SUBJECT'])) {
            $this->arParams['SUBJECT'] = Option::get($module_id, 'subject', '');
        }

        $this->arResult['AGREE_PROCESSING'] = $this->arParams['AGREE_PROCESSING'];

        $this->arResult['SUBJECT'] = $this->arParams['SUBJECT'];

        $this->arResult['FIELDS'] = $this->getIBLOCK_FIELDS_USE(intval($this->arParams['IBLOCK_ID']));

        // preset form fields
        $this->arResult['form'] = [
            'NAME' => '',
            'PHONE' => '',
            'EMAIL' => '',

        ];
        if ($USER->IsAuthorized()) {
            $this->arResult['form']['NAME'] = $USER->GetFullName();
            $this->arResult['form']['PHONE'] = '';
            $this->arResult['form']['EMAIL'] = $USER->GetEmail();

            $rsUser = CUser::GetByID($USER->GetID()); //$USER->GetID() - получаем ID авторизованного пользователя  и сразу же - его поля
            $arUser = $rsUser->Fetch();
            $this->arResult['form']["PHONE"] = $arUser['PERSONAL_PHONE'];
        } else {

        }
    }

    /**
     * @return mixed|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        $this->arResult['validateErrors'] = [];
        $this->arResult['success'] = null;

        $module_id = 'interlabs.feedbackform';

        global $USER;
        global $APPLICATION;

        $this->prepareParams($module_id, $APPLICATION, $USER);

        if (Loader::includeModule($module_id)) {

            $this->arResult['isSaveFeedback'] = false; // flag data is save
            $this->arResult['validateErrors'] = [
                //[
                //    'message' => '',
                //]
            ];
            $this->arResult['saveFeedbackBy'] = [];


            $request = Context::getCurrent()->getRequest();
            $data = $request->toArray();



            if ($request->isPost() && $request->get('interlabs__feedbackform') === 'Y' && $request->get('interlabs__feedbackform_FORM_ID') === $this->arParams['FORM_ID']) {// save data

                $dataIBlock = [];
                $dataSend = [
                    'BODY' => []
                ];

                $IBLOCK_ID = intval($this->arParams["IBLOCK_ID"]);
                $this->loadFieldsAndProps($IBLOCK_ID);
                $fieldsUse = $this->getIBLOCK_FIELDS_USE($IBLOCK_ID);

                $serviceFields = [
                    strtolower('captcha_sid'),
                    strtolower('captcha_word'),
                    strtolower('AGREE_PROCESSING'),
                    strtolower('interlabs__feedbackform'),
                    strtolower('AJAX_CALL'),
                    strtolower('bitrix_include_areas'),
                    strtolower('clear_cache'),
                    strtolower('sessid'),
                    strtolower('bxajaxid'),
                    strtolower('interlabs__feedbackform_FORM_ID'),
                ];

                foreach ($data as $field => $value) {
                    if (in_array(strtolower($field), $serviceFields)) {
                        continue;
                    }

                    if ($field === $this->arParams['IBLOCK_FIELD_EMAIL']) {
                        $field = $this->arParams['IBLOCK_FIELD_EMAIL'];
                    }
                    if ($field === $this->arParams['IBLOCK_FIELD_PHONE']) {
                        $field = $this->arParams['IBLOCK_FIELD_PHONE'];
                    }

                    $dataIBlock[$field] = $value;
                    $dataSend[$field] = $value;


                    //if exists property in IBLOCK
                    if (isset($this->properties[$field]) && isset($this->properties[$field]['NAME']) && $this->properties[$field]['NAME'] != '') {

                        //its list property
                        if (isset($this->propertiesForRender[$field]['VALUES']) && count($this->propertiesForRender[$field]['VALUES']) > 0) {

                            if (is_array($value)) {//multiple value of list

                                $r = [];
                                $links = [];
                                $names = [];
                                foreach ($value as $v) {
                                    if (isset($this->propertiesForRender[$field]['VALUES'][$v])) {
                                        $r[] = $this->propertiesForRender[$field]['VALUES'][$v];
                                        $links[] = $this->propertiesForRender[$field]['URLS'][$v];
                                        if ($this->propertiesForRender[$field]['URLS'][$v]) {
                                            $names[] = "<a href=\"//{$this->propertiesForRender[$field]['URLS'][$v]}\">{$this->propertiesForRender[$field]['VALUES'][$v]}</a>";
                                        } else {
                                            $names[] = $this->propertiesForRender[$field]['VALUES'][$v];
                                        }

                                    } else {
                                        $r[] = $v;
                                        $links[] = $v;
                                    }
                                }
                                //$dataSend['BODY'][] = $this->properties[$field]['NAME'] . ":" . implode(', ', $r);
                                $dataSend['BODY'][] = $this->properties[$field]['NAME'] . ":" . implode(', ', $names);
                                $dataSend[$field . '-id'] = $value;
                                $dataSend[$field . '-name'] = implode(', ', $names);
                                $dataSend[$field . '-link'] = implode(', ', $links);

                            } else {// single values of list

                                if (isset($this->propertiesForRender[$field]['VALUES'][$value])) {
                                    $dataSend[$field . '-id'] = $value;
                                    $dataSend[$field . '-name'] = $this->propertiesForRender[$field]['VALUES'][$value];
                                    $dataSend[$field . '-link'] = $this->propertiesForRender[$field]['URLS'][$value];
                                    if ($this->propertiesForRender[$field]['URLS'][$value]) {

                                        $dataSend['BODY'][] = $this->propertiesForRender[$field]['NAME'] . ":" .
                                            "<a href=\"//{$this->propertiesForRender[$field]['URLS'][$value]}\">{$this->propertiesForRender[$field]['VALUES'][$value]}</a>";
                                    } else {

                                        $row = $this->propertiesForRender[$field]['NAME'] . ":" . $this->propertiesForRender[$field]['VALUES'][$value];

                                        $dataSend['BODY'][] = $row;


                                    }

                                } else {

                                    $product = $this->getProductByOffer(intval($value));
                                    $row = $this->propertiesForRender[$field]['NAME'] . ":" . $value;
                                    $dataSend[$field . '-id'] = $value;
                                    $dataSend[$field . '-name'] = $value;
                                    $dataSend[$field . '-link'] = $value;

                                    if ($product) {
                                        $row = $this->propertiesForRender[$field]['NAME'] . ":" .
                                            "<a href=\"//" . SITE_SERVER_NAME . $product['DETAIL_PAGE_URL'] . "\">{$product['NAME']}</a>";
                                        $dataSend[$field . '-id'] = $value;
                                        $dataSend[$field . '-name'] = $product['NAME'];
                                        $dataSend[$field . '-link'] = $product['DETAIL_PAGE_URL'];
                                    }


                                    $dataSend['BODY'][] = $row;
                                }
                            }
                        } else {
                            //nothing

                            $row = $this->properties[$field]['NAME'] . ":" . $value;
                            $product = $this->getProductByOffer(intval($value));
                            if ($product) {
                                $row = $this->properties[$field]['NAME'] . ":" .
                                    "<a href=\"//" . SITE_SERVER_NAME . $product['DETAIL_PAGE_URL'] . "\">{$product['NAME']}</a>";
                                $dataSend[$field . '-id'] = $value;
                                $dataSend[$field . '-name'] = $product['NAME'];
                                $dataSend[$field . '-link'] = $product['DETAIL_PAGE_URL'];
                            }
                            $dataSend['BODY'][] = $row;

                        }
                    } elseif (isset($this->fields[$field]) && $this->fields[$field]['NAME'] && $this->fields[$field]['NAME'] !== '') {

                        $dataSend['BODY'][] = $this->fields[$field]['NAME'] . ":" . $value;

                    } else {

                        $row = $field . ":" . $value;
                        $product = $this->getProductByOffer(intval($value));
                        if ($product) {
                            $row = $field . ":" .
                                "<a href=\"//" . SITE_SERVER_NAME . $product['DETAIL_PAGE_URL'] . "\">{$product['NAME']}</a>";
                            $dataSend[$field . '-id'] = $value;
                            $dataSend[$field . '-name'] = $product['NAME'];
                            $dataSend[$field . '-link'] = $product['DETAIL_PAGE_URL'];
                        }
                        $dataSend['BODY'][] = $row;
                    }
                    // validate
                    if (in_array($field, $this->arParams["FIELD_CHECK"]) &&
                        strpos($field, '_label') === false) {
                        if (trim(strval($value)) === '') {
                            $this->arResult['validateErrors'][] = [
                                'message' => Loc::getMessage('ERROR_FIELD_REQUIRED'),
                                'field' => $field
                            ];
                        }
                    }


                }


                $dataSend['BODY'] = implode("<br/>\r\n", $dataSend['BODY']);


                if ($this->arParams['USE_CAPTCHA'] === 'Y') {
                    $captcha = new CCaptcha();
                    if (!strlen($_REQUEST["captcha_word"]) > 0) {
                        $this->arResult['validateErrors'][] = [
                            'message' => Loc::getMessage('ERROR_NO_CAPTCHA_CODE'),
                            'field' => 'captcha_word'
                        ];

                    } elseif (!$captcha->CheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"])) {
                        $this->arResult['validateErrors'][] = [
                            'message' => Loc::getMessage('ERROR_CAPTCHA_CODE_WRONG'),
                            'field' => 'captcha_word'
                        ];
                    }
                }

                if ($this->arParams['AGREE_PROCESSING'] === 'Y') {
                    if (isset($_REQUEST['AGREE_PROCESSING']) && strval($_REQUEST['AGREE_PROCESSING']) === 'Y') {
                        // ok
                    } else {
                        $this->arResult['validateErrors'][] = [
                            'message' => Loc::getMessage('ERROR_AGREE_REQUIRED'),
                            'field' => 'AGREE_PROCESSING'
                        ];
                    }
                }


                $filesFieldNameFilesIds = [];// => [ field=>array<int> ]
                if (count($this->arResult['validateErrors']) === 0) {
                    $filesFieldNameFilesIds = $this->saveFiles(
                        $module_id,
                        $this->arResult['validateErrors'],
                        intval($this->arParams['MAX_FILE_COUNT']),
                        intval($this->arParams['MAX_FILE_SIZE'])
                    );
                }

                //save
                $this->saveInIBlock($module_id, $filesFieldNameFilesIds, $dataIBlock);


                //send
                $this->send($module_id, $filesFieldNameFilesIds, $dataSend, $dataIBlock);
            }

            $this->arResult['AGREE_PROCESSING_TEXT'] = null;
            $this->arResult['AGREE_PROCESSING_FILE'] = null;
            $AGREE_PROCESSING_TEXT = Option::get($module_id, 'AGREE_PROCESSING_TEXT', '');
            if ($AGREE_PROCESSING_TEXT) {
                $this->arResult['AGREE_PROCESSING_TEXT'] = $AGREE_PROCESSING_TEXT;
            } else {
                $AGREE_PROCESSING_FILE_ID = Option::get($module_id, 'AGREE_PROCESSING_FILE_ID', '');
                if ($AGREE_PROCESSING_FILE_ID) {
                    $arFile = CFile::GetFileArray($AGREE_PROCESSING_FILE_ID);
                    if ($arFile) {
                        $this->arResult['AGREE_PROCESSING_FILE'] = $arFile;
                    }
                }
            }


            if (isset($data['AJAX_CALL']) && $data['AJAX_CALL'] === 'Y' && $request->get('interlabs__feedbackform') === 'Y' && $request->get('interlabs__feedbackform_FORM_ID') === $this->arParams['FORM_ID']) {// json response
                $result = [
                    /**
                     * @var mixwd
                     */
                    'data' => ["message" => ''],
                    /**
                     * @var Null | array<[ 'message' => string ]>
                     */
                    'errors' => count($this->arResult['validateErrors']) > 0 ? $this->arResult['validateErrors'] : null
                ];
                $this->jsonResponse($result);
            } else { //html render
                $this->AbortResultCache();
                $this->IncludeComponentTemplate();
            }


        } else {
            //nothing, module is off
        }
    }

    /**
     * @param int $ID
     * @return array|null
     */
    protected function getProductByOffer($ID = 0)
    {
        $arSelect = Array("LANG_ID", "IBLOCK_ID", "ID", "NAME", 'DETAIL_PAGE_URL', 'PROPERTY_CML2_LINK');
        $arSort = ['NAME' => 'ASC'];
        //offer
        $arFilter = Array("ID" => intval($ID), "ACTIVE" => "Y");
        $offer = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect)->getNext();
        if ($offer && $offer['PROPERTY_CML2_LINK_VALUE']) {

            $arSelect = [];
            $arFilter = Array("ID" => intval($offer['PROPERTY_CML2_LINK_VALUE']), "ACTIVE" => "Y");
            $product = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect)->getNext();

            if ($product) {
                return $product;
            }
        } elseif ($offer && !$offer['PROPERTY_CML2_LINK_VALUE'] && $offer['DETAIL_PAGE_URL']) {
            // its a product
            return $offer;
        }
        return null;
    }

    /**
     * Get Email from arParams Or module Or mainModule
     * @param string $module_id
     * @param string $email EMAIL_FROM|EMAIL_TO
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    protected function getEmail($module_id = '', $email = '')
    {
        $result = '';

        if (isset($this->arParams[$email]) && $this->arParams[$email]) {
            $result = $this->arParams[$email];
        } else if (Option::get($module_id, $email, '')) {
            $result = Option::get($module_id, $email, '');
        } else {
            $result = Option::get('main', strtolower($email), '');
        }

        return $result;
    }

    /**
     * @param string $module_id
     * @param array $filesFieldNameFilesIds [field=>array<int>]
     * @param array $dataSend
     * @param array $dataIBlock
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    protected function send($module_id = '', $filesFieldNameFilesIds = [], $dataSend = [], $dataIBlock = [])
    {
        $EVENT_NAME = (isset($this->arParams["EVENT_NAME"]) && $this->arParams["EVENT_NAME"]) ? $this->arParams["EVENT_NAME"] : Option::get($module_id, 'EVENT_NAME', null);
        $MESSAGE_ID = intval(
            (isset($this->arParams["MESSAGE_ID"]) && $this->arParams["MESSAGE_ID"]) ?
                $this->arParams["MESSAGE_ID"]
                :
                Option::get($module_id, 'MESSAGE_ID', 0)
        );
        $filesIds = [];
        array_map(function ($ids) use (&$filesIds) {
            $filesIds = array_merge($filesIds, $ids);
        }, $filesFieldNameFilesIds);

        if ($EVENT_NAME && $MESSAGE_ID > 0 && count($this->arResult['validateErrors']) === 0) {

            $eventData = [
                "EVENT_NAME" => $EVENT_NAME,
                "LID" => SITE_ID,//'s1'
                "C_FIELDS" => array(
                    "EMAIL_FROM" => $this->getEmail($module_id, 'EMAIL_FROM'),
                    "EMAIL_TO" => $this->getEmail($module_id, 'EMAIL_TO'),
                    "SUBJECT" => $this->arParams['SUBJECT'] ? $this->arParams['SUBJECT'] : Option::get($module_id, 'subject', 'Interlabs - form'),
                ),
                "FILE" => $filesIds,
                "MESSAGE_ID" => $MESSAGE_ID, //	Идентификатор почтового шаблона по которому будет отправлено письмо.
            ];

            foreach ($dataSend as $field => $value) {
                if (!isset($eventData['C_FIELDS'][$field])) {//not rewrite
                    $eventData['C_FIELDS'][$field] = $value;
                }
            }

            if (Event::send($eventData)) {
                $this->arResult['isSaveFeedback'] = true;
                $this->arResult['saveFeedbackBy'][] = [
                    'MESSAGE_ID' => $eventData["MESSAGE_ID"]
                ];
            }
        }
    }

    /**
     * @param string $module_id
     * @param array $filesFieldNameFilesIds [ field => array<int> ]
     * @param array $dataIBlock
     * @param array  array ['fields' => array<string>, 'properties' => array<string>]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function saveInIBlock($module_id = '', $filesFieldNameFilesIds = [], $dataIBlock = [])
    {
        $IBLOCK_ID = intval($this->arParams["IBLOCK_ID"]);
        if ($IBLOCK_ID > 0 && Loader::includeModule('iblock') && count($this->arResult['validateErrors']) === 0) {

            // all fields from `b_iblock_fields`, b_iblock_property
            $fieldsAndProperties = $this->loadFields(intval($IBLOCK_ID), $dataIBlock);
            $fields = $fieldsAndProperties['fields'];
            $properties = $fieldsAndProperties['properties'];

            $arLoadProductArray = [
                "IBLOCK_ID" => $IBLOCK_ID,
                //"PROPERTY_VALUES" => $PROP,
                "NAME" => $this->arParams['SUBJECT'] ? $this->arParams['SUBJECT'] : Loc::getMessage('SUBJECT_DEFAULT'),
                "ACTIVE" => "Y"
            ];

            $PROP = [];
            foreach ($dataIBlock as $k => $v) {
                if (in_array($k, $fields)) {
                    $arLoadProductArray[$k] = $v;
                } else {
                    $PROP[$k] = $v;
                }
            }
            if (count($filesFieldNameFilesIds) > 0) {// save files data
                foreach ($filesFieldNameFilesIds as $field => $filesIds) {
                    if (in_array($field, $properties)) {
                        if (count($filesIds) === 1) {//  MULTIPLE=N
                            $PROP[$field] = CFile::MakeFileArray($filesIds[0]);
                        } else {// MULTIPLE=Y
                            array_map(function ($fileId) use (&$PROP, $field) {
                                $PROP[$field][] = ["VALUE" => CFile::MakeFileArray($fileId)];
                            }, $filesIds);
                        }
                    } elseif (in_array($field, $fields)) {
                        if (count($filesIds) === 1) {//  MULTIPLE=N
                            $arLoadProductArray[$field] = CFile::MakeFileArray($filesIds[0]);
                        } else {// MULTIPLE=Y
                            array_map(function ($fileId) use (&$arLoadProductArray, $field) {
                                $arLoadProductArray[$field][] = ["VALUE" => CFile::MakeFileArray($fileId)];
                            }, $filesIds);
                        }
                    }

                }
            }

            $arLoadProductArray['PROPERTY_VALUES'] = $PROP;
            if (isset($USER)) {
                $arLoadProductArray['MODIFIED_BY'] = $USER->GetID();
            }


            $el = new CIBlockElement;
            if ($id = $el->Add($arLoadProductArray)) {
                //saved
                $this->arResult['isSaveFeedback'] = true;

                $this->arResult['saveFeedbackBy'][] = [
                    'IBLOCK_ID' => $IBLOCK_ID,
                    'ID' => $id
                ];


            } else {
                // error on save
                $this->arResult['validateErrors'][] = ['message' => $el->LAST_ERROR];
            }

        }
    }

    /**
     * @param string $module_id
     * @param array $validateErrors
     * @param int $maxFileCount
     * @param int $maxFileSize
     * @return array   [ field=>array<int> ]
     */
    protected function saveFiles($module_id = '', &$validateErrors = [], $maxFileCount = 10, $maxFileSize = 5)
    {
        $filesIds = [];
        $filesFieldNameFilesIds = [];
        $countFiles = 0;
        foreach ($_FILES as $fieldName => $f) {
            if (!in_array($fieldName, $this->arParams['IBLOCK_FIELDS_USE'])) {
                continue;
            }
            $filesFieldNameFilesIds[$fieldName] = [];

            $arFile = [];
            if (is_array($f['name'])) {//multiple
                for ($i = 0; $i < count($f['name']); $i++) {
                    $arFile = [];
                    foreach ($f as $key => $v) {
                        $arFile[$key] = $v[$i];
                    }
                    $arFile["del"] = ${$fieldName . "_del"};
                    $arFile["MODULE_ID"] = $module_id;
                    $fid = CFile::SaveFile($arFile, $module_id);
                    if (intval($fid) > 0) {
                        $filesIds[] = intval($fid);
                        $filesFieldNameFilesIds[$fieldName][] = intval($fid);
                        $countFiles++;
                        if ($countFiles > $maxFileCount) {
                            $validateErrors[] = [
                                'message' => Loc::getMessage('ERROR_ERROR_TO_MANY_FILES')
                            ];
                            //delete saved files;
                            array_map(function ($id) {
                                CFile::Delete($id);
                            }, $filesIds);
                            return [];
                        }
                        if (($arFile['size'] / 1024 / 1024) > $maxFileSize) {
                            $validateErrors[] = [
                                'message' => Loc::getMessage('ERROR_ERROR_FILE_SIZE_BIG') . ' ' . $arFile['name']
                            ];
                            //delete saved files;
                            array_map(function ($id) {
                                CFile::Delete($id);
                            }, $filesIds);
                            return [];
                        }
                    };
                }
            } else {
                $arFile = $f;
                $arFile["del"] = ${$fieldName . "_del"};
                $arFile["MODULE_ID"] = $module_id;
                $fid = CFile::SaveFile($arFile, $module_id);
                if (intval($fid) > 0) {
                    $filesIds[] = intval($fid);
                    $filesFieldNameFilesIds[$fieldName][] = intval($fid);
                    $countFiles++;
                    if ($countFiles > $maxFileCount) {
                        $validateErrors[] = [
                            'message' => Loc::getMessage('ERROR_TO_MANY_FILES')
                        ];
                        //delete saved files;
                        array_map(function ($id) {
                            CFile::Delete($id);
                        }, $filesIds);
                        return [];
                    }
                    if (($arFile['size'] / 1024 / 1024) > $maxFileSize) {
                        $validateErrors[] = [
                            'message' => Loc::getMessage('ERROR_FILE_SIZE_BIG') . $arFile['name']
                        ];
                        //delete saved files;
                        array_map(function ($id) {
                            CFile::Delete($id);
                        }, $filesIds);
                        return [];
                    }
                };
            }
        }
        return $filesFieldNameFilesIds;
    }

    /**
     * @var array<IblockFieldTable>
     */
    protected $fields = [];
    /**
     * @var array<PropertyTable>
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $propertiesForRender = [];

    /**
     * @param int $IBLOCK_ID
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function loadFieldsAndProps($IBLOCK_ID = 0)
    {
        if (Loader::includeModule('iblock')) {

            $res = IblockFieldTable::getList(['filter' => ['IBLOCK_ID' => $IBLOCK_ID]]);


            while ($field = $res->Fetch()) {
                $this->fields[$field['FIELD_ID']] = $field;
            }


            $rsProperty = PropertyTable::getList(['filter' => ['IBLOCK_ID' => $IBLOCK_ID]]);

            while ($arProperty = $rsProperty->fetch()) {
                $this->properties[$arProperty['CODE']] = $arProperty;
            };
        }
    }


    /**
     * Get all fields of iblock
     * @param int $IBLOCK_ID
     * @param array $dataIBlock
     * @return array ['fields' => array<string>, 'properties' => array<string>]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function loadFields($IBLOCK_ID = 0, &$dataIBlock = [])
    {
        $fields = [];
        $properties = [];
        if (Loader::includeModule('iblock')) {

            if (count($this->fields) == 0 || count($this->properties)) {
                $this->loadFieldsAndProps($IBLOCK_ID);
            }
            $_fields = [];

            foreach ($this->fields as $field) {
                $fields[] = $field['FIELD_ID'];
                $_fields[$field['FIELD_ID']] = $field;
            }
            $_property = [];
            foreach ($this->properties as $arProperty) {
                $_property[$arProperty['CODE']] = $arProperty;
                $properties[] = $arProperty['CODE'];
            }


            foreach ($dataIBlock as $f => &$value) {
                if (!in_array($f, $this->arParams['IBLOCK_FIELDS_USE'])) {
                    continue;
                }
                if (isset($_fields[$f])) {
                    switch ($_property[$f]['PROPERTY_TYPE']) {
                        case 'S':// - строка,
                            $value = strval($value);
                            break;
                        case 'N':// - число,
                            $value = floatval($value);
                            break;
                        case 'F':// - файл,
                            //its not possible, because $dataIBlock - is a POST array, its file id
                            //throw new Exception("{$f} is a file in IBLOCK {$IBLOCK_ID}");
                            //or intval -> get FileArray by id
                            $value = ["VALUE" => CFile::MakeFileArray(intval($value))];
                            break;
                        case 'L':// - список,
                            if (!is_array($value)) {
                                $value = [$value];
                            }
                            $VALUES = [];
                            foreach ($value as $v) {
                                $VALUES[$v] = [
                                    'VALUE' => array(
                                        'TYPE' => 'HTML', // или html
                                        'TEXT' => $v,
                                    ),
                                    "DESCRIPTION" => $v
                                ];
                            }
                            $value = $VALUES;
                            break;
                        case 'E':// - привязка к элементам,
                        case 'G':// - привязка к группам.
                        default:
                            //throw new Exception('Not Emplement');
                    }
                } elseif (isset($_property[$f])) {

                    switch ($_property[$f]['PROPERTY_TYPE']) {
                        case 'S':// - строка,
                            if ($_property[$f]["MULTIPLE"] == 'N') {
                                $value = strval($value);
                            } else {
                                $value = array_map(function ($v) {
                                    return [
                                        'VALUE' => array(
                                            'TYPE' => 'S:HTML', // или html
                                            'TEXT' => $v,
                                        ),
                                        "DESCRIPTION" => $v
                                    ];
                                }, $value);
                            }

                            break;
                        case 'N':// - число,
                            if ($_property[$f]["MULTIPLE"] == 'N') {
                                $value = floatval($value);
                            } else {
                                $value = array_map(function ($v) {
                                    return ['VALUE' => floatval($v), "DESCRIPTION" => ""];
                                }, $value);
                            }
                            break;
                        case 'F':// - файл,
                            //its not possible, because $dataIBlock - is a POST array, its file id
                            //throw new Exception("{$f} is a file in IBLOCK {$IBLOCK_ID}");
                            //or intval -> get FileArray by id
                            $value = ["VALUE" => CFile::MakeFileArray(intval($value))];
                            break;
                        case 'L':// - список,
                            if ($_property[$f]['PROPERTY_TYPE']['MULTIPLE'] === 'N') {
                                $value = ['VALUE' => $value];
                            } else {
                                if (!is_array($value)) {
                                    $value = [$value];
                                }
                                $VALUES = [];
                                foreach ($value as $v) {
                                    $VALUES[$v] = ['VALUE' => $v
                                        /*'VALUE' => array(
                                            'TYPE' => 'S:HTML', // или html
                                            'TEXT' => $v,
                                        ),
                                        "DESCRIPTION" => $v*/
                                    ];
                                }
                                $value = $VALUES;
                            }


                            break;
                        case 'E':// - привязка к элементам,
                        case 'G':// - привязка к группам.
                        default:
                            //throw new Exception('Not Emplement');
                    }
                } else {
                    // not exists property/field
                }
            }

        }
        return ['fields' => $fields, 'properties' => $properties];
    }


    protected function getIBLOCK_FIELDS_USE($IBLOCK_ID = 0)
    {
        $fieldsUse = [];

        $arFields = [];
        if ($IBLOCK_ID > 0) {
            $rsProperty = Bitrix\Iblock\PropertyTable::getList(['filter' => ['IBLOCK_ID' => $IBLOCK_ID]]);

            while ($arProperty = $rsProperty->fetch()) {
                $arFields[$arProperty['CODE']] = $arProperty;
            }

            /*$res = IblockFieldTable::getList(['filter' => ['IBLOCK_ID' => $IBLOCK_ID]]);
            while ($field = $res->Fetch()) {
                $arFields[$field['FIELD_ID']] = $field;
                $arFields[$field['FIELD_ID']] ['NAME'] = Loc::getMessage($field['FIELD_ID']);
            }*/
        }

        foreach ($arFields as $code => $field) {
            if (trim($code) === '') {
                continue;
            }

            $this->propertiesForRender[$code] = [
                'BITRIX_PROPERTY_TYPE' => '',
                'CODE' => $field,
                'NAME' => $arFields[$code]['NAME'], //Loc::getMessage($field)
                'REQUIRED' => in_array($code, $this->arParams['FIELD_CHECK']),
                'IS_EMAIL' => $code === $this->arParams['IBLOCK_FIELD_EMAIL'],
                'IS_PHONE' => $code === $this->arParams['IBLOCK_FIELD_PHONE'],
                'TYPE' => 'text',
                'MULTIPLE' => $arFields[$code]['MULTIPLE'],
                'URLS' => [],
                'VALUES' => []
            ];


            if (isset($arFields[$code]['PROPERTY_TYPE'])) {
                $this->propertiesForRender[$code]['BITRIX_PROPERTY_TYPE'] = $arFields[$code]['PROPERTY_TYPE'];
                if ($arFields[$code]['PROPERTY_TYPE'] === 'L') {
                    $this->propertiesForRender[$code]['TYPE'] = 'select'; // list_type==L
                    if ($arFields[$code]['LIST_TYPE'] === 'C') {
                        if ($arFields[$code]['MULTIPLE'] === 'Y') {
                            $this->propertiesForRender[$code]['TYPE'] = 'checkbox[]';
                        } else {
                            $this->propertiesForRender[$code]['TYPE'] = 'radio';
                        }
                    } else {
                        if ($arFields[$code]['MULTIPLE'] === 'Y') {
                            $this->propertiesForRender[$code]['TYPE'] .= '[]';
                        }
                    }
                    //b_iblock_property_enum
                    $arPropertyEnumerationTable = PropertyEnumerationTable::getList(
                        [
                            'select' => ['ID', 'VALUE'],
                            'filter' => ['PROPERTY_ID' => $arFields[$code]['ID']
                            ]
                        ]
                    );
                    $values = [];
                    while ($ar = $arPropertyEnumerationTable->fetch()) {
                        $values[$ar['ID']] = $ar['VALUE'];
                    }
                    $this->propertiesForRender[$code]['VALUES'] = $values;

                } elseif (isset($arFields[$code]['DEFAULT_VALUE']) && $arFields[$code]['DEFAULT_VALUE'] === 'a:2:{s:4:"TEXT";s:0:"";s:4:"TYPE";s:4:"HTML";}') {
                    $this->propertiesForRender[$code]['TYPE'] = 'textarea';
                } elseif ($arFields[$code]['PROPERTY_TYPE'] === 'S' && isset($arFields[$code]['USER_TYPE'])) {
                    if ($arFields[$code]['USER_TYPE'] === 'Date') {
                        $this->propertiesForRender[$code]['TYPE'] = 'datepicker';
                    }
                } elseif ($arFields[$code]['PROPERTY_TYPE'] === 'F') {
                    if ($arFields[$code]['MULTIPLE'] === 'Y') {
                        $this->propertiesForRender[$code]['TYPE'] = 'file[]';
                    } else {
                        $this->propertiesForRender[$code]['TYPE'] = 'file';
                    }
                } elseif ($arFields[$code]['PROPERTY_TYPE'] === 'E') {// element
                    if ($arFields[$code]['MULTIPLE'] === 'Y') {
                        $this->propertiesForRender[$code]['TYPE'] = 'select[]';
                    } else {
                        $this->propertiesForRender[$code]['TYPE'] = 'select';
                    }
                    $arSelect = Array("ID", "NAME", 'DETAIL_PAGE_URL', 'PROPERTY_CML2_LINK');
                    $arSort = ['NAME' => 'ASC'];
                    $arFilter = Array("IBLOCK_ID" => intval($arFields[$code]['LINK_IBLOCK_ID']), "ACTIVE" => "Y");
                    $res = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
                    $values = [];
                    while ($ob = $res->GetNextElement()) {
                        $arF = $ob->GetFields();
                        $values[$arF['ID']] = $arF['NAME'];
                        $this->propertiesForRender[$code]['URLS'][$arF['ID']] = SITE_SERVER_NAME . $arF['DETAIL_PAGE_URL'];
                    }
                    $this->propertiesForRender[$code]['VALUES'] = $values;
                } elseif ($arFields[$code]['PROPERTY_TYPE'] === 'G') { // section
                    if ($arFields[$code]['MULTIPLE'] === 'Y') {
                        $this->propertiesForRender[$code]['TYPE'] = 'select[]';
                    } else {
                        $this->propertiesForRender[$code]['TYPE'] = 'select';
                    }
                    $arSelect = Array("ID", "NAME", 'SECTION_PAGE_URL');
                    $arSort = ['NAME' => 'ASC'];
                    $arFilter = Array("IBLOCK_ID" => intval($arFields[$code]['LINK_IBLOCK_ID']), "ACTIVE" => "Y");
                    $res = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect, false);
                    $values = [];
                    while ($ob = $res->GetNextElement()) {
                        $arF = $ob->GetFields();
                        $values[$arF['ID']] = $arF['NAME'];
                        $this->propertiesForRender[$code]['URLS'][$arF['ID']] = SITE_SERVER_NAME . $arF['SECTION_PAGE_URL'];
                    }
                    $this->propertiesForRender[$code]['VALUES'] = $values;
                }

            }


        }
        if(is_array($this->arParams['IBLOCK_FIELDS_USE']) and !empty($this->arParams['IBLOCK_FIELDS_USE'])){
            foreach ($this->arParams['IBLOCK_FIELDS_USE'] as $field) {
                if (trim($field) === '') {
                    continue;
                }
                $fieldsUse[$field] = [
                    'CODE' => $field,
                    'NAME' => Loc::getMessage($field),
                    'REQUIRED' => in_array($field, $this->arParams['FIELD_CHECK']),
                    'IS_EMAIL' => $field === $this->arParams['IBLOCK_FIELD_EMAIL'],
                    'IS_PHONE' => $field === $this->arParams['IBLOCK_FIELD_PHONE'],
                    'TYPE' => 'text',
                    'MULTIPLE' => $arFields[$field]['MULTIPLE'],
                    'URLS' => [],
                    'VALUES' => []
                ];
                if (isset($this->propertiesForRender[$field])) {
                    $fieldsUse[$field] = $this->propertiesForRender[$field];
                }
            }
        }

        return $fieldsUse;
    }

    /**
     * @param $result
     */
    public function jsonResponse($result)
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        if ($result && isset($result['errors']) && is_array($result['errors']) && count($result['errors']) === 0) {
            $result['errors'] = null;
        }
        ob_end_clean();
        header("Content-Type: application/json; charset=utf8");//windows-1251
        ob_clean();
        echo json_encode($result, JSON_UNESCAPED_SLASHES);
        die();
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public static function reqInput($key, $default = null)
    {
        $val = $default;
        if (isset($_GET[$key])) {
            $val = $_GET[$key];
        }
        if (isset($_POST[$key])) {
            $val = $_POST[$key];
        }

        $data = json_decode(file_get_contents('php://input'));
        if ($data !== false) {
            if (is_object($data)) {
                if (isset($data->{$key})) {
                    $val = $data->{$key};
                }
            } elseif (is_array($data)) {
                if (isset($data[$key])) {
                    $val = $data[$key];
                }
            }
        }

        return $val;
    }

}