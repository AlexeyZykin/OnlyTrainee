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
        $iblock = IblockTable::getList([
            'select' => ['ID', 'CODE', 'NAME'],
            'filter' => [
                '=CODE' => LogConfig::LOG_IBLOCK_CODE
            ]
        ])->fetchObject();

        if (!$iblock) {
            return;
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
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
