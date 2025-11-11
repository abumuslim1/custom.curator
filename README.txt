МОДУЛЬ КУРАТОР ДЛЯ BITRIX24
============================

✅ СТРУКТУРА ДЛЯ /local/modules/

ИНСТРУКЦИЯ ПО УСТАНОВКЕ:

1. Распакуйте архив в /local/modules/ так, чтобы получилось:
   /local/modules/custom.curator/

2. Правильная структура:
   /local/modules/custom.curator/
   ├── install/
   │   ├── index.php         ← Главный файл установки
   │   ├── version.php
   │   ├── db/
   │   │   ├── install.sql
   │   │   └── uninstall.sql
   │   ├── js/
   │   │   └── curator-selector.js
   │   └── css/
   │       └── curator-selector.css
   ├── lib/
   │   ├── Curator.php
   │   ├── Observer.php
   │   ├── EventHandlers.php
   │   └── Rest/
   │       └── CuratorRest.php
   ├── include.php
   └── .settings.php

3. Перейдите в администрирование:
   Администрирование → Модули и компоненты → Модули

4. Модуль "Куратор в задачах" появится в списке

5. Нажмите "Установить"

ВАЖНО:
✓ Модуль размещается в /local/modules/ - безопасно для обновлений Bitrix24
✓ Требуется модуль "Задачи" (tasks)
✓ После установки создаётся таблица b_task_curator
✓ JS и CSS копируются в /local/js/ и /local/css/

ПРОВЕРКА:
✓ В разделе Задачи появится поле "Куратор"
✓ REST API методы custom.curator.* будут доступны
✓ Таблица b_task_curator создана в БД

ВЕРСИЯ: 1.0.0
ДАТА: 2025-11-11
