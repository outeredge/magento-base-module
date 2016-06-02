<?php
namespace OuterEdge\Base\Helper;

use Magento\Framework\Model\Context;
use Magento\Framework\App\State;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $isDeveloper = false;

    public function __construct(Context $context)
    {
        $this->isDeveloper = $context->getAppState()->getMode() == State::MODE_DEVELOPER;
    }

    /**
     * True if running on developer mode
     * @return type
     */
    public function isDeveloperMode()
    {
        return $this->isDeveloper;
    }
}
