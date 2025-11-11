<?php
namespace Custom\Curator\Rest;

use Custom\Curator\Curator;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;

class CuratorRest extends Controller
{
    protected function getDefaultPreFilters()
    {
        return array(
            new ActionFilter\Authentication(),
            new ActionFilter\HttpMethod(array(ActionFilter\HttpMethod::METHOD_POST)),
            new ActionFilter\Cors(array('*')),
        );
    }

    public function addAction($taskId, $userId)
    {
        try {
            if (!$taskId || !$userId) {
                throw new \Exception("Не указаны обязательные параметры");
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

    public function removeAction($taskId, $userId)
    {
        try {
            if (!$taskId || !$userId) {
                throw new \Exception("Не указаны обязательные параметры");
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

    public function listAction($taskId)
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

    public function gettasksAction($userId)
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

    public function checkAction($taskId, $userId)
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