<?php

namespace OuterEdge\Base\Plugin;

use Closure;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Cache\FrontendInterface as FrontendCacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\Module\Plugin\DbStatusValidator as MagentoDbStatusValidator;

class DbStatusValidator extends MagentoDbStatusValidator
{
    protected $errors = [];

    protected $productMetadata;

    protected $appState;

    public function __construct(FrontendCacheInterface $cache, DbVersionInfo $dbVersionInfo, ProductMetadataInterface $productMetadata, State $appState)
    {
        $this->productMetadata = $productMetadata;
        $this->appState        = $appState;
        parent::__construct($cache, $dbVersionInfo);
    }

    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        if ($this->appState->getMode() != State::MODE_PRODUCTION) {
            try {
                parent::beforeDispatch($subject, $request);
            } catch (LocalizedException $ex) {
                $this->errors[] = $ex->getMessage();
            }
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
