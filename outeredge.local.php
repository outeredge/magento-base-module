<?php
/*
    Configuration for Magento/Setup (before Interceptors can be used).

    To enable our classes this file needs to be copied into ~/setup/config/autoload/
    which can be done like so composer.json:

        "scripts": {
            "pre-autoload-dump": [
                "cp vendor/outeredge/magento-base-module/outeredge.local.php setup/config/autoload/",
                ...
*/

return [
    'service_manager' => [
        'aliases' => [
            \Magento\Framework\App\DeploymentConfig\Writer::class => \OuterEdge\Base\Framework\App\DeploymentConfig\Writer::class
        ]
    ]
];
