<?php

class Edge_Base_Model_Observer
{
    public function homePageCanonicalUrl(Varien_Event_Observer $observer) {
        
        $currentPage = Mage::getSingleton('cms/page')->getIdentifier();
        $homePage = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);
        
        if ($currentPage !== $homePage) {
            return;
        }
        
        Mage::app()->getLayout()->getBlock('head')->addLinkRel('canonical', Mage::getBaseUrl());
        
    }
}