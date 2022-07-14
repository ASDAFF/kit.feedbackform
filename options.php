<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 01.10.2018
 * Time: 10:36
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);

if (Option::get($module_id, 'EMAIL_TO', '') === '') {
    Option::set(
        $module_id,
        'EMAIL_TO',
        Option::get(
            'main',
            'email_to',
            Option::get(
                'main',
                'email_from',
                ''
            )
        )
    );
}

if (Option::get($module_id, 'EMAIL_FROM', '') === '') {
    Option::set(
        $module_id,
        'EMAIL_FROM',
        Option::get('main', 'email_from', '')
    );
}


$aTabs = array(
    array(
        "DIV" => "edit",
        "TAB" => Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_NAME"),
        "TITLE" => Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_NAME"),
        "OPTIONS" => array(
            Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_COMMON"),


            array(
                "subject",
                Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_SUBJECT"),
                "Kit - forms",
                array("text", 64)
            ),


            array(
                "EMAIL_FROM",
                Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_EMAIL_FROM"),
                "",
                array("text", 64)
            ),
            array(
                "EMAIL_TO",
                Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_EMAIL_TO"),
                "",
                array("text", 64)
            ),
            array(
                "EVENT_NAME",
                Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_EVENT_NAME"),
                "",
                array("text", 64)
            ),
            array(
                "MESSAGE_ID",
                Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_MESSAGE_ID"),
                "",
                array("text", 64)
            ),

            /*array(
                "AGREE_PROCESSING_FILE_ID",
                'Согласие: Файл',
                "",
                array("file")
            ),*/
            array(
                "AGREE_PROCESSING_TEXT",
                Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_AGREE_PROCESSING_TEXT"),
                "",
                ['textarea', 5]
            ),

        )
    )
);


$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);

$tabControl->Begin();

?>

    <form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>"
          method="post" enctype="multipart/form-data">

        <?
        foreach ($aTabs as $aTab) {

            if ($aTab["OPTIONS"]) {

                $tabControl->BeginNextTab();
                __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
            }
        }
        ?>
        <tr>
            <td width="40%" nowrap>
                <?php echo Loc::getMessage("KIT_FEEDBACKFORM_OPTIONS_TAB_AGREE_PROCESSING_FILE"); ?>
            <td width="60%">
                <input name="AGREE_PROCESSING_FILE_ID" type="text" placeholder="id"
                       value="<?php echo Option::get($module_id, 'AGREE_PROCESSING_FILE_ID', ''); ?>">
                <?php
                $AGREE_PROCESSING_FILE_ID = Option::get($module_id, 'AGREE_PROCESSING_FILE_ID', '');
                if ($AGREE_PROCESSING_FILE_ID) {
                    $arFile = CFile::GetFileArray($AGREE_PROCESSING_FILE_ID);
                    if ($arFile) {
                        echo '<a href="' . $arFile["SRC"] . '" target="_blank">' . $arFile["FILE_NAME"] . '</a>';
                    }
                } ?>
                <input name="AGREE_PROCESSING_FILE" type="file">
            </td>
        </tr>
        <?php
        $tabControl->BeginNextTab();

        $tabControl->Buttons();
        ?>

        <input type="submit" name="apply"
               value="<? echo(Loc::GetMessage("KIT_FEEDBACKFORM_OPTIONS_INPUT_APPLY")); ?>" class="adm-btn-save"/>
        <input type="submit" name="default"
               value="<? echo(Loc::GetMessage("KIT_FEEDBACKFORM_OPTIONS_INPUT_DEFAULT")); ?>"/>

        <?
        echo(bitrix_sessid_post());
        ?>

    </form>

<?php
$tabControl->End();


// save options
if ($request->isPost() && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) {

        foreach ($aTab["OPTIONS"] as $arOption) {

            if (!is_array($arOption)) {

                continue;
            }

            if ($arOption["note"]) {

                continue;
            }

            if ($request["apply"]) {

                $optionValue = $request->getPost($arOption[0]);


                if (in_array($arOption[0], ['ajax', "switch_on"])) {
                    if ($optionValue == "") {

                        $optionValue = "N";
                    }
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);

            } elseif ($request["default"]) {

                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }

    }

    if (isset($_FILES['AGREE_PROCESSING_FILE'])) {
        $arFile = $_FILES['AGREE_PROCESSING_FILE'];
        $arFile["del"] = ${$fieldName . "_del"};
        $arFile["MODULE_ID"] = $module_id;
        $fid = CFile::SaveFile($arFile, $module_id);
        if (intval($fid) > 0) {
            Option::set($module_id, 'AGREE_PROCESSING_FILE_ID', intval($fid));
        };
    }


    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . $module_id . "&lang=" . LANG);
}


