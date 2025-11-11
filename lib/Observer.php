<?php
namespace Custom\Curator;

class Observer
{
    public static function canView($taskId, $userId)
    {
        return Curator::isCurator($taskId, $userId);
    }

    public static function canComment($taskId, $userId)
    {
        if (Curator::isCurator($taskId, $userId)) {
            return true;
        }
        return false;
    }

    public static function canEdit($taskId, $userId)
    {
        return false;
    }

    public static function canComplete($taskId, $userId)
    {
        return false;
    }
}
?>