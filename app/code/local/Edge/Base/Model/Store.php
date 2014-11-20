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
}