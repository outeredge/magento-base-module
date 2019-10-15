<?php

namespace OuterEdge\Base\Plugin;

use Magento\Deploy\Model\Plugin\ConfigChangeDetector as MagentoConfigChangeDetector;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Prevents config changes from breaking development sites
 */
class ConfigChangeDetector extends MagentoConfigChangeDetector
{
    protected $errors = [];

    public function beforeDispatch(FrontControllerInterface $subject, RequestInterface $request)
    {
        try {
            parent::beforeDispatch($subject, $request);
        } catch (LocalizedException $ex) {
            $this->errors[] = $ex->getMessage();
        }
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getErrorMessages()
    {
        return $this->errors;
    }
}