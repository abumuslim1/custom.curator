<?php
namespace Custom\Curator\Rest;

use Custom\Curator\Curator;

class CuratorRest
{
    public static function add($taskId, $userId)
    {
        try {
            if (!$taskId || !$userId) {
                return array(
                    'status' => 'error',
                    'message' => 'Не указаны обязательные параметры'
                );
            }

            Curator::addCurator($taskId, $userId);

            return array(
                'status' => 'success',
                'message' => 'Куратор добавлен',
                'data' => array(
                    'taskId' => $taskId,
                    'userId' => $userId
                )
            );
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }

    public static function remove($taskId, $userId)
    {
        try {
            if (!$taskId || !$userId) {
                return array(
                    'status' => 'error',
                    'message' => 'Не указаны обязательные параметры'
                );
            }

            Curator::removeCurator($taskId, $userId);

            return array(
                'status' => 'success',
                'message' => 'Куратор удалён'
            );
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }

    public static function getCuratorList($taskId)
    {
        try {
            $curators = Curator::getCurators($taskId);

            return array(
                'status' => 'success',
                'data' => $curators,
                'count' => count($curators)
            );
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }

    public static function getUserTasks($userId)
    {
        try {
            $tasks = Curator::getUserTasks($userId, 'curator');

            foreach ($tasks as &$task) {
                if ($task['RESPONSIBLE_ID']) {
                    $rsUser = \CUser::GetByID($task['RESPONSIBLE_ID']);
                    if ($arUser = $rsUser->Fetch()) {
                        $task['RESPONSIBLE_NAME'] = $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
                    }
                }
            }

            return array(
                'status' => 'success',
                'data' => $tasks,
                'count' => count($tasks)
            );
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }

    public static function checkCurator($taskId, $userId)
    {
        try {
            $isCurator = Curator::isCurator($taskId, $userId);

            return array(
                'status' => 'success',
                'data' => array(
                    'isCurator' => $isCurator
                )
            );
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }
}
?>