<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 01.10.2018
 * Time: 10:21
 */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if(!check_bitrix_sessid()){

    return;
}

echo(CAdminMessage::ShowNote(Loc::getMessage("KIT_FEEDBACKFORM_UNSTEP_BEFORE")." ".Loc::getMessage("KIT_FEEDBACKFORM_UNSTEP_AFTER")));
?>

<form action="<? echo($APPLICATION->GetCurPage()); ?>">
    <input type="hidden" name="lang" value="<? echo(LANG); ?>" />
    <input type="submit" value="<? echo(Loc::getMessage("KIT_FEEDBACKFORM_UNSTEP_SUBMIT_BACK")); ?>">
</form>