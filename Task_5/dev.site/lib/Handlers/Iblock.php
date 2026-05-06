<?php

namespace Dev\Site\Handlers;


use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CIBlock;
use CIBlockElement;
use CIBlockType;
use Dev\Site\config\LogConfig;
use Exception;

class Iblock
{

    public static function OnAfterIBlockElementAddHandler(&$arFields): void
    {
        self::addLog($arFields);
    }


    public static function OnAfterIBlockElementUpdateHandler(&$arFields): void
    {
        self::addLog($arFields);
    }


    private static function addLog(&$arFields)
    {
        try {
            $iBlockId = $arFields["IBLOCK_ID"];
            $elementId = $arFields["ID"];

            $iBlock = IblockTable::getById($iBlockId)
                ->fetchObject();

            if ($iBlock->getCode() === LogConfig::LOG_IBLOCK_CODE) {
                return;
            }

            self::createLogIBlockTypeIfNotExists();

            $logIBlockId = self::createLogIBlockIfNotExists();

            $logSectionId = self::createLogIBlockSectionIfNotExists(
                $logIBlockId,
                $iBlock->getName(),
                $iBlock->getCode()
            );

            $previewText = self::getPreviewText($iBlock->getName(), $iBlockId, $elementId);
            self::saveLogElement(
                iBlockId: $logIBlockId,
                sectionId: $logSectionId,
                iBlockElemId: $elementId,
                previewText: $previewText
            );
        } catch (Exception $e) {
            // Как-то логировать
            return;
        }
    }


    /**
     * Создание типа инфоблока для логов, если такого не существует
     *
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     * @throws Exception
     */
    private static function createLogIBlockTypeIfNotExists()
    {
        $existingLogTypeIBlock = TypeTable::getList([
            'filter' => ["=ID" => LogConfig::LOG_IBLOCK_TYPE_ID],
        ])->fetch();
        if ($existingLogTypeIBlock) {
            return;
        }

        $iblockType = new CIBlockType();
        $result = $iblockType->Add([
            'ID' => LogConfig::LOG_IBLOCK_TYPE_ID,
            'SECTIONS' => 'Y',
            'LANG' => [
                'ru' => [
                    'NAME' => 'Логи',
                ],
                'en' => [
                    'NAME' => 'Logs',
                ],
            ],
        ]);

        if (!$result) {
            throw new Exception($iblockType->getLastError());
        }
    }


    /**
     * Созданине инфоблока для логов, если не существует
     *
     * @return logIBlockId - ID инфоблока LOG
     * @throws SystemException
     * @throws ArgumentException
     * @throws Exception
     *
     * @throws ObjectPropertyException
     */
    private static function createLogIBlockIfNotExists()
    {
        $existingLogIBlock = IblockTable::getList([
            "filter" => ["=CODE" => LogConfig::LOG_IBLOCK_CODE,],
            "select" => ['ID']
        ])->fetch();

        if ($existingLogIBlock) {
            return $existingLogIBlock["ID"];
        }

        $iblock = new CIBlock();
        $result = $iblock->Add([
            'IBLOCK_TYPE_ID' => LogConfig::LOG_IBLOCK_TYPE_ID,
            'NAME' => 'Логи',
            'CODE' => LogConfig::LOG_IBLOCK_CODE,
            'API_CODE' => LogConfig::LOG_IBLOCK_API_CODE,
            'LID' => ['s1'],
        ]);

        if (!$result) {
            throw new Exception($iblock->getLastError());
        }

        return $result;
    }


