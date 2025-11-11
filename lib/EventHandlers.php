<?php
namespace Custom\Curator;

use Bitrix\Main\Loader;

Loader::includeModule('tasks');

class EventHandlers
{
    public static function OnBeforeTaskAdd(&$arFields)
    {
        if (isset($arFields['CURATOR_ID']) && $arFields['CURATOR_ID']) {
            $arFields['_TEMP_CURATOR_ID'] = $arFields['CURATOR_ID'];
            unset($arFields['CURATOR_ID']);
        }
    }

    public static function OnTaskAdd($idTask, $arTask)
    {
        global $USER;

        if (isset($arTask['_TEMP_CURATOR_ID']) && $arTask['_TEMP_CURATOR_ID']) {
            $curatorId = $arTask['_TEMP_CURATOR_ID'];
            Curator::addCurator($idTask, $curatorId, $USER->GetID());
        }

        if (isset($GLOBALS['_TEMP_CURATOR_ID'][$idTask]) && $GLOBALS['_TEMP_CURATOR_ID'][$idTask]) {
            Curator::addCurator($idTask, $GLOBALS['_TEMP_CURATOR_ID'][$idTask], $USER->GetID());
            unset($GLOBALS['_TEMP_CURATOR_ID'][$idTask]);
        }
    }

    public static function OnTaskUpdate($idTask, &$arFields, &$arTaskCopy)
    {
        global $USER;

        if (isset($arFields['CURATOR_ID'])) {
            $newCuratorId = $arFields['CURATOR_ID'];
            $oldCurators = Curator::getCuratorIds($idTask);

            foreach ($oldCurators as $oldId) {
                Curator::removeCurator($idTask, $oldId);
            }

            if ($newCuratorId) {
                if (is_array($newCuratorId)) {
                    foreach ($newCuratorId as $id) {
                        Curator::addCurator($idTask, $id, $USER->GetID());
                    }
                } else {
                    Curator::addCurator($idTask, $newCuratorId, $USER->GetID());
                }
            }

            unset($arFields['CURATOR_ID']);
        }
    }

    public static function OnRestRegister()
    {
        return array(
            'curator.add' => array(
                '\\Custom\\Curator\\Rest\\CuratorRest',
                'add'
            ),
            'curator.remove' => array(
                '\\Custom\\Curator\\Rest\\CuratorRest',
                'remove'
            ),
            'curator.list' => array(
                '\\Custom\\Curator\\Rest\\CuratorRest',
                'list'
            ),
            'curator.gettasks' => array(
                '\\Custom\\Curator\\Rest\\CuratorRest',
                'gettasks'
            ),
            'curator.check' => array(
                '\\Custom\\Curator\\Rest\\CuratorRest',
                'check'
            ),
        );
    }
}
?>