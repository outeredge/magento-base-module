<?php

namespace OuterEdge\Base\Helper;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Canonical extends AbstractHelper
{
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
        if ($this->scopeConfig->getValue('web/default/cms_home_page') == $this->cmsPage->getIdentifier()) {
            return '<link rel="canonical" href="' . trim($this->scopeConfig->getValue('web/unsecure/base_url'),'/') . '" />';
        }
        elseif ($this->cmsPage->getId()) {
            return '<link rel="canonical" href="' . $this->scopeConfig->getValue('web/unsecure/base_url') . $this->cmsPage->getIdentifier() . '" />';
        }

        return '';
    }
}
