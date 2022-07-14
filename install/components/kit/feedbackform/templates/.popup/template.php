<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 01.10.2018
 * Time: 11:59
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

CUtil::InitJSCore(array('interlabs_feedbackform'));

/**
 * $arResult=[
 *    'isSaveFeedback' => boolean  //save in iblock or generate event
 *    'use_ajax' => boolean  // true - use ajax request to send data; false - use post request to page where components is placement
 *    'validateErrors'=> array<[message=>string]>
 *    'AJAX_MODE' => string Y|N
 * ];
 */
?>


<div class="interlabs-feedbackform__container">
    <button class="js-interlabs-feedbackform__container-show-button">
        <?php echo Loc::getMessage("FORM_POPUP_SHOW_BUTTON"); ?>
    </button>


    <div class="interlabs-feedbackform__container__dialog modal-mask<?php echo (isset($arResult['validateErrors']) && count($arResult['validateErrors']) > 0) ? '' : ' hidden'; ?>">
        <div class="modal-wrapper">
            <div class="modal-container">

                <div class="header">
                    <label><?php echo $arResult['SUBJECT'] ? $arResult['SUBJECT'] : Loc::getMessage("FORM_TITLE"); ?></label>
                    <span class="js-interlabs-feedbackform__dialog__close">
                             <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                  xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L17 17" stroke="#8B8989" stroke-width="2" stroke-linecap="round"/>
                    <path d="M1 17L17 1" stroke="#8B8989" stroke-width="2" stroke-linecap="round"/>
                </svg>
                        </span>
                </div>


                <div class="body">
                    <div class="interlabs-feedbackform__container__errors">
                        <?php if (isset($arResult['validateErrors']) && count($arResult['validateErrors']) > 0) { ?>
                            <?php foreach ($arResult['validateErrors'] as $error) { ?>
                                <label class="interlabs-feedbackform__container__errors__item"
                                       data-field="<?php echo isset($error['field']) ? $error['field'] : ''; ?>">
                                    <?php echo $error['message']; ?>
                                </label>
                            <?php } ?>

                        <?php } ?>
                    </div>


                    <?php if ($arResult['AJAX_MODE'] === 'Y'){ ?>
                    <div method="post" enctype="multipart/form-data"
                         data-validatefields='<?php echo json_encode($arResult['template']['validate']); ?>'
                         class="js-div-to-form-convert ajax">
                        <input name="AJAX_CALL" value="Y" type="hidden">
                        <?php }else{ ?>
                        <form method="post" enctype="multipart/form-data" action=""
                              data-validatefields='<?php echo json_encode($arResult['template']['validate']); ?>'
                              class="">
                            <?php } ?>
                            <input type="hidden" name="interlabs__feedbackform" value="Y">
                            <input type="hidden" name="interlabs__feedbackform_FORM_ID" value="<?php echo $arParams['FORM_ID'] ?>">
                            <?=bitrix_sessid_post()?>
                            <div class="scroll-area">
                                <?php
                                foreach ($arResult['FIELDS'] as $code => $field) { ?>
                                    <div class="form-group">
                                        <?php
                                        switch ($field['TYPE']) {
                                            case 'file[]':
                                                ?>
                                                <label for="<?php echo $code; ?>"><?php echo $field['NAME']; ?><?php echo $field['REQUIRED'] ? '<span class="field-required">*</span>' : ''; ?></label>
                                                <label class="file">
                                                    <?php $idFileField = 'file-' . uniqid($code); ?>
                                                    <input id="<?php echo $idFileField; ?>" type="file" value=""
                                                           name="<?php echo $code; ?>[]" multiple
                                                           onchange="var inp =document.getElementById('<?php echo $idFileField; ?>');var l=[];for (var i = 0; i < inp.files.length; ++i) {l.push(inp.files.item(i).name.match(/[^\/\\]+$/));}var label=document.getElementById('<?php echo $idFileField; ?>-label');label.innerHTML=l.join(', ');if(l.length>0){var arr=label.className.split(' '); if (arr.indexOf('selected') == -1) {label.className += ' ' + 'selected';}}else{label.className += arr.join(' ').replace('selected',''); }">
                                                    <a onclick="document.getElementById('<?php echo $idFileField; ?>').click();return false;">Îבחמנ</a>
                                                    <label id="<?php echo $idFileField; ?>-label"><?php echo Loc::getMessage("INPUT_FILE_DEFAULT"); ?></label>
                                                </label>

                                                <?php
                                                break;

                                            case 'file':
                                                ?>
                                                <label for="<?php echo $code; ?>"><?php echo $field['NAME']; ?><?php echo $field['REQUIRED'] ? ' <span class="field-required">*</span>' : ''; ?></label>
                                                <label class="file">
                                                    <?php $idFileField = 'file-' . uniqid($code); ?>
                                                    <input id="<?php echo $idFileField; ?>"
                                                           type="file"
                                                           name="<?php echo $code; ?>"
                                                           onchange="var inp =document.getElementById('<?php echo $idFileField; ?>');var l=[];for (var i = 0; i < inp.files.length; ++i) {l.push(inp.files.item(i).name.match(/[^\/\\]+$/));}var label=document.getElementById('<?php echo $idFileField; ?>-label');label.innerHTML=l.join(', ');if(l.length>0){var arr=label.className.split(' '); if (arr.indexOf('selected') == -1) {label.className += ' ' + 'selected';}}else{label.className += arr.join(' ').replace('selected',''); }">
                                                    <a onclick="document.getElementById('<?php echo $idFileField; ?>').click();return false;">Îבחמנ</a>
                                                    <label id="<?php echo $idFileField; ?>-label"><?php echo Loc::getMessage("INPUT_FILE_DEFAULT"); ?></label>
                                                </label>
                                                <?php
                                                break;

                                            case 'datepicker':
                                                ?>
                                                <label for="<?php echo $code; ?>"><?php echo $field['NAME']; ?><?php echo $field['REQUIRED'] ? '<span class="field-required">*</span>' : ''; ?></label>
                                                <input id="<?php echo $code; ?>" type="text" value="" class="date"
                                                       name="<?php echo $code; ?>"
                                                       value="<?php echo Feedbackform::reqInput($code, ''); ?>"
                                                       onclick="BX.calendar({node: this, field: this, bTime: false});">
                                                <?php
                                                break;
                                            case 'select':
                                                ?>
                                                <label for="<?php echo $code; ?>"><?php echo $field['NAME']; ?><?php echo $field['REQUIRED'] ? '<span class="field-required">*</span>' : ''; ?></label>
                                                <select id="<?php echo $code; ?>" name="<?php echo $code; ?>"
                                                    <?php echo $field['REQUIRED'] ? ' validate="validate" required ' : ''; ?>
                                                >
                                                    <?php foreach ($field['VALUES'] as $id => $text) { ?>
                                                        <option value="<?php echo $id; ?>"
                                                            <?php echo Feedbackform::reqInput($code) == $id ? ' selected ' : ''; ?>
                                                        ><?php echo $text; ?>
                                                        </option>
                                                        <?php
                                                    } ?>
                                                </select>
                                                <?php
                                                break;
                                            case 'select[]':
                                                ?>
                                                <label for="<?php echo $code; ?>"><?php echo $field['NAME']; ?><?php echo $field['REQUIRED'] ? '<span class="field-required">*</span>' : ''; ?></label>
                                                <select multiple id="<?php echo $code; ?>" name="<?php echo $code; ?>[]"
                                                    <?php echo $field['REQUIRED'] ? ' validate="validate" required ' : ''; ?>
                                                >
                                                    <?php foreach ($field['VALUES'] as $id => $text) { ?>
                                                        <option value="<?php echo $id; ?>"
                                                            <?php echo in_array($id, Feedbackform::reqInput($code, [])) ? ' selected ' : ''; ?>
                                                        >
                                                            <?php echo $text; ?></option>
                                                        <?php
                                                    } ?>
                                                </select>
                                                <?php
                                                break;
                                            case 'radio':
                                                ?>
                                                <?php foreach ($field['VALUES'] as $id => $text) { ?>
                                                <div class="c-radio">
                                                    <input id="<?php echo $code . '-' . $id; ?>" name="<?php echo $code; ?>"
                                                           type="radio" value="<?php echo $id; ?>"
                                                        <?php echo Feedbackform::reqInput($code) == $id ? ' checked ' : ''; ?>
                                                    >
                                                    <label for="<?php echo $code . '-' . $id; ?>"><?php echo $text; ?></label>
                                                </div>
                                                <?php
                                            }
                                                break;
                                            case 'checkbox[]':
                                                ?>
                                                <?php foreach ($field['VALUES'] as $id => $text) { ?>
                                                <div class="c-checkbox">
                                                    <input id="<?php echo $code . '-' . $id; ?>" name="<?php echo $code; ?>[]"
                                                           type="checkbox" value="<?php echo $id; ?>"
                                                        <?php echo in_array($id, Feedbackform::reqInput($code, [])) ? ' checked ' : ''; ?>
                                                    >
                                                    <label for="<?php echo $code . '-' . $id; ?>"><?php echo $text; ?></label>
                                                </div>
                                                <?php
                                            }
                                                break;
                                            case 'textarea':
                                                ?>
                                                <label for="<?php echo $code; ?>"><?php echo $field['NAME']; ?><?php echo $field['REQUIRED'] ? '<span class="field-required">*</span>' : ''; ?></label>
                                                <textarea id="<?php echo $code; ?>" name="<?php echo $code; ?>"
                                                    <?php echo $field['REQUIRED'] ? ' validate="validate" required ' : ''; ?>
                                                ><?php echo Feedbackform::reqInput($code, ''); ?></textarea>
                                                <?php
                                                break;
                                            case 'text':
                                            default:
                                                ?>
                                                <label for="<?php echo $code; ?>"><?php echo $field['NAME']; ?><?php echo $field['REQUIRED'] ? '<span class="field-required">*</span>' : ''; ?></label>
                                                <input id="<?php echo $code; ?>" name="<?php echo $code; ?>"
                                                       placeholder="<?php echo $field['NAME']; ?>"
                                                       type="text"
                                                       value="<?php echo Feedbackform::reqInput($code, $arResult['form'][$code]); ?>"
                                                    <?php echo $field['REQUIRED'] ? ' validate="validate" required ' : ''; ?>
                                                >
                                            <?php } ?>
                                    </div>
                                <? } ?>


                                <?php if ($arParams['USE_CAPTCHA'] === 'Y') { ?>
                                    <div class="form-group">
                                        <label for="captcha"><?php echo Loc::getMessage("CAPTCHA_ENTER_CODE"); ?></label>
                                        <div class="captcha">
                                            <input type="hidden" name="captcha_sid"
                                                   value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                                            <input id="captcha" type="text" name="captcha_word" maxlength="50" value=""
                                                   required/>
                                            <img src="/bitrix/tools/captcha.php?captcha_code=<?= $arResult["CAPTCHA_CODE"] ?>"
                                                 alt="CAPTCHA"/>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if ($arResult['AGREE_PROCESSING'] === 'Y') {
                                    $AGREE_PROCESSING_TEXT_dialog_CSS_ID = 'AGREE_PROCESSING_TEXT_dialog' . uniqid('AGREE_PROCESSING_TEXT_dialog');
                                    ?>
                                    <div class="form-group agree">
                                        <div class="c-checkbox">
                                            <input id="AGREE_PROCESSING" name="AGREE_PROCESSING" value="Y"
                                                   type="checkbox" required>
                                            <label for="AGREE_PROCESSING"><?php echo Loc::getMessage("AGREE_PROCESSING"); ?>
                                                <span
                                                        class="field-required">*</span></label>
                                        </div>

                                        <?php if ($arResult['AGREE_PROCESSING_TEXT']) { ?>
                                            <div id="<?php echo $AGREE_PROCESSING_TEXT_dialog_CSS_ID; ?>"
                                                 class="interlabs__info-dialog hidden">
                                                <div class="header">
                                                    <label><?php echo Loc::getMessage("AGREE_PROCESSING_DIALOG_TITLE"); ?></label>
                                                    <span class="close-dialog"
                                                          onclick="document.getElementById('<?php echo $AGREE_PROCESSING_TEXT_dialog_CSS_ID; ?>').className+=' hidden '">
                                         <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                              xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L17 17" stroke="#8B8989" stroke-width="2" stroke-linecap="round"/>
                    <path d="M1 17L17 1" stroke="#8B8989" stroke-width="2" stroke-linecap="round"/>
                </svg>
                                    </span>
                                                </div>
                                                <div class="body">
                                                    <div class="form-group scroll-area">
                                                        <?php echo $arResult['AGREE_PROCESSING_TEXT']; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <a class="btn btn-close"
                                                           onclick="document.getElementById('<?php echo $AGREE_PROCESSING_TEXT_dialog_CSS_ID; ?>').className+=' hidden '"><?php echo Loc::getMessage("FORM_CLOSE"); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <a onclick="document.getElementById('<?php echo $AGREE_PROCESSING_TEXT_dialog_CSS_ID; ?>').className=document.getElementById('<?php echo $AGREE_PROCESSING_TEXT_dialog_CSS_ID; ?>').className.replace('hidden','')">
                                                <?php echo Loc::getMessage("AGREE_PROCESSING_DIALOG_TITLE"); ?>
                                            </a>
                                        <?php } else if ($arResult['AGREE_PROCESSING_FILE']) { ?>
                                            <a class="AGREE_PROCESSING_FILE__link"
                                               href=" <?php echo $arResult['AGREE_PROCESSING_FILE']["SRC"]; ?>"
                                               target="_blank">
                                                <?php echo $arResult['AGREE_PROCESSING_FILE']["FILE_NAME"]; ?>
                                            </a>
                                        <?php } ?>

                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group  control-buttons">
                                <button class="modal-default-button js-interlabs-feedbackform__dialog__send-button"
                                        type="submit">
                                    <?php echo Loc::getMessage("FORM_SEND"); ?>
                                </button>
                                <a class="modal-default-button js-interlabs-feedbackform__dialog__cancel-button">
                                    <?php echo Loc::getMessage("FORM_CLOSE"); ?>
                                </a>
                            </div>
                            <?php if ($arResult['AJAX_MODE'] === 'Y'){ ?>
                    </div>
                <?php } else { ?>
                    </form>
                <?php } ?>

                </div>

            </div>
        </div>
    </div>


    <div class="interlabs-feedbackform__container-succsess modal-mask <?php echo $arResult['isSaveFeedback'] === false ? ' hidden' : ''; ?>">
        <div class="modal-wrapper">
            <div class="modal-container">
                <div class="header">
                    <label><?php echo $arResult['SUBJECT'] ? $arResult['SUBJECT'] : Loc::getMessage("FORM_TITLE"); ?></label>
                    <span class="js-interlabs-feedbackform__dialog__close">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L17 17" stroke="#8B8989" stroke-width="2" stroke-linecap="round"/>
                    <path d="M1 17L17 1" stroke="#8B8989" stroke-width="2" stroke-linecap="round"/>
                </svg>
                    </span>

                </div>
                <div class="body">
                    <div class="scroll-area">
                        <label><?php echo Loc::getMessage("FORM_SAVED"); ?></label>
                    </div>
                    <div class="form-group control-buttons">
                        <button class="interlabs-feedbackform__container-succsess__close">
                            <?php echo Loc::getMessage("FORM_CLOSE"); ?>
                        </button>
                    </div>

                </div>


            </div>
        </div>
    </div>


</div>

<script type="text/javascript">
    <?php echo Loc::getMessage("JQUERY_VALIDATOR_MESSAGES"); ?>
</script>