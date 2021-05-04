<?php

namespace OuterEdge\Base\Block\Html;

class BannerImage extends \Magento\Cms\Block\Page
{
    protected $_helper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\View\Page\Config $pageConfig,
        \OuterEdge\Base\Helper\Image $helper,
        array $data = []
    ) {
        parent::__construct($context, $page, $filterProvider, $storeManager, $pageFactory, $pageConfig, $data);
        $this->_helper = $helper;
    }

    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $url = $this->_filterProvider->getPageFilter()->filter($this->getPage()->getBannerImage());

        $fullImageUrl = $this->_helper->get('cms/bannerimage/tmp' . $url);

        $html = "<img class='cms-banner-image' src='$fullImageUrl' />";

        return $html;
    }

    public function getImageUrl()
    {
        return $this->_filterProvider->getPageFilter()->filter($this->getPage()->getBannerImage());
    }
}
