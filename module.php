<?php
// Основной файл модуля
IncludeModuleLangFile(__FILE__);

class custom_curator extends CModule
{
    var $MODULE_ID = "custom.curator";
    var $MODULE_NAME = "Куратор в задачах";
    var $MODULE_DESCRIPTION = "Добавляет роль Куратор с фильтрацией и отображением";
    var $MODULE_VERSION = "1.0.0";
    var $PARTNER_NAME = "Custom";
    var $PARTNER_URI = "";

    public function __construct()
    {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/module.php"));
        include($path . "/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            if (array_key_exists("VERSION_DATE", $arModuleVersion))
                $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!CModule::IncludeModule("tasks")) {
            $APPLICATION->ThrowException("Требуется модуль Задачи");
            return false;
        }

        $this->InstallDB();
        $this->InstallEvents();

        CModule::RegisterModule($this->MODULE_ID);

        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallDB();
        $this->UnInstallEvents();

        CModule::UnRegisterModule($this->MODULE_ID);

        return true;
    }

    public function InstallDB()
    {
        global $DB;

        $DB->Query("
            CREATE TABLE IF NOT EXISTS b_task_curator (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                TASK_ID INT NOT NULL,
                USER_ID INT NOT NULL,
                CREATED_BY INT,
                DATE_CREATE DATETIME,
                UNIQUE KEY unique_task_user (TASK_ID, USER_ID),
                KEY idx_user (USER_ID),
                FOREIGN KEY (TASK_ID) REFERENCES b_tasks(ID) ON DELETE CASCADE,
                FOREIGN KEY (USER_ID) REFERENCES b_user(ID) ON DELETE CASCADE
            ) ENGINE=INNODB
        ", false, "FILE: " . __FILE__);

        return true;
    }

    public function UnInstallDB()
    {
        global $DB;
        $DB->Query("DROP TABLE IF EXISTS b_task_curator");
        return true;
    }

    public function InstallEvents()
    {
        RegisterModuleEventHandler("tasks", "OnTaskAdd", "custom.curator", "EventHandlers", "OnTaskAdd");
        RegisterModuleEventHandler("tasks", "OnTaskUpdate", "custom.curator", "EventHandlers", "OnTaskUpdate");
        RegisterModuleEventHandler("tasks", "OnBeforeTaskAdd", "custom.curator", "EventHandlers", "OnBeforeTaskAdd");
        RegisterModuleEventHandler("rest", "OnRestServiceBuildDescription", "custom.curator", "EventHandlers", "OnRestRegister");

        return true;
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleEventHandler("tasks", "OnTaskAdd", "custom.curator", "EventHandlers", "OnTaskAdd");
        UnRegisterModuleEventHandler("tasks", "OnTaskUpdate", "custom.curator", "EventHandlers", "OnTaskUpdate");
        UnRegisterModuleEventHandler("tasks", "OnBeforeTaskAdd", "custom.curator", "EventHandlers", "OnBeforeTaskAdd");
        UnRegisterModuleEventHandler("rest", "OnRestServiceBuildDescription", "custom.curator", "EventHandlers", "OnRestRegister");

        return true;
    }
}
?>