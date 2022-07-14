<?php
/**
 * Created by PhpStorm.
 * User: akorolev
 * Date: 01.10.2018
 * Time: 11:58
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$arComponentDescription = array(
    "NAME" => GetMessage("COMPONENT_NAME"),
    "DESCRIPTION" => GetMessage("COMPONENT_DESCRIPTION"),
    "PATH" => array(
        "ID" => "Interlabs",
        "SORT" => 2000,
        "NAME" => "Interlabs",
    ),
);