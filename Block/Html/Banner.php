<?php

namespace OuterEdge\Base\Block\Html;

class Banner extends \Magento\Cms\Block\Page
{
    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = $this->_filterProvider->getPageFilter()->filter($this->getPage()->getBannerText());
        return $html;
    }
}
