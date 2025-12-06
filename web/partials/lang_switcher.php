<?php

$currentLang = $lang_code ?? 'en';
$params = $urlParams ?? $_GET;
$isOobSwap = $isOob ?? false;

$paramsEn = array_merge($params, ['lang' => 'en']);
$linkEn = '?' . http_build_query($paramsEn);

$paramsUa = array_merge($params, ['lang' => 'ua']);
$linkUa = '?' . http_build_query($paramsUa);
?>

<div id="lang-switcher" class="lang-switch" <?= $isOobSwap ? 'hx-swap-oob="true"' : '' ?>>
    <a href="<?= $linkEn ?>" class="<?= $currentLang === 'en' ? 'active' : '' ?>">EN</a>
    <a href="<?= $linkUa ?>" class="<?= $currentLang === 'ua' ? 'active' : '' ?>">UA</a>
</div>