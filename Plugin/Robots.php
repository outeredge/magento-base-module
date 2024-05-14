<?php
namespace OuterEdge\Base\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer;

class Robots
{
    protected $queryStrings = [
        'dir',
        'limit',
        'order',
        'cat',
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
     *
     * @param PageConfig $pageConfig
     * @param Http $request
     */
    public function __construct(
        PageConfig $pageConfig,
        Http $request
    ) {
        $this->pageConfig = $pageConfig;
        $this->request = $request;
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
        }
    }
}
