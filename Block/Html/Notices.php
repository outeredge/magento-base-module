<?php

namespace OuterEdge\Base\Block\Html;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use OuterEdge\Base\Plugin\DbStatusValidator;

class Notices extends Template
{
    protected $dbStatusValidator;

    public function __construct(Context $context, DbStatusValidator $dbStatusValidator, array $data = array())
    {
        $this->dbStatusValidator = $dbStatusValidator;
        parent::__construct($context, $data);
    }

    public function hasErrors()
    {
        return $this->dbStatusValidator->hasErrors();
    }

    public function getErrorMessages()
    {
        return $this->dbStatusValidator->getErrorMessages();
    }
}
