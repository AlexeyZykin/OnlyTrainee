<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Bitrix\Main\Localization\Loc;

class UFProperty
{
    private static bool $showedCss = false;
    private static bool $showedJs = false;
    private static bool $htmlEditorEventInitialized = false;


    public static function GetUserTypeDescription(): array
    {
        return [
            'USER_TYPE_ID' => 'uf_complex_property',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Комплексное поле',
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
            'GetEditFormHTML' => [__CLASS__, 'GetEditFormHTML'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'GetLength' => [__CLASS__, 'GetLength'],
            'GetDBColumnType' => [__CLASS__, 'GetDBColumnType'],
            'OnBeforeSave' => [__CLASS__, 'OnBeforeSave'],
        ];
    }


    public static function OnBeforeSave($arUserField, $value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $settings = $arUserField['SETTINGS'] ?? [];
        $arFields = self::prepareSetting($settings);

        foreach ($value as $code => $val) {
            if (($arFields[$code]['TYPE'] ?? '') === 'file' && is_array($val)) {
                $value[$code] = self::prepareFileToDB($val);
            }
        }

        $isEmpty = true;
        foreach ($value as $v) {
            if (!empty($v)) {
                $isEmpty = false;
                break;
            }
        }

        return $isEmpty ? null : json_encode($value, JSON_UNESCAPED_UNICODE);
    }


    public static function GetEditFormHTML($arUserField, $arHtmlControl): string
    {
        $value = $arHtmlControl['VALUE'];
        $cleanValue = htmlspecialchars_decode($value);
        $arrValue = !empty($cleanValue)
            ? json_decode($cleanValue, true)
            : [];

        $hideText = Loc::getMessage('IEX_CPROP_HIDE_TEXT');
        $clearText = Loc::getMessage('IEX_CPROP_CLEAR_TEXT');

        self::showCss();
        self::showJs();

        $settings = $arUserField['SETTINGS'] ?? [];
        if (!empty($settings)) {
            $arFields = self::prepareSetting($settings);
        } else {
            return '<span>' . Loc::getMessage('IEX_CPROP_ERROR_INCORRECT_SETTINGS') . '</span>';
        }

        $result = '';
        $result .= '<div class="mf-gray"><a class="cl mf-toggle">' . $hideText . '</a>';
        if ($arUserField['MULTIPLE'] === 'Y') {
            $result .= ' | <a class="cl mf-delete">' . $clearText . '</a></div>';
        }
        $result .= '<table class="mf-fields-list active">';

        foreach ($arFields as $code => $arItem) {
            $result .= match ($arItem['TYPE']) {
                'string' => self::showString($code, $arItem['TITLE'], $arrValue, $arHtmlControl),
                'file' => self::showFile($code, $arItem['TITLE'], $arrValue, $arHtmlControl),
                'text' => self::showTextarea($code, $arItem['TITLE'], $arrValue, $arHtmlControl),
                'date' => self::showDate($code, $arItem['TITLE'], $arrValue, $arHtmlControl),
                'element' => self::showBindElement($code, $arItem['TITLE'], $arrValue, $arHtmlControl),
                'html' => self::showHTMLEditor($code, $arItem['TITLE'], $arrValue, $arHtmlControl),
                default => ''
            };
        }

        $result .= '</table>';

        if ($arUserField['MULTIPLE'] === 'Y') {
            $result .= '<input type="hidden" name="' . $arHtmlControl['NAME'] . '" value="">';
        }

        return $result;
    }


    public static function GetAdminListViewHTML($arUserField, $arHtmlControl): string
    {
        $value = $arHtmlControl['VALUE'] ?? '';

        if (empty($value)) {
            return '';
        }

        $settings = $arUserField['SETTINGS'] ?? [];
        $fields = self::prepareSetting($settings);

        $cleanJsonStr = htmlspecialchars_decode($value);
        $data = json_decode($cleanJsonStr, true);
        $filteredData = array_filter($data, fn($v) => !empty($v));

        $arrRes = [];
        foreach ($filteredData as $key => $val) {
            $title = $fields[$key]['TITLE'] ?? $key;
            $arrRes[] = $title . ': ' . $val;
        }

        return implode("; ", $arrRes);
    }

    public static function GetPublicViewHTML($arUserField, $arHtmlControl): string
    {
        return self::GetAdminListViewHTML($arUserField, $arHtmlControl);
    }


    public static function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm): string
    {
        if (!is_array($arUserField)) {
            $arUserField = [];
        }

        $btnAdd = Loc::getMessage('IEX_CPROP_SETTING_BTN_ADD');

        self::showJsForSetting($arHtmlControl["NAME"]);
        self::showCssForSetting();

        $result = '<tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">        
                <tr valign="top" class="heading mf-setting-title">
                   <td>XML_ID</td>
                   <td>' . Loc::getMessage('IEX_CPROP_SETTING_FIELD_TITLE') . '</td>
                   <td>' . Loc::getMessage('IEX_CPROP_SETTING_FIELD_SORT') . '</td>
                   <td>' . Loc::getMessage('IEX_CPROP_SETTING_FIELD_TYPE') . '</td>
                </tr>';

        $settings = $arUserField['SETTINGS'] ?? [];
        $arSetting = self::prepareSetting($settings);

        if (!empty($arSetting)) {
            foreach ($arSetting as $code => $arItem) {
                $result .= '
                       <tr valign="top">
                           <td><input type="text" class="inp-code" size="20" value="' . $code . '"></td>
                           <td><input type="text" class="inp-title" size="35" name="' . $arHtmlControl["NAME"] . '[' . $code . '_TITLE]" value="' . $arItem['TITLE'] . '"></td>
                           <td><input type="text" class="inp-sort" size="5" name="' . $arHtmlControl["NAME"] . '[' . $code . '_SORT]" value="' . $arItem['SORT'] . '"></td>
                           <td>
                                <select class="inp-type" name="' . $arHtmlControl["NAME"] . '[' . $code . '_TYPE]">
                                    ' . self::getOptionList($arItem['TYPE']) . '
                                </select>                        
                           </td>
                       </tr>';
            }
        }

        $result .= '
               <tr valign="top">
                    <td><input type="text" class="inp-code" size="20"></td>
                    <td><input type="text" class="inp-title" size="35"></td>
                    <td><input type="text" class="inp-sort" size="5" value="500"></td>
                    <td>
                        <select class="inp-type"> ' . self::getOptionList() . '</select>                        
                    </td>
               </tr>
             </table>   
                
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <input type="button" value="' . $btnAdd . '" onclick="addNewRows()">
                    </td>
                </tr>
                </td></tr>';

        return $result;
    }


