<?php
namespace Edge\Base\App\Area;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Backend\App\Config;
use Edge\Base\Helper\Data;

class FrontNameResolver extends \Magento\Backend\App\Area\FrontNameResolver
{
    protected $isDeveloper = false;

    public function __construct(
        Config $config,
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        parent::__construct($config, $deploymentConfig, $scopeConfig);
        $this->isDeveloper = $helper->isDeveloperMode();
    }

    public function isHostBackend()
    {
        if ($this->isDeveloper) {
            return "http://" . $_SERVER['HTTP_HOST'] . "/";
        }

        return parent::isHostBackend();
    }
}
