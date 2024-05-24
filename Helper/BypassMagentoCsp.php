<?php

namespace OuterEdge\Base\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager;
use Magento\Csp\Helper\CspNonceProvider;

class BypassMagentoCsp
{
    public function __construct(
        protected Manager $moduleManager
    ) {
    }

    public function generateNonce(): string
    {
        if ($this->moduleManager->isOutputEnabled('Magento_Csp')) {
            $cspNonceProvider = ObjectManager::getInstance()->get(CspNonceProvider::class);
            return $cspNonceProvider->generateNonce();
        }
        return '';
    }
}
