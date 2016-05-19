<?php

class Edge_Base_Block_CatalogSearch_Result extends Mage_CatalogSearch_Block_Result
{
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setRobots('NOINDEX,FOLLOW');
        return parent::_prepareLayout();
    }
}
