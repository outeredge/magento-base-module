<?php

namespace OuterEdge\Base\Plugin\Framework\App;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config as AppConfig;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Disable CDN's in Admin
     */
    public function aroundGetValue(AppConfig $subject, callable $proceed,
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if (($path == Store::XML_PATH_SECURE_BASE_MEDIA_URL || $path == Store::XML_PATH_UNSECURE_BASE_MEDIA_URL)
            && ($scope != ScopeInterface::SCOPE_STORE || $scopeCode == Store::ADMIN_CODE)
        ) {
            try {
                if($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
                    return null;
                }
            } catch (LocalizedException $ex) {
                // ignore
            }
        }

        return $proceed($path, $scope, $scopeCode);
    }
}
