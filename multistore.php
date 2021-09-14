<?php

$files = glob('app/etc/env.php.*');
if (empty($files)) {
    return;
}

$validStores = ['default' => 'default*'];
foreach ($files as $file) {
    $code = substr(strrchr($file, '.'), 1);
    $validStores[$code] = $code;
}

if (empty($_SERVER['MAGE_RUN_CODE']) && php_sapi_name() === 'cli') {
    $input = readline('Select store (' . implode(', ', $validStores) . '): ');
    if (!empty($validStores[$input])) {
        $_SERVER['MAGE_RUN_CODE'] = $validStores[$input];
        $_SERVER['MAGE_RUN_TYPE'] = 'store';
    }
    return;
}

if (!isset($_SERVER['REQUEST_URI'])) {
    return;
}

// Determine the store view code from the URL
$requestUri = $_SERVER['REQUEST_URI'];
$paths = explode('/', ltrim($requestUri, '/'));
$pathsFiltered = array_filter($paths);
$storeCode = reset($pathsFiltered);
$storeCode = str_replace('_admin', '', $storeCode);

if (!array_key_exists($storeCode, $validStores)) {
    return;
}

// Set run code and run type
$_SERVER['MAGE_RUN_CODE'] = $storeCode;
$_SERVER['MAGE_RUN_TYPE'] = 'store';

if (strstr($_SERVER['REQUEST_URI'], '_admin')) {
    $newRequestUri = str_replace('/' . $storeCode . '/', '', $requestUri);
} else {
    $newRequestUri = str_replace('/' . $storeCode, '', $requestUri);
}

$_SERVER['REQUEST_URI'] = $newRequestUri;
