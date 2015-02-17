<?php

class Edge_Base_Model_Template_Filter extends Mage_Widget_Model_Template_Filter
{
    /**
     * Retrieve media file URL directive
     *
     * @param array $construction
     * @return string
     */
    public function mediaDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        return Mage::helper('edge/image')->getImage($params['url']);
    }
}