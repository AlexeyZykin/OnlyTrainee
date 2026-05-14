<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>

<div class="news-list">

    <?php foreach ($arResult["ITEMS"] as $iblockId => $arGroup): ?>
        <div class="news-group">
            <h2>Инфоблок ID <?= $iblockId ?></h2>

            <?php foreach ($arGroup as $arItem): ?>
                <div class="news-item">
                    <?php if ($arItem["NAME"]): ?>
                    <div class="news-item__title"><?= htmlspecialchars($arItem["NAME"]) ?></div>
                    <?php endif; ?>

                    <?php if ($arItem["PREVIEW_TEXT"]): ?>
                    <div class="news-item__subtitle"><?= $arItem["PREVIEW_TEXT"] ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($arResult["ITEMS"])): ?>
        <p>Список пуст</p>
    <?php endif; ?>

</div>