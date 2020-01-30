<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 01.10.2018
 * Time: 16:29
 */

$module_id = 'interlabs.feedbackform';

$arJsConfig = array(
    'interlabs_feedbackform' => array(
        'js' => [
            "https://code.jquery.com/jquery-3.3.1.min.js",
            "https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js",
            //"/bitrix/js/" . $module_id . "/build/popup.js",

        ],
        'css' => array(),
        'rel' => array(),
    )
);

foreach ($arJsConfig as $ext => $arExt) {
    \CJSCore::RegisterExt($ext, $arExt);
}