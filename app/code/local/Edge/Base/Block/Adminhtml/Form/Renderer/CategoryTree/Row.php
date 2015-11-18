<?php

class Edge_Base_Block_Adminhtml_Form_Renderer_CategoryTree_Row
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    protected function _construct()
    {
        $this->setTemplate('edge/form/renderer/categorytree.phtml');
    }
}
