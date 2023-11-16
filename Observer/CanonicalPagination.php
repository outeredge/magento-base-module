<?php

namespace OuterEdge\Base\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Helper\Category;
use Magento\Framework\View\Page\Config;

class CanonicalPagination implements ObserverInterface
{
    protected $categoryHelper;
    protected $pageConfig;

    public function __construct(
        Category $categoryHelper,
        Config $pageConfig
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ('catalog_category_view' != $observer->getEvent()->getFullActionName()) {
            return $this;
        }

        /** @var \Magento\Catalog\Block\Product\ListProduct $productListBlock */
        $productListBlock = $observer->getEvent()->getLayout()->getBlock('category.products.list');
        if (!$productListBlock) {
            return $this;
        }

        /** @var \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbarBlock */
        $toolbarBlock = $productListBlock->getToolbarBlock();

        if ($toolbarBlock->getCurrentPage() == 1) {
            return $this;
        }

        $category = $productListBlock->getLayer()->getCurrentCategory();
        /** Remove default canonical tag */
        if ($this->categoryHelper->canUseCanonicalTag()) {
            $this->pageConfig->getAssetCollection()->remove($category->getUrl());

            /** Add canonical full url */
            $this->pageConfig->addRemotePageAsset(
                $toolbarBlock->getPagerUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
    }
}
