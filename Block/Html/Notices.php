<?php

namespace OuterEdge\Base\Block\Html;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use OuterEdge\Base\Plugin\DbStatusValidator;
use OuterEdge\Base\Plugin\ConfigChangeDetector;

class Notices extends Template
{
    protected $dbStatusValidator;

    protected $configChangeDetector;

    public function __construct(
        Context $context,
        DbStatusValidator $dbStatusValidator,
        ConfigChangeDetector $configChangeDetector,
        array $data = []
    ) {
        $this->dbStatusValidator = $dbStatusValidator;
        $this->configChangeDetector = $configChangeDetector;

        parent::__construct($context, $data);
    }

    public function hasErrors()
    {
        return $this->dbStatusValidator->hasErrors() || $this->configChangeDetector->hasErrors();
    }

    public function getErrorMessages()
    {
        return array_merge(
            $this->dbStatusValidator->getErrorMessages(),
            $this->configChangeDetector->getErrorMessages()
        );
    }
}
