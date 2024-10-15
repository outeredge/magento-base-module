<?php

namespace OuterEdge\Base\Helper;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Canonical extends AbstractHelper
{
    public const CONFIG_BASE_URL = 'web/unsecure/base_url';

    /**
     * @var Page
     */
    protected $cmsPage;

    /**
     * Canonical constructor.
     * @param Context $context
     * @param Page    $cmsPage
     */
    public function __construct(
        Context $context,
        Page $cmsPage
    ) {
        $this->cmsPage = $cmsPage;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getCanonicalForCmsPage(): string
    {
        $baseUrl = $this->scopeConfig->getValue(
            self::CONFIG_BASE_URL,
            ScopeInterface::SCOPE_STORE
        );

        if ($this->scopeConfig->getValue('web/default/cms_home_page') == $this->cmsPage->getIdentifier()) {
            return '<link rel="canonical" href="' . rtrim($baseUrl,'/') . '" />';
        } elseif ($this->cmsPage->getId()) {
            return '<link rel="canonical" href="' . $baseUrl . $this->cmsPage->getIdentifier() . '" />';
        }

        return '';
    }
}
