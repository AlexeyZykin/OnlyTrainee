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
    <?php foreach ($arResult["ITEMS"] as $arItem): ?>

        <a class="article-item article-list__item" href="<?=$arItem["DETAIL_PAGE_URL"] ?>">
            <div class="article-item__background">
                <? if (!empty($arItem["PREVIEW_PICTURE"]["SRC"])): ?>
                    <img src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>" alt="">
                <? else: ?>
                    <img src="<?= $templateFolder ?>/images/article-bg.png" alt="">
                <? endif; ?>
            </div>

            <div class="article-item__wrapper">
                <div class="article-item__title">
                    <?= $arItem["NAME"] ?>
                </div>

                <div class="article-item__content">
                    <?= $arItem["PREVIEW_TEXT"] ?>
                </div>
            </div>
        </a>

    <? endforeach; ?>
</div>