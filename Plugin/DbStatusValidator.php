<?php

namespace OuterEdge\Base\Plugin;

use Closure;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Cache\FrontendInterface as FrontendCacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\Module\Plugin\DbStatusValidator as MagentoDbStatusValidator;

class DbStatusValidator extends MagentoDbStatusValidator
{
    protected $errors;

    protected $productMetadata;

    public function __construct(FrontendCacheInterface $cache, DbVersionInfo $dbVersionInfo, ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
        parent::__construct($cache, $dbVersionInfo);
    }

    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0') != -1) {
            try {
                parent::beforeDispatch($subject, $request);
            } catch (LocalizedException $ex) {
                $this->errors = $ex->getMessage();
            }
        }
    }

    public function aroundDispatch(FrontController $subject, Closure $proceed, RequestInterface $request)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0') == -1) {
            try {
                return parent::aroundDispatch($subject, $proceed, $request);
            } catch (LocalizedException $ex) {
                $this->errors = $ex->getMessage();
            }
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
