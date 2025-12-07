<?php
$rootPath = dirname(__DIR__); 
$langDir = $rootPath . '/lang/';

$allowedLangs = ['en', 'ua'];

$lang_code = 'en';

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLangs)) {
    $lang_code = $_GET['lang'];
    
    setcookie('lang', $lang_code, time() + (86400 * 30), "/");
    
} elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $allowedLangs)) {
    $lang_code = $_COOKIE['lang'];
}

$defaultUi = require $langDir . 'en.php';

if ($lang_code !== 'en') {
    $transFile = $langDir . $lang_code . '.php';
    
    if (file_exists($transFile)) {
        $localUi = require $transFile;
        $ui = array_merge($defaultUi, $localUi);
    } else {
        $ui = $defaultUi;
    }
} else {
    $ui = $defaultUi;
}

?>