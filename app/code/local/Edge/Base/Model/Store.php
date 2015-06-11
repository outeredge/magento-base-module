<?php

class Edge_Base_Model_Store extends Mage_Core_Model_Store
{
    public function getBaseUrl($type = self::URL_TYPE_LINK, $secure = null)
    {
        $cacheKey = $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {

            if(Mage::getIsDeveloperMode()){
                // Relative Base URL

                $dbBaseUrl = Mage::getStoreConfig('web/unsecure/base_url');
                $baseUrl = parent::getBaseUrl($type, $secure);
                $url = str_replace($dbBaseUrl, "http://" . $_SERVER['HTTP_HOST'] . "/", $baseUrl);

                $this->_baseUrlCache[$cacheKey] = $url;
            }
        }

        return parent::getBaseUrl($type, $secure);
    }

    public function getConfig($path)
    {
        if (Mage::getIsDeveloperMode()) {
            switch ($path) {
                case 'web/secure/use_in_frontend':
                case 'web/secure/use_in_adminhtml':
                case 'web/url/redirect_to_base':
                    return null;
            }
        }
        return parent::getConfig($path);
    }
}