<?php

namespace Dev\Site\Agents;


use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use CIBlockElement;
use Dev\Site\config\AgentsConfig;
use Dev\Site\config\LogConfig;

class Iblock
{

    public static function clearOldLogs()
    {
        try {
            $iblock = IblockTable::getList([
                'select' => ['ID', 'CODE', 'NAME'],
                'filter' => [
                    '=CODE' => LogConfig::LOG_IBLOCK_CODE
                ]
            ])->fetchObject();

            if (!$iblock) {
                return AgentsConfig::CLEAR_OLD_LOGS_AGENT_NAME;
            }

            $keepIds = [];
            $keepLogsResult = ElementTable::getList([
                'select' => ['ID'],
                'filter' => [
                    '=IBLOCK_ID' => $iblock->getId(),
                ],
                'order' => ['ACTIVE_FROM' => 'DESC'],
                'limit' => LogConfig::LOG_KEEP_LIMIT,
            ]);
            while ($keepLog = $keepLogsResult->fetchObject()) {
                $keepIds[] = $keepLog->getId();
            }

            $logsToDelete = ElementTable::getList([
                'select' => ['ID'],
                'filter' => [
                    '!@ID' => $keepIds
                ]
            ]);

            while ($log = $logsToDelete->fetchObject()) {
                CIBlockElement::Delete($log->getId());
            }

            return AgentsConfig::CLEAR_OLD_LOGS_AGENT_NAME;
        } catch (Exception $e) {
            // Как-то логировать
            return AgentsConfig::CLEAR_OLD_LOGS_AGENT_NAME;
        }
    }
}
