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
        global $APPLICATION;

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
        global $APPLICATION;

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

        // Проверяем, существует ли таблица
        if (!$DB->Query("SELECT 'x' FROM b_task_curator LIMIT 1", true)) {
            $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/local/modules/custom.curator/install/db/install.sql");
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

        $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/local/modules/custom.curator/install/db/uninstall.sql");

        if ($errors !== false) {
            $APPLICATION->ThrowException(implode("", $errors));
            return false;
        }

        return true;
    }

    public function InstallEvents()
    {
        RegisterModuleDependences("tasks", "OnTaskAdd", "custom.curator", "\Custom\Curator\EventHandlers", "OnTaskAdd");
        RegisterModuleDependences("tasks", "OnTaskUpdate", "custom.curator", "\Custom\Curator\EventHandlers", "OnTaskUpdate");
        RegisterModuleDependences("tasks", "OnBeforeTaskAdd", "custom.curator", "\Custom\Curator\EventHandlers", "OnBeforeTaskAdd");

        return true;
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences("tasks", "OnTaskAdd", "custom.curator", "\Custom\Curator\EventHandlers", "OnTaskAdd");
        UnRegisterModuleDependences("tasks", "OnTaskUpdate", "custom.curator", "\Custom\Curator\EventHandlers", "OnTaskUpdate");
        UnRegisterModuleDependences("tasks", "OnBeforeTaskAdd", "custom.curator", "\Custom\Curator\EventHandlers", "OnBeforeTaskAdd");

        return true;
    }

    public function InstallFiles()
    {
        // Копируем JS и CSS файлы в публичную папку
        $jsSource = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/custom.curator/install/js/";
        $jsDest = $_SERVER["DOCUMENT_ROOT"] . "/local/js/custom.curator/";

        $cssSource = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/custom.curator/install/css/";
        $cssDest = $_SERVER["DOCUMENT_ROOT"] . "/local/css/custom.curator/";

        if (is_dir($jsSource)) {
            CopyDirFiles($jsSource, $jsDest, true, true);
        }

        if (is_dir($cssSource)) {
            CopyDirFiles($cssSource, $cssDest, true, true);
        }

        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFilesEx("/local/js/custom.curator/");
        DeleteDirFilesEx("/local/css/custom.curator/");

        return true;
    }
}
?>