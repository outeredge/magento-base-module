<?php
namespace OuterEdge\Base\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\Registry;
use Magento\Framework\View\Page\Config\Renderer;

class Robots
{
    protected $queryStrings = [
        'dir',
        'limit',
        'order',
        'cat',
        'product_list_dir',
        'product_list_limit',
        'product_list_order',
        '___from_store',
        '___store',
        'referer'
    ];

    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     *
     * @param PageConfig $pageConfig
     * @param Http $request
     */
    public function __construct(
        PageConfig $pageConfig,
        Http $request,
        Registry $registry,
    ) {
        $this->pageConfig = $pageConfig;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * Before Render function
     *
     * @param Renderer $subject
     */
    public function beforeRenderMetadata(Renderer $subject)
    {
        $params = $this->request->getParams();

        $control = false;
        if ($params) {
            foreach ($params as $key => $value) {
                if (in_array($key, $this->queryStrings)) {
                    $control = true;
                }
            }
        }

        if ($control) {
            $this->pageConfig->setRobots('NOINDEX,NOFOLLOW');

            if ($category = $this->registry->registry('current_category')) {
                $this->pageConfig->getAssetCollection()->remove($category->getUrl());
            }
        }
    }
}
