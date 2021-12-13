<?php

namespace OuterEdge\Base\Framework\App\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;

class Writer extends DeploymentConfig\Writer
{
    protected $configReader;

    public function __construct(
        DeploymentConfig\Reader $reader,
        Filesystem $filesystem,
        ConfigFilePool $configFilePool,
        DeploymentConfig $deploymentConfig,
        DeploymentConfig\Writer\FormatterInterface $formatter = null,
        DeploymentConfig\CommentParser $commentParser = null
    ) {
        $this->configReader = $reader;

        parent::__construct($reader, $filesystem, $configFilePool, $deploymentConfig, $formatter, $commentParser);
    }

    /**
     * Avoid config.php being modified when modules are enabled/disabled in env.php
     */
    public function saveConfig(array $data, $override = false, $pool = null, array $comments = [])
    {
        $callingFunction = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? null;

        // check parent is createModulesConfig
        if ($callingFunction == 'createModulesConfig') {
            $currentData = $this->configReader->load(ConfigFilePool::APP_CONFIG);

            foreach ($data[ConfigFilePool::APP_CONFIG]['modules'] as $module => $status) {
                if (isset($currentData['modules'][$module])) {
                    // leave module status unchanged if it already exists inf config.php
                    $data[ConfigFilePool::APP_CONFIG]['modules'][$module] = $currentData['modules'][$module];
                }
            }
        }

        parent::saveConfig($data, $override, $pool, $comments);
    }
}
