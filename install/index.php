<?php
IncludeModuleLangFile(__FILE__);

class custom_curator extends CModule
{
    var $MODULE_ID = "custom.curator";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME = "Куратор в задачах";
    var $MODULE_DESCRIPTION = "Добавляет роль Куратор с правами наблюдателя в модуль задач";
    var $MODULE_SORT = 5000;
    var $PARTNER_NAME = "Custom";
    var $PARTNER_URI = "";

    public function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__ . "/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = "Куратор в задачах";
        $this->MODULE_DESCRIPTION = "Добавляет новую роль Куратор в модуль задач Bitrix24";
    }

    public function DoInstall()
    {
        global $APPLICATION, $step;

        if (!CModule::IncludeModule("tasks")) {
            $APPLICATION->ThrowException("Требуется модуль Задачи (tasks)");
            return false;
        }

        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();

        RegisterModule("custom.curator");

        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION, $step;

        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();

        UnRegisterModule("custom.curator");

        return true;
    }

    public function InstallDB()
    {
        global $DB, $APPLICATION;

        $errors = false;

        // Создаём таблицу для кураторов
        if (!$DB->Query("SELECT 'x' FROM b_task_curator LIMIT 1", true)) {
            $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/custom.curator/install/db/install.sql");
        }

        if ($errors !== false) {
            $APPLICATION->ThrowException(implode("", $errors));
            return false;
        }

        return true;
    }

    public function UnInstallDB()
    {
        global $DB, $APPLICATION;

        $errors = false;

        $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/custom.curator/install/db/uninstall.sql");

        if ($errors !== false) {
            $APPLICATION->ThrowException(implode("", $errors));
            return false;
        }

        return true;
    }

    public function InstallEvents()
    {
        RegisterModuleDependences("tasks", "OnTaskAdd", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnTaskAdd");
        RegisterModuleDependences("tasks", "OnTaskUpdate", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnTaskUpdate");
        RegisterModuleDependences("tasks", "OnBeforeTaskAdd", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnBeforeTaskAdd");
        RegisterModuleDependences("rest", "OnRestServiceBuildDescription", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnRestRegister");

        return true;
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences("tasks", "OnTaskAdd", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnTaskAdd");
        UnRegisterModuleDependences("tasks", "OnTaskUpdate", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnTaskUpdate");
        UnRegisterModuleDependences("tasks", "OnBeforeTaskAdd", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnBeforeTaskAdd");
        UnRegisterModuleDependences("rest", "OnRestServiceBuildDescription", "custom.curator", "\\Custom\\Curator\\EventHandlers", "OnRestRegister");

        return true;
    }

    public function InstallFiles()
    {
        // Копируем JS и CSS файлы
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/custom.curator/install/js/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/custom.curator/",
            true,
            true
        );

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/custom.curator/install/css/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/custom.curator/",
            true,
            true
        );

        return true;
    }

    public function UnInstallFiles()
    {
        // Удаляем скопированные файлы
        DeleteDirFilesEx("/bitrix/js/custom.curator/");
        DeleteDirFilesEx("/bitrix/css/custom.curator/");

        return true;
    }
}
?>