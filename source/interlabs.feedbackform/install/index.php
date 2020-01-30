<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 01.10.2018
 * Time: 10:01
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use \Bitrix\Main\IO\File;

Loc::loadMessages(__FILE__);

/**
 * Class interlabs_feedbackform
 */
class interlabs_feedbackform extends CModule
{
    var $MODULE_ID = "interlabs.feedbackform";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    /**
     * interlabs_feedbackform constructor.
     */
    public function __construct()
    {

        if (file_exists(__DIR__ . "/version.php")) {

            $arModuleVersion = array();

            include(__DIR__ . "/version.php");

            $this->MODULE_ID = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME = Loc::getMessage("INTERLABS_FEEDBACKFORM_NAME");
            $this->MODULE_DESCRIPTION = Loc::getMessage("INTERLABS_FEEDBACKFORM_DESCRIPTION");
            $this->PARTNER_NAME = Loc::getMessage("INTERLABS_FEEDBACKFORM_PARTNER_NAME");
            $this->PARTNER_URI = Loc::getMessage("INTERLABS_FEEDBACKFORM_PARTNER_URI");
        }

        return false;
    }

    /**
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function DoInstall()
    {

        global $APPLICATION;

        if (CheckVersion(ModuleManager::getVersion("main"), "14.00.00")) {

            $this->InstallFiles();
            $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallEvents();
        } else {

            $APPLICATION->ThrowException(
                Loc::getMessage("INTERLABS_FEEDBACKFORM_INSTALL_ERROR_VERSION")
            );
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("INTERLABS_FEEDBACKFORM_INSTALL_TITLE") . " \"" . Loc::getMessage("INTERLABS_FEEDBACKFORM_NAME") . "\"",
            __DIR__ . "/step.php"
        );

        $ib = new CIBlock;
        $arFields = [
            "ACTIVE" =>'Y',
            "NAME" => 'Отзывы о товарах',
            "CODE" => 'PRODUCT_COMMENTS',
            "IBLOCK_TYPE_ID" => 'product_comments',
            "SITE_ID" => Array("en", "ru"),
            "GROUP_ID" => Array("2" => "D", "3" => "R")
        ];
        $ib->Add($arFields);

        return false;
    }

    /**
     * @return bool|void
     */
    public function InstallFiles()
    {


        CopyDirFiles(
            __DIR__ . "/components/interlabs/feedbackform",
            Application::getDocumentRoot() . "/bitrix/components/interlabs/feedbackform",
            true,
            true
        );

        return false;
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function InstallDB()
    {

        Option::set($this->MODULE_ID, 'EMAIL_FROM', Option::get('main', 'email_from', ''));
        Option::set($this->MODULE_ID, 'EMAIL_TO', Option::get('main', 'email_to', ''));


        $by = "sort";
        $order = "asc";
        $db_res = CLanguage::GetList($by, $order);
        if ($db_res && $res = $db_res->GetNext()) {
            do {
                $arParams["LANGUAGE"][$res["LID"]] = $res;

            } while ($res = $db_res->GetNext());
        }

        $CEventType_ID = null;
        $EVENT_NAME = 'INTERLABS_FEEDBACK';
        foreach ($arParams["LANGUAGE"] as $idLang => $arLang) {
            //Создание типа почтового события.
            IncludeModuleLangFile(__FILE__, $idLang);
            $arType = array(
                "LID" => $idLang,  // язык интерфейса
                "EVENT_NAME" => $EVENT_NAME, //идентификатор типа почтового события
                "NAME" => GetMessage("INTERLABS_FEEDBACKFORM_EVENT_NAME"), // заголовок типа почтового события
                "DESCRIPTION" => GetMessage("INTERLABS_FEEDBACKFORM_EVENT_DESCRIPTION"), //описание задающее поля типа почтового события
            );
            $CEventType_ID = CEventType::Add($arType);
            if ($CEventType_ID && $idLang == 'ru') {
                Option::set($this->MODULE_ID, 'EVENT_NAME', $EVENT_NAME);
            }
        }

        IncludeModuleLangFile(__FILE__, LANGUAGE_ID);

        if ($CEventType_ID) {
            //Создание почтового шаблона
            $rsSites = CSite::GetList($by = "sort", $order = "desc");
            while ($arSite = $rsSites->Fetch()) {
                $em = new CEventMEssage;
                $arFields = array(
                    "LID" => $arSite['LID'], //идентификатор сайта 's1';
                    'ACTIVE' => 'Y', //флаг активности почтового шаблона: "Y" - активен; "N" - не активен;
                    'EVENT_NAME' => $EVENT_NAME,  //идентификатор типа почтового события;
                    'EMAIL_FROM' => '#EMAIL_FROM#', //поле "From" ("Откуда");
                    'EMAIL_TO' => '#EMAIL_TO#', //поле "To" ("Куда");
                    //'BCC', //поле "BCC" ("Скрытая копия");
                    'SUBJECT' => '#SUBJECT#', //заголовок сообщения;
                    //'BODY_TYPE' => 'HTML',// - тип тела почтового сообщения: "text" - текст; "html" - HTML;
                    'MESSAGE' => '#BODY#', // тело почтового сообщения.
                    'BODY_TYPE' => 'html'
                );
                $CEventMEssage_ID = $em->Add($arFields);
                if ($CEventMEssage_ID) {
                    Option::set($this->MODULE_ID, 'MESSAGE_ID', $CEventMEssage_ID);
                }
            }
        }


        return false;
    }

    /**
     * @return bool|void
     */
    public function InstallEvents()
    {
        return false;
    }

    /**
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function DoUninstall()
    {

        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("INTERLABS_FEEDBACKFORM_UNINSTALL_TITLE") . " \"" . Loc::getMessage("INTERLABS_FEEDBACKFORM_NAME") . "\"",
            __DIR__ . "/unstep.php"
        );

        return false;
    }

    /**
     * @return bool|void
     */
    public function UnInstallFiles()
    {
        Directory::deleteDirectory(
            Application::getDocumentRoot() . "/bitrix/components/interlabs/feedbackform"
        );

        return false;
    }

    /**
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function UnInstallDB()
    {
        $et = new CEventType();
        $et->Delete("INTERLABS_FEEDBACK");


        $arIblockFilter = array(
            '=EVENT_NAME' => "INTERLABS_FEEDBACK",
        );
        $by = null;
        $order = null;
        $arElement = CEventMEssage::GetList(
            $by,
            $order,
            $arIblockFilter,
            false,
            array('ID')
        )->Fetch();
        if ($arElement ['ID']) {
            CEventMEssage::Delete($arElement ['ID']);
        }

        $arFilter = ['=EVENT_NAME' => "INTERLABS_FEEDBACK",];
        $arOrder = [];
        $res = CEventType::GetList($arFilter, $arOrder);
        while ($t = $res->fetch()) {
            CEventType::Delete($t['ID']);
        }


        Option::delete($this->MODULE_ID);

        return false;
    }

    /**
     * @return bool|void
     */
    public function UnInstallEvents()
    {
        return false;
    }

}