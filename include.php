<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CModule::AddModuleInstallFile(__FILE__, array(
    "CLASS_NAME" => "custom_curator",
    "DESCRIPTION" => "Модуль для добавления роли Куратор в задачи"
));
?>