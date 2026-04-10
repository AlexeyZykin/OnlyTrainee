<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 */

if ($arResult["isFormErrors"] == "Y"):?>
    <?= $arResult["FORM_ERRORS_TEXT"]; ?>
<? endif; ?>

<?= $arResult["FORM_NOTE"] ?? '' ?>

<? if ($arResult["isFormNote"] != "Y"): ?>

    <div class="contact-form">

        <? if ($arResult["isFormDescription"] == "Y" || $arResult["isFormTitle"] == "Y"): ?>
            <div class="contact-form__head">
                <? if ($arResult["isFormTitle"] == "Y"): ?>
                    <div class="contact-form__head-title"><?= $arResult["FORM_TITLE"] ?></div>
                <? endif; ?>

                <? if ($arResult["isFormDescription"] == "Y"): ?>
                    <div class="contact-form__head-text">
                        <?= $arResult["FORM_DESCRIPTION"] ?>
                    </div>
                <? endif; ?>
            </div>
        <? endif; ?>


        <?= $arResult["FORM_HEADER"] ?>

        <div class="contact-form__form-inputs">
            <? foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
                <? if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden'): ?>
                    <?= $arQuestion["HTML_CODE"] ?>
                    <? continue; ?>
                <?endif;?>

                <? if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'textarea') continue; ?>

                <div class="input contact-form__input">
                    <label class="input__label">
                        <div class="input__label-text">
                            <?=htmlspecialcharsbx($arQuestion["CAPTION"])?>
                            <?=($arQuestion["REQUIRED"] == "Y") ? "*" : ""?>
                        </div>

                        <?=$arQuestion["HTML_CODE"]?>

                        <? if (isset($arResult["FORM_ERRORS"][$FIELD_SID])): ?>
                            <div class="input__notification">
                                <?= htmlspecialcharsbx($arResult["FORM_ERRORS"][$FIELD_SID]) ?>
                            </div>
                        <? endif; ?>
                    </label>
                </div>
            <? endforeach; ?>
        </div>

        <?
        // Поиск первого textarea (шаблон разрешает только один)
        $textareaField = null;
        $textareaSid = null;
        foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion) {
            if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'textarea') {
                $textareaField = $arQuestion;
                $textareaSid = $FIELD_SID;
                break;
            }
        }
        ?>

        <? if ($textareaField): ?>
            <div class="contact-form__form-message">
                <div class="input">
                    <label class="input__label">
                        <div class="input__label-text">
                            <?= htmlspecialcharsbx($textareaField["CAPTION"]) ?>
                            <?= ($textareaField["REQUIRED"] == "Y") ? "*" : "" ?>
                        </div>
                        <?= $textareaField["HTML_CODE"] ?>
                        <?php if (isset($arResult["FORM_ERRORS"][$textareaSid])): ?>
                            <div class="input__notification">
                                <?= htmlspecialcharsbx($arResult["FORM_ERRORS"][$textareaSid]) ?>
                            </div>
                        <?php endif; ?>
                    </label>
                </div>
            </div>
        <?endif;?>

        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
                ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных
                данных&raquo;.
            </div>

            <input
                class="form-button contact-form__bottom-button"
                <?=(intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "");?>
                type="submit"
                name="web_form_submit"
                value="<?=htmlspecialcharsbx(trim($arResult["arForm"]["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]);?>"
            />
        </div>

        <p>
            <?= $arResult["REQUIRED_SIGN"]; ?> - <?= GetMessage("FORM_REQUIRED_FIELDS") ?>
        </p>

        <?= $arResult["FORM_FOOTER"] ?>

    </div>
<? endif; ?>