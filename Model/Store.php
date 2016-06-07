<?php
namespace OuterEdge\Base\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;

class Store extends Store
{
    protected $isDeveloper = false;

    public function _construct()
    {
        $this->isDeveloper = $this->_appState->getMode() == State::MODE_DEVELOPER;
    }

    public function getBaseUrl($type = UrlInterface::URL_TYPE_LINK, $secure = null)
    {
        $cacheKey = $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {

            if ($this->isDeveloper) {

                $dbBaseUrl = $this->_config->getValue(Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
                $baseUrl   = parent::getBaseUrl($type, $secure);
                $host      = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(trim($dbBaseUrl), PHP_URL_HOST);
                $url       = str_replace($dbBaseUrl, "http://$host/", $baseUrl);

                $this->_baseUrlCache[$cacheKey] = $url;
            }
        }

        return parent::getBaseUrl($type, $secure);
    }

    public function getConfig($path)
    {
        if ($this->isDeveloper) {
            switch ($path) {
                case Store::XML_PATH_SECURE_IN_FRONTEND:
                case Store::XML_PATH_SECURE_IN_ADMINHTML:
                    return null;
            }
        }
        return parent::getConfig($path);
    }
}
