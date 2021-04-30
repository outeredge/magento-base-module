<?php

namespace OuterEdge\Base\Block\Html;

class BannerImage extends \Magento\Cms\Block\Page
{
    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = $this->_filterProvider->getPageFilter()->filter($this->getPage()->getBannerImage());
        return $html;
    }
}
