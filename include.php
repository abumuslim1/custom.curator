<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

// Регистрируем автозагрузку классов модуля
Loader::registerAutoLoadClasses(
    'custom.curator',
    array(
        '\Custom\Curator\Curator' => 'lib/Curator.php',
        '\Custom\Curator\Observer' => 'lib/Observer.php',
        '\Custom\Curator\EventHandlers' => 'lib/EventHandlers.php',
        '\Custom\Curator\Rest\CuratorRest' => 'lib/Rest/CuratorRest.php',
    )
);
?>