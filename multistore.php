<?php

$files = glob(__DIR__ . '/../../../app/etc/env.php.*');
if (empty($files) || empty($_SERVER['CONFIG__DEFAULT__WEB__SECURE__BASE_URL'])) {
    return;
}

$validStores = ['default' => 'default*'];
foreach ($files as $file) {
    $code = substr(strrchr($file, '.'), 1);
    if ($code != 'production') {
        $validStores[$code] = $code;
    }
}

if (empty($_SERVER['MAGE_RUN_CODE'])
    && stristr($_SERVER['PHP_SELF'], 'bin/magento')
    && php_sapi_name() === 'cli'
) {
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

if ($_SERVER['SCRIPT_NAME'] == '/static.php' && !empty($_SERVER['HTTP_REFERER'])) {
    $requestUri = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);   
} else {
    $requestUri = $_SERVER['REQUEST_URI'];
}

// Determine the store view code from the URL
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
    $newRequestUri = str_replace('/' . $storeCode . '/', '/', $requestUri);
}

$_SERVER['REQUEST_URI'] = $newRequestUri;
