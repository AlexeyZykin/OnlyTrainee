<?php

use \Bitrix\Main\Loader;

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
}
if (php_sapi_name() !== 'cli') {
    die();
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
Loader::includeModule('iblock');

$VACANCY_IBLOCK_ID = 4;
$vacancyElement = new CIBlockElement;
$vacancyPropsEnum = [];
$importedCount = 0;


// Очистка элементов инфоблока
$existingElementsRes  = CIBlockElement::GetList(
    arOrder: [],
    arFilter: ['IBLOCK_ID' => $VACANCY_IBLOCK_ID],
    arSelectFields: ['ID']
);
while ($element = $existingElementsRes->GetNext()) {
    CIBlockElement::Delete($element['ID']);
}


// Получение свойств инфоблока типа "Список"
$propEnumRes = CIBlockPropertyEnum::GetList(
    arFilter: ["IBLOCK_ID" => $VACANCY_IBLOCK_ID]
);
while ($enumArr = $propEnumRes->Fetch()) {
    $key = trim(mb_strtolower($enumArr["VALUE"]));
    $val = $enumArr["ID"];
    $vacancyPropsEnum[$enumArr["PROPERTY_CODE"]][$key] = $val;
}


// Парсинг csv файла и заполнение инфоблока элементами
$rowNum = 1;
if (($handle = fopen("vacancy.csv", "r")) !== false) {

    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
        if ($rowNum === 1) {
            $rowNum++;
            continue;
        }
        $rowNum++;

        $itemProps = [
            "ACTIVITY" => $row[9],
            "FIELD" => $row[3],
            "OFFICE" => $row[1],
            "EMAIL" => $row[12],
            "LOCATION" => $row[2],
            "TYPE" => $row[8],
            "SALARY_TYPE" => '',
            "SALARY_VALUE" => $row[7],
            "REQUIRE" => $row[4],
            "DUTY" => $row[5],
            "CONDITIONS" => $row[6],
            "SCHEDULE" => $row[10],
            "DATE" => date('d.m.Y'),
        ];

        foreach ($itemProps as $key => &$val) {
            $val = trim($val);
            $val = str_replace(["\n", "\r", "\t"], '', $val);
            $hasTextWithList = str_contains($val, "•");

            if ($vacancyPropsEnum[$key]) {
                $lowerCaseVal = mb_strtolower($val);
                foreach ($vacancyPropsEnum[$key] as $iBlockPropKey => $iBlockPropVal) {
                    if (str_contains($lowerCaseVal, $iBlockPropKey)) {
                        $val = $iBlockPropVal;
                    }
                    if (similar_text($iBlockPropKey, $lowerCaseVal) > 70) {
                        $val = $iBlockPropVal;
                    }
                }
            } else if ($hasTextWithList) {
                $arr = explode("•", $val);
                $cleanArr = array_map('trim', $arr);
                $filteredArr = array_filter($cleanArr, fn($val) => $val !== "");
                $val = $filteredArr;
            }
        }

        if ($itemProps["SALARY_VALUE"]) {
            $salaryVal = $itemProps["SALARY_VALUE"];
            $type = match (true) {
                str_contains($salaryVal, "по договоренности") => "договорная",
                str_starts_with($salaryVal, "от") => "от",
                str_starts_with($salaryVal, "до") => 'до',
                default => '='
            };
            $itemProps["SALARY_TYPE"] = $vacancyPropsEnum["SALARY_TYPE"][$type] ?? '';
            $itemProps["SALARY_VALUE"] = preg_replace("/\D/", "", $salaryVal);
        }

        $vacancyFields = [
            "MODIFIED_BY" => 1,
            "IBLOCK_ID" => $VACANCY_IBLOCK_ID,
            "PROPERTY_VALUES" => $itemProps,
            "NAME" => $itemProps["FIELD"] || "Без названия",
            "ACTIVE" => 'Y',
        ];

        if ($vacancyElement->Add($vacancyFields)) {
            $importedCount++;
        } else {
            echo "Error: " . $vacancyElement->LAST_ERROR . "\n";
        }
    }

    echo "Количество импортированных элементов: " . $importedCount . "\n";

    fclose($handle);
}













 