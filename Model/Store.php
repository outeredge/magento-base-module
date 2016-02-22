<?php
namespace Edge\Base\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;

class Store extends \Magento\Store\Model\Store
{
    const PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    public function getBaseUrl($type = UrlInterface::URL_TYPE_LINK, $secure = null)
    {
        $cacheKey = $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {

            if ($this->_appState->getMode() == State::MODE_DEVELOPER) {

                $dbBaseUrl = $this->_config->getValue(self::PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
                $baseUrl = parent::getBaseUrl($type, $secure);
                $url = str_replace($dbBaseUrl, "http://" . $_SERVER['HTTP_HOST'] . "/", $baseUrl);
                
                $this->_baseUrlCache[$cacheKey] = $url;
            }
        }

        return parent::getBaseUrl($type, $secure);
    }
}
