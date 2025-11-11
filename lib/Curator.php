<?php
namespace Custom\Curator;

class Curator
{
    const TYPE_CURATOR = 'CURATOR';

    public static function addCurator($taskId, $userId, $createdBy = false)
    {
        global $DB;

        if (!$createdBy) {
            global $USER;
            $createdBy = $USER->GetID();
        }

        $sql = "
            INSERT INTO b_task_curator (TASK_ID, USER_ID, CREATED_BY, DATE_CREATE)
            VALUES (
                " . intval($taskId) . ",
                " . intval($userId) . ",
                " . intval($createdBy) . ",
                NOW()
            )
            ON DUPLICATE KEY UPDATE CREATED_BY = " . intval($createdBy) . ", DATE_CREATE = NOW()
        ";

        return $DB->Query($sql);
    }

    public static function removeCurator($taskId, $userId)
    {
        global $DB;
        return $DB->Query("
            DELETE FROM b_task_curator
            WHERE TASK_ID = " . intval($taskId) . "
            AND USER_ID = " . intval($userId)
        );
    }

    public static function getCurators($taskId)
    {
        global $DB;

        $result = $DB->Query("
            SELECT USER_ID, CREATED_BY, DATE_CREATE
            FROM b_task_curator
            WHERE TASK_ID = " . intval($taskId) . "
            ORDER BY DATE_CREATE ASC
        ");

        $curators = array();
        while ($row = $result->Fetch()) {
            $curators[] = $row;
        }

        return $curators;
    }

    public static function getCuratorIds($taskId)
    {
        $curators = self::getCurators($taskId);
        $ids = array();

        foreach ($curators as $curator) {
            $ids[] = $curator['USER_ID'];
        }

        return $ids;
    }

    public static function getUserTasks($userId, $type = 'curator')
    {
        global $DB;

        $sql = "
            SELECT t.ID, t.TITLE, t.RESPONSIBLE_ID, t.CREATED_BY, t.DEADLINE
            FROM b_tasks t
            INNER JOIN b_task_curator c ON t.ID = c.TASK_ID
            WHERE c.USER_ID = " . intval($userId);

        if ($type === 'curator') {
            $sql .= " AND c.USER_ID != t.RESPONSIBLE_ID
                     AND c.USER_ID != t.CREATED_BY";
        }

        $sql .= " ORDER BY t.DEADLINE ASC";

        $result = $DB->Query($sql);
        $tasks = array();

        while ($row = $result->Fetch()) {
            $tasks[] = $row;
        }

        return $tasks;
    }

    public static function isCurator($taskId, $userId)
    {
        $curators = self::getCuratorIds($taskId);
        return in_array($userId, $curators);
    }
}
?>