    public static function PrepareSettings($arUserField): array
    {
        $result = [];
        if (!empty($arUserField['SETTINGS'])) {
            foreach ($arUserField['SETTINGS'] as $code => $value) {
                $result[$code] = $value;
            }
        }
        return $result;
    }


    public static function GetLength($arUserField, $value): int
    {
        $settings = $arUserField['SETTINGS'] ?? [];
        $arFields = self::prepareSetting($settings);

        $values = is_array($value) ? $value : json_decode($value, true);
        if (empty($values)) {
            return false;
        }

        $result = false;
        foreach ($values as $code => $val) {
            if ($arFields[$code]['TYPE'] === 'file') {
                if (!empty($val['name']) || (!empty($val['OLD']) && empty($val['DEL']))) {
                    $result = true;
                    break;
                }
            } else {
                if (!empty($val)) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }


    public static function GetDBColumnType($arUserField): string
    {
        return 'text';
    }


    private static function getOptionList($selected = 'string'): string
    {
        $result = '';
        $arOption = [
            'string' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_STRING'),
            'file' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_FILE'),
            'text' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_TEXT'),
            'date' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_DATE'),
            'element' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_ELEMENT'),
            'html' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_HTML'),
        ];

        foreach ($arOption as $code => $name) {
            $s = '';
            if ($code === $selected) {
                $s = 'selected';
            }

            $result .= '<option value="' . $code . '" ' . $s . '>' . $name . '</option>';
        }

        return $result;
    }


    private static function prepareSetting($arSetting): array
    {
        $arResult = [];

        foreach ($arSetting as $key => $value) {
            if (strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            } else {
                if (strstr($key, '_SORT') !== false) {
                    $code = str_replace('_SORT', '', $key);
                    $arResult[$code]['SORT'] = $value;
                } else {
                    if (strstr($key, '_TYPE') !== false) {
                        $code = str_replace('_TYPE', '', $key);
                        $arResult[$code]['TYPE'] = $value;
                    }
                }
            }
        }

        if (!function_exists('cmp')) {
            function cmp($a, $b): int
            {
                if ($a['SORT'] == $b['SORT']) {
                    return 0;
                }
                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            }
        }

        uasort($arResult, 'cmp');

        return $arResult;
    }


    private static function showCss(): void
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .cl {
                    cursor: pointer;
                }

                .mf-gray {
                    color: #797777;
                }

                .mf-fields-list {
                    display: none;
                    padding-top: 10px;
                    margin-bottom: 10px !important;
                    margin-left: -300px !important;
                    border-bottom: 1px #e0e8ea solid !important;
                }

                .mf-fields-list.active {
                    display: block;
                }

                .mf-fields-list td {
                    padding-bottom: 5px;
                }

                .mf-fields-list td:first-child {
                    width: 300px;
                    color: #616060;
                }

                .mf-fields-list td:last-child {
                    padding-left: 5px;
                }

                .mf-fields-list input[type="text"] {
                    width: 350px !important;
                }

                .mf-fields-list textarea {
                    min-width: 350px;
                    max-width: 650px;
                    color: #000;
                }

                .mf-fields-list img {
                    max-height: 150px;
                    margin: 5px 0;
                }

                .mf-img-table {
                    background-color: #e0e8e9;
                    color: #616060;
                    width: 100%;
                }

                .mf-fields-list input[type="text"].adm-input-calendar {
                    width: 170px !important;
                }

                .mf-file-name {
                    word-break: break-word;
                    padding: 5px 5px 0 0;
                    color: #101010;
                }

                .mf-fields-list input[type="text"].mf-inp-bind-elem {
                    width: unset !important;
                }
            </style>
            <?
        }
    }


