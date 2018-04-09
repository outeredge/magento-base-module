<?php

namespace OuterEdge\Base\Plugin;

use Closure;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Plugin\DbStatusValidator as MagentoDbStatusValidator;

class DbStatusValidator extends MagentoDbStatusValidator
{
    protected $errors;

    public function aroundDispatch(FrontController $subject, Closure $proceed, RequestInterface $request)
    {
        try {
            parent::aroundDispatch($subject, $proceed, $request);
        } catch (LocalizedException $ex) {
            $this->errors = $ex->getMessage();
        }

        return $proceed($request);
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