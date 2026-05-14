<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class NewsList extends CBitrixComponent
{

    private array $arrFilter = [];

    private bool $bUSER_HAVE_ACCESS = false;


    public function onPrepareComponentParams($arParams)
    {
        global $DB, $USER;

        if (!isset($arParams["CACHE_TIME"])) {
            $arParams["CACHE_TIME"] = 36000000;
        }

        $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"] ?? '');
        $arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"] ?? '');
        $arParams["PARENT_SECTION"] = (int)($arParams["PARENT_SECTION"] ?? 0);
        $arParams["PARENT_SECTION_CODE"] ??= '';
        $arParams["INCLUDE_SUBSECTIONS"] = ($arParams["INCLUDE_SUBSECTIONS"] ?? '') !== "N";
        $arParams["SET_LAST_MODIFIED"] = ($arParams["SET_LAST_MODIFIED"] ?? '') === "Y";

        $orderExpression = '/^(asc|desc|nulls)(,asc|,desc|,nulls)?$/i';
        $arParams["SORT_BY1"] = trim($arParams["SORT_BY1"] ?? '');
        if (empty($arParams["SORT_BY1"])) {
            $arParams["SORT_BY1"] = "ACTIVE_FROM";
        }
        if (
            !isset($arParams["SORT_ORDER1"])
            || !preg_match($orderExpression, $arParams["SORT_ORDER1"])
        ) {
            $arParams["SORT_ORDER1"] = "DESC";
        }

        $arParams["SORT_BY2"] = trim($arParams["SORT_BY2"] ?? '');
        if (empty($arParams["SORT_BY2"])) {
            if (mb_strtoupper($arParams["SORT_BY1"]) === 'SORT') {
                $arParams["SORT_BY2"] = "ID";
                $arParams["SORT_ORDER2"] = "DESC";
            } else {
                $arParams["SORT_BY2"] = "SORT";
            }
        }
        if (
            !isset($arParams["SORT_ORDER2"])
            || !preg_match($orderExpression, $arParams["SORT_ORDER2"])
        ) {
            $arParams["SORT_ORDER2"] = "ASC";
        }
        $arParams['CUSTOM_ELEMENT_SORT'] ??= [];

        if (!empty($arParams["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])) {
            $this->arrFilter = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
            if (!is_array($this->arrFilter)) {
                $this->arrFilter = [];
            }
        }

        if (!empty($arParams["FILTER"]) && is_array($arParams["FILTER"])) {
            $this->arrFilter = array_merge($this->arrFilter, $arParams["FILTER"]);
        }

        $arParams["CHECK_DATES"] = ($arParams["CHECK_DATES"] ?? '') !== "N";

        if (empty($arParams["FIELD_CODE"]) || !is_array($arParams["FIELD_CODE"])) {
            $arParams["FIELD_CODE"] = [];
        }

        foreach ($arParams["FIELD_CODE"] as $key => $val) {
            if (!$val) {
                unset($arParams["FIELD_CODE"][$key]);
            }
        }

        if (empty($arParams["PROPERTY_CODE"]) || !is_array($arParams["PROPERTY_CODE"])) {
            $arParams["PROPERTY_CODE"] = array();
        }
        foreach ($arParams["PROPERTY_CODE"] as $key => $val) {
            if ($val === "") {
                unset($arParams["PROPERTY_CODE"][$key]);
            }
        }

        $arParams["DETAIL_URL"] = trim($arParams["DETAIL_URL"] ?? '');
        $arParams["SECTION_URL"] = trim($arParams["SECTION_URL"] ?? '');
        $arParams["IBLOCK_URL"] = trim($arParams["IBLOCK_URL"] ?? '');

        $arParams["NEWS_COUNT"] = (int)($arParams["NEWS_COUNT"] ?? 0);
        if ($arParams["NEWS_COUNT"] <= 0) {
            $arParams["NEWS_COUNT"] = 30;
        }

        $arParams["CACHE_FILTER"] = ($arParams["CACHE_FILTER"] ?? '') === "Y";
        if (!$arParams["CACHE_FILTER"] && !empty($this->arrFilter)) {
            $arParams["CACHE_TIME"] = 0;
        }

        $arParams["SET_TITLE"] = ($arParams["SET_TITLE"] ?? '') !== "N";
        $arParams["SET_BROWSER_TITLE"] = ($arParams["SET_BROWSER_TITLE"] ?? '') === 'N' ? 'N' : 'Y';
        $arParams["SET_META_KEYWORDS"] = ($arParams["SET_META_KEYWORDS"] ?? '') === 'N' ? 'N' : 'Y';
        $arParams["SET_META_DESCRIPTION"] = ($arParams["SET_META_DESCRIPTION"] ?? '') === 'N' ? 'N' : 'Y';
        $arParams["ADD_SECTIONS_CHAIN"] = ($arParams["ADD_SECTIONS_CHAIN"] ?? '') !== "N";
        $arParams["INCLUDE_IBLOCK_INTO_CHAIN"] = ($arParams["INCLUDE_IBLOCK_INTO_CHAIN"] ?? '') !== "N";
        $arParams["STRICT_SECTION_CHECK"] = ($arParams["STRICT_SECTION_CHECK"] ?? '') === "Y";
        $arParams["ACTIVE_DATE_FORMAT"] = trim($arParams["ACTIVE_DATE_FORMAT"] ?? '');
        if (empty($arParams["ACTIVE_DATE_FORMAT"])) {
            $arParams["ACTIVE_DATE_FORMAT"] = $DB->DateFormatToPHP(\CSite::GetDateFormat("SHORT"));
        }
        $arParams["PREVIEW_TRUNCATE_LEN"] = (int)($arParams["PREVIEW_TRUNCATE_LEN"] ?? 0);
        $arParams["HIDE_LINK_WHEN_NO_DETAIL"] = ($arParams["HIDE_LINK_WHEN_NO_DETAIL"] ?? '') === "Y";

        $arParams["INTRANET_TOOLBAR"] ??= '';
        $arParams["CHECK_PERMISSIONS"] = ($arParams["CHECK_PERMISSIONS"] ?? '') !== "N";
        $arParams["MESSAGE_404"] ??= '';
        $arParams["SET_STATUS_404"] ??= 'N';
        $arParams["SHOW_404"] ??= 'N';
        $arParams["FILE_404"] ??= '';

        $arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] ?? '') === "Y";
        if (!is_array($arParams["GROUP_PERMISSIONS"] ?? null)) {
            $adminGroupCode = 1;
            $arParams["GROUP_PERMISSIONS"] = [$adminGroupCode];
        }

        $this->bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
        if ($arParams["USE_PERMISSIONS"] && isset($USER) && is_object($USER)) {
            $arUserGroupArray = $USER->GetUserGroupArray();
            foreach ($arParams["GROUP_PERMISSIONS"] as $PERM) {
                if (in_array($PERM, $arUserGroupArray)) {
                    $this->bUSER_HAVE_ACCESS = true;
                    break;
                }
            }
        }

        $arParams["CACHE_GROUPS"] ??= '';

        return parent::onPrepareComponentParams($arParams);
    }


    public function executeComponent()
    {
        if (!$this->validateParams()) {
            return false;
        }

        if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
            if (!Loader::includeModule("iblock")) {
                $this->abortResultCache();
                ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
                return false;
            }

            $this->initResult();

            $this->includeComponentTemplate();
        }

        return $this->arResult;
    }

    private function validateParams(): bool
    {
        $errors = [];

        if (empty($this->arParams["IBLOCK_TYPE"]) && empty($this->arParams["IBLOCK_ID"])) {
            $errors[] = "IBLOCK_TYPE or IBLOCK_ID is required";
        }

        if (!empty($this->arParams["IBLOCK_ID"]) && !is_numeric($this->arParams["IBLOCK_ID"])) {
            $errors[] = "INVALID IBLOCK_ID format: {$this->arParams["IBLOCK_ID"]}";
        }

        if ($this->arParams["USE_PERMISSIONS"] && !$this->bUSER_HAVE_ACCESS) {
            $errors[] = "Access denied";
        }

        if (count($errors) !== 0) {
            ShowError($errors[0]);
            return false;
        }

        return true;
    }

    private function initResult(): void
    {
        $elements = empty($this->arParams["IBLOCK_ID"])
            ? $this->getElementsByIblockType()
            : $this->getElementsByIblock();

        $this->arResult["ITEMS"] = $this->groupElementsByIblock($elements);
    }

    private function getElementsByIblockType(): array
    {
        $rsIblocks = IblockTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=IBLOCK_TYPE_ID' => $this->arParams['IBLOCK_TYPE'],
                '=ACTIVE' => 'Y',
            ]
        ]);

        $iblockIds = [];
        while ($arrIblock = $rsIblocks->fetch()) {
            $iblockIds[] = $arrIblock['ID'];
        }

        if (empty($iblockIds)) {
            return [];
        }

        $filter = [
            '=IBLOCK_ID' => $iblockIds,
            '=ACTIVE' => 'Y',
        ];
        $filter = $this->mergeFilters($filter);

        $elements = [];
        $rsElements = ElementTable::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => [
                $this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1'],
                $this->arParams['SORT_BY2'] => $this->arParams['SORT_ORDER2'],
            ],
            'limit' => $this->arParams['NEWS_COUNT']
        ]);

        while ($elem = $rsElements->fetch()) {
            $elements[] = $elem;
        }

        return $elements;
    }

    private function getElementsByIblock(): array
    {
        $filter = [
            '=IBLOCK_ID' => $this->arParams["IBLOCK_ID"],
            '=ACTIVE' => 'Y',
        ];
        $filter = $this->mergeFilters($filter);

        $arr = [];
        $rs = ElementTable::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => [
                $this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1'],
                $this->arParams['SORT_BY2'] => $this->arParams['SORT_ORDER2'],
            ],
            'limit' => $this->arParams['NEWS_COUNT']
        ]);

        while ($elem = $rs->fetch()) {
            $arr[] = $elem;
        }

        return $arr;
    }

    private function groupElementsByIblock($elements): array
    {
        $groupedArr = [];
        foreach ($elements as $element) {
            $iblockId = $element["IBLOCK_ID"];
            $groupedArr[$iblockId][] = $element;
        }

        return $groupedArr;
    }

    private function mergeFilters(array $filterArr): array
    {
        return !empty($this->arrFilter)
            ? array_merge($filterArr, $this->arrFilter)
            : $filterArr;
    }

}
