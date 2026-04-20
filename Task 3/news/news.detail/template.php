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

<div class="article-card-details">
    <? if ($arResult["NAME"]): ?>
        <div class="article-card__title"><?= $arResult["NAME"] ?></div>
    <? endif; ?>

    <? if ($arResult["DISPLAY_ACTIVE_FROM"]): ?>
        <div class="article-card__date"><?= $arResult["DISPLAY_ACTIVE_FROM"] ?></div>
    <? endif; ?>

    <div class="article-card__content">
        <? if ($arParams["DISPLAY_PICTURE"] != "N" && is_array($arResult["DETAIL_PICTURE"])): ?>
            <div class="article-card__image sticky">
                <img src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>" alt="<?= $arResult["DETAIL_PICTURE"]["ALT"] ?>"/>
            </div>
        <? endif; ?>

        <div class="article-card__text">
            <div class="block-content"><?= $arResult["DETAIL_TEXT"] ?></div>
            <a class="article-card__button" href="<?=$arParams["IBLOCK_URL"]?>"><?=GetMessage("T_NEWS_DETAIL_BACK")?></a>
        </div>
    </div>
</div>