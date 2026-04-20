<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="article-list">
    <? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
        <?= $arResult["NAV_STRING"] ?><br/>
    <? endif; ?>
    <? foreach ($arResult["ITEMS"] as $arItem): ?>

        <div class="article-card">
            <? if ($arParams["DISPLAY_NAME"] != "N" && $arItem["NAME"]): ?>
                <? if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
                    <a class="article-card__title" href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                        <? echo $arItem["NAME"] ?>
                    </a>
                <? else: ?>
                    <div class="article-card__title"><?= $arItem["NAME"] ?></div>
                <? endif; ?>
            <? endif; ?>

            <? if ($arItem["DISPLAY_ACTIVE_FROM"]): ?>
                <div class="article-card__date"><?= $arItem["DISPLAY_ACTIVE_FROM"] ?></div>
            <? endif; ?>

            <div class="article-card__content">
                <? if ($arParams["DISPLAY_PICTURE"] != "N" && is_array($arItem["PREVIEW_PICTURE"])): ?>
                    <div class="article-card__image sticky">
                        <img src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                             alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"/>
                    </div>
                <? endif; ?>

                <? if ($arParams["DISPLAY_PREVIEW_TEXT"] != "N" && $arItem["PREVIEW_TEXT"]): ?>
                    <div class="article-card__text">
                        <div class="block-content"><?= $arItem["PREVIEW_TEXT"] ?></div>
                    </div>
                <? endif; ?>
            </div>
        </div>

    <? endforeach; ?>
    <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
        <br/><?= $arResult["NAV_STRING"] ?>
    <? endif; ?>
</div>
