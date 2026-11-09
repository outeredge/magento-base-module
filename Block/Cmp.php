<?php
declare(strict_types=1);

namespace OuterEdge\Base\Block;

use Magento\Framework\View\Element\Template;
use OuterEdge\Base\Helper\Config;

class Cmp extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly Config $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getConfigValue(string $path): ?string
    {
        return $this->configHelper->getConfig($path);
    }
}