    /**
     * Создание раздела для логов, если не существует
     *
     * @param $iBlockId - id инфоблока LOG
     * @param $sectionName -  Название раздела для лога. Идентичен имени инфоблока логируемого элемента.
     * @param $sectionCode - Символьный код раздела для лога. Идентичен символьному коду логируемого элемента
     * @throws Exception
     */
    private static function createLogIBlockSectionIfNotExists($iBlockId, $sectionName, $sectionCode)
    {
        $existingIBlockSection = self::findIBlockSection($iBlockId, $sectionCode);
        if ($existingIBlockSection) {
            return $existingIBlockSection->getId();
        }

        $result = SectionTable::add([
            'fields' => [
                'IBLOCK_ID' => $iBlockId,
                'NAME' => "$sectionName",
                'CODE' => $sectionCode,
                'ACTIVE' => 'Y'
            ]
        ]);

        if (!$result->isSuccess()) {
            throw new Exception($result->getErrorMessages()[0]);
        }

        return $result->getId();
    }


    /**
     * Поиск секции
     *
     * @param $iBlockId - Id инфоблока
     * @param $sectionCode - символьный код секции
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function findIBlockSection($iBlockId, $sectionCode)
    {
        return SectionTable::getList([
            'select' => ['ID', 'NAME', 'CODE', 'IBLOCK_ID'],
            'filter' => [
                '=IBLOCK_ID' => $iBlockId,
                '=CODE' => $sectionCode,
            ]
        ])->fetchObject();
    }

    /**
     * Получить текст анонса для элемента
     */
    private static function getPreviewText(
        $iBlockName,
        $iBlockId,
        $elementId,
    ): string {
        $element = ElementTable::getList([
            'select' => ['ID', 'IBLOCK_SECTION_ID', 'NAME'],
            'filter' => [
                '=IBLOCK_ID' => $iBlockId,
                '=ID' => $elementId
            ],
        ])->fetchObject();

        if (!$element) {
            return "";
        }

        $arrPath[] = $iBlockName;
        $arrPath = array_merge($arrPath, self::getSectionPath($iBlockId, $element->getIblockSectionId()));
        $arrPath[] = $element->getName();

        return implode(' -> ', $arrPath);
    }

    /**
     * Рекурсивный поиск имени раздела
     *
     * @param $iBlockId - родительский инфоблок для элемента
     * @param $sectionId - id раздела элемента
     */
    private static function getSectionPath($iBlockId, $sectionId): array
    {
        if (!$iBlockId || !$sectionId) {
            return [];
        }

        $section = SectionTable::getList([
            'select' => ['ID', 'NAME', 'IBLOCK_SECTION_ID'],
            'filter' => [
                '=IBLOCK_ID' => $iBlockId,
                '=ID' => $sectionId
            ]
        ])->fetchObject();

        if (!$section) {
            return [];
        }

        $path = self::getSectionPath($iBlockId, $section->getIblockSectionId());
        $path[] = $section->getName();

        return $path;
    }


    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    private static function saveLogElement($iBlockId, $sectionId, $iBlockElemId, $previewText)
    {
        $entity = ElementTable::getList([
            'select' => ['*'],
            'filter' => [
                '=IBLOCK_ID' => $iBlockId,
                '=IBLOCK_SECTION_ID' => $sectionId,
                '=NAME' => $iBlockElemId
            ],
        ])->fetchObject();

        $el = new CIBlockElement();

        if ($entity) {
            $result = $el->Update(
                $entity->getId(),
                [
                    'NAME' => $iBlockElemId,
                    'PREVIEW_TEXT' => $previewText,
                    'PREVIEW_TEXT_TYPE' => 'text',
                    'ACTIVE_FROM' => new DateTime(),
                    'ACTIVE' => 'Y',
                ]
            );

            if (!$result) {
                throw new Exception($el->getLastError());
            }
            return;
        }

        $result = $el->Add([
            'IBLOCK_ID' => $iBlockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME' => $iBlockElemId,
            'PREVIEW_TEXT' => $previewText,
            'PREVIEW_TEXT_TYPE' => 'text',
            'ACTIVE_FROM' => new DateTime(),
            'ACTIVE' => 'Y',
        ]);

        if (!$result) {
            throw new Exception($el->getLastError());
        }
    }

}