    private static function showJs(): void
    {
        $showText = Loc::getMessage('IEX_CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('IEX_CPROP_HIDE_TEXT');

        if (!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                BX.ready(() => {
                    document.addEventListener('click', (e) => {
                        const target = e.target;

                        if (!target.matches('a.mf-toggle')) return;

                        e.preventDefault();

                        const table = target.closest('tr')?.querySelector('table.mf-fields-list');
                        if (!table) return;

                        table.classList.toggle('active');
                        target.textContent = table.classList.contains('active')
                            ? '<?=$hideText?>'
                            : '<?=$showText?>';
                    });

                    document.addEventListener('click', (e) => {
                        const target = e.target;

                        if (!target.matches('a.mf-delete')) return;

                        e.preventDefault();

                        const row = target.closest('tr');
                        if (!row) return;

                        const textInputs = row.querySelectorAll('input[type="text"]');
                        textInputs?.forEach(it => {
                            it.value = '';
                        })

                        const textArea = row.querySelectorAll('textarea');
                        textArea?.forEach(it => {
                            it.value = '';
                        })

                        const checkBoxInputs = row.querySelectorAll('input[type="checkbox"]');
                        checkBoxInputs?.forEach(it => {
                            it.checked = true;
                        })

                        row.style.display = 'none';
                    });
                });
            </script>
            <?
        }
    }


    private static function showJsForSetting($inputName): void
    {
        ?>
        <script>
            BX.ready(() => {
                window.addNewRows = () => {
                    const fieldsTable = document.querySelector('#many-fields-table');
                    if (!fieldsTable) {
                        return;
                    }

                    const row = document.createElement('tr');
                    row.setAttribute('valign', "top");
                    row.innerHTML = `
                        <td><input type="text" class="inp-code" size="20"></td>
                        <td><input type="text" class="inp-title" size="35"></td>
                        <td><input type="text" class="inp-sort" size="5" value="500"></td>
                        <td><select class="inp-type"><?=self::getOptionList()?></select></td>
                    `;

                    fieldsTable.append(row)
                };

                document.addEventListener('change', (e) => {
                    const target = e.target;

                    if (!target.classList.contains('inp-code')) return;

                    const code = target.value;
                    const row = target.closest('tr');

                    if (!row) return;

                    const titleInput = row.querySelector('input.inp-title');
                    const sortInput = row.querySelector('input.inp-sort');
                    const typeSelect = row.querySelector('select.inp-type');

                    if (code.length <= 0) {
                        titleInput?.removeAttribute('name');
                        sortInput?.removeAttribute('name');
                        typeSelect?.removeAttribute('name');
                    } else {
                        titleInput?.setAttribute('name', '<?=$inputName?>[' + code + '_TITLE]')
                        sortInput?.setAttribute('name', '<?=$inputName?>[' + code + '_SORT]');
                        typeSelect?.setAttribute('name', '<?=$inputName?>[' + code + '_TYPE]');
                    }
                });

                document.addEventListener('input', (e) => {
                    const target = e.target;

                    if (!target.classList.contains('inp-sort')) return;

                    const num = target.value;
                    target.value = num.replace(/[^0-9]/gim, '');
                });
            });
        </script>
        <?
    }

    private static function showCssForSetting(): void
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .many-fields-table {
                    margin: 0 auto;
                }

                .mf-setting-title td {
                    text-align: center !important;
                    border-bottom: unset !important;
                }

                .many-fields-table td {
                    text-align: center;
                }

                .many-fields-table > input, .many-fields-table > select {
                    width: 90% !important;
                }

                .inp-sort {
                    text-align: center;
                }

                .inp-type {
                    min-width: 125px;
                }
            </style>
            <?
        }
    }

    private static function showString($code, $title, $values, $arHtmlControl)
    {
        $result = '';

        $v = $values[$code] ?? '';
        $name = $arHtmlControl['NAME'] . '[' . $code . ']';

        $result .= '<tr>
                    <td align="right">' . $title . ': </td>
                    <td><input type="text" value="' . $v . '" name="' . $name . '"/></td>
                </tr>';

        return $result;
    }


    private static function showFile($code, $title, $values, $arHtmlControl): string
    {
        $result = '';

        $rawValue = $values[$code] ?? '';

        $fieldName = $arHtmlControl['NAME'] . '[' . $code . ']';

        if (!empty($rawValue) && !is_array($rawValue)) {
            $fileId = $rawValue;
        } else {
            if (!empty($rawValue['OLD'])) {
                $fileId = $rawValue['OLD'];
            } else {
                $fileId = '';
            }
        }

        if (!empty($fileId)) {
            $arPicture = CFile::GetByID($fileId)->Fetch();
            if ($arPicture) {
                $strImageStorePath = COption::GetOptionString('main', 'upload_dir', 'upload');
                $sImagePath = '/' . $strImageStorePath . '/' . $arPicture['SUBDIR'] . '/' . $arPicture['FILE_NAME'];
                $fileType = self::getExtension($sImagePath);

                if (in_array($fileType, ['png', 'jpg', 'jpeg', 'gif'])) {
                    $content = '<img src="' . $sImagePath . '">';
                } else {
                    $content = '<div class="mf-file-name">' . $arPicture['FILE_NAME'] . '</div>';
                }

                $result = '<tr>
                        <td align="right" valign="top">' . $title . ': </td>
                        <td>
                            <table class="mf-img-table">
                                <tr>
                                    <td>' . $content . '<br>
                                        <div>
                                            <label><input name="' . $fieldName . '[DEL]" value="Y" type="checkbox"> ' . Loc::getMessage(
                        "IEX_CPROP_FILE_DELETE"
                    ) . '</label>
                                            <input name="' . $fieldName . '[OLD]" value="' . $fileId . '" type="hidden">
                                        </div>
                                    </td>
                                </tr>
                            </table>                      
                        </td>
                    </tr>';
            }
        } else {
            $result .= '<tr>
                    <td align="right">' . $title . ': </td>
                    <td><input type="file" value="" name="' . $fieldName . '"/></td>
                </tr>';
        }

        return $result;
    }


    public static function showTextarea($code, $title, $values, $arHtmlControl): string
    {
        $result = '';

        $v = $values[$code] ?? '';
        $name = $arHtmlControl['NAME'] . '[' . $code . ']';

        $result .= '<tr>
                    <td align="right" valign="top">' . $title . ': </td>
                    <td><textarea rows="8" name="' . $name . '">' . $v . '</textarea></td>
                </tr>';

        return $result;
    }


    public static function showDate($code, $title, $values, $arHtmlControl): string
    {
        $result = '';

        $v = $values[$code] ?? '';
        $name = $arHtmlControl['NAME'] . '[' . $code . ']';

        $result .= '<tr>
                        <td align="right" valign="top">' . $title . ': </td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding: 0;">
                                        <div class="adm-input-wrap adm-input-wrap-calendar">
                                            <input class="adm-input adm-input-calendar" type="text" name="' . $name . '" size="23" value="' . $v . '">
                                            <span class="adm-calendar-icon"
                                                  onclick="BX.calendar({node: this, field:\'' . $name . '\', form: \'\', bTime: true, bHideTime: false});"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

        return $result;
    }


    private static function showHTMLEditor($code, $title, $values, $arHtmlControl)
    {
        $v = $values[$code] ?? '';
        $fieldName = $arHtmlControl['NAME'] . '[' . $code . ']';

        $htmlEditorFieldName = str_replace(['[', ']'], '_', $fieldName);

        ob_start();

        ?>
        <tr>
            <td align="right" valign="top"><?= $title ?></td>
            <td>
                <input type="hidden"
                       name="<?= $fieldName ?>"
                       value="<?= $v ?>"
                       data-editor-name="<?= $htmlEditorFieldName ?>">
                <?php
                CFileMan::AddHTMLEditorFrame(
                    $htmlEditorFieldName,
                    $v,
                    $htmlEditorFieldName . "_TYPE",
                    strlen($v) ? 'html' : 'text',
                    [
                        'height' => 250,
                        'width' => '100%'
                    ]
                );
                ?>
            </td>
        </tr>
        <?php
        self::showHtmlEditorJs() ?>
        <?php
        return ob_get_clean();
    }


    // Скрипт для записи value из textaree html редактора
    private static function showHtmlEditorJs()
    {
        if (self::$htmlEditorEventInitialized) {
            return;
        }
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('form').forEach(form => {
                    if (!form.querySelector('input[data-editor-name]')) return;

                    form.addEventListener("submit", () => {
                        form.querySelectorAll("input[data-editor-name]").forEach(hidden => {
                            const editorName = hidden.getAttribute("data-editor-name");
                            const htmlEditorTextarea = document.getElementById("bxed_" + editorName);
                            hidden.value = htmlEditorTextarea.value;
                        });
                    })
                });
            })
        </script>
        <?php
        self::$htmlEditorEventInitialized = true;
    }


    public static function showBindElement($code, $title, $values, $arHtmlControl): string
    {
        $result = '';

        $v = $values[$code] ?? '';
        $fieldName = $arHtmlControl['NAME'] . '[' . $code . ']';

        $elUrl = '';
        if (!empty($v)) {
            $arElem = \CIBlockElement::GetList([],
                ['ID' => $v],
                false,
                ['nPageSize' => 1],
                ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if (!empty($arElem)) {
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . $arElem['IBLOCK_ID'] . '&ID=' . $arElem['ID'] . '&type=' . $arElem['IBLOCK_TYPE_ID'] . '">' . $arElem['NAME'] . '</a>';
            }
        }

        $result .= '<tr>
                    <td align="right">' . $title . ': </td>
                    <td>
                        <input name="' . $fieldName . '" id="' . $fieldName . '" value="' . $v . '" size="8" type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n=' . $arHtmlControl['NAME'] . '&k=' . $code . '\', 900, 700);">&nbsp;
                        <span>' . $elUrl . '</span>
                    </td>
                </tr>';

        return $result;
    }


    private static function prepareFileToDB($arValue)
    {
        $result = false;

        if (!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])) {
            CFile::Delete($arValue['OLD']);
        } else {
            if (!empty($arValue['OLD'])) {
                $result = $arValue['OLD'];
            } else {
                if (!empty($arValue['name'])) {
                    $result = CFile::SaveFile($arValue, 'vote');
                }
            }
        }

        return $result;
    }


    private static function getExtension($filePath)
    {
        $array = explode('.', $filePath);
        return array_pop($array);
    }

}