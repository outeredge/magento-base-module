<?php

class Edge_Base_Block_Adminhtml_Form_Renderer_CategoryTree_Element extends Varien_Data_Form_Element_Abstract
{
    protected $_ids;
    protected $_fieldName;

    protected function _getChildCategories($categoryId)
    {
        $html = '<ul>';
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('parent_id', array('eq' => $categoryId));

        foreach ($categories as $category){
            $checked = in_array($category->getId(), $this->_ids) ? ' checked="checked"' : '';
            $html .= '<li>';
            if ($category->getId() > 1) {
                $html .= '<div>';
                $html .= '<input type="checkbox" name="' . $this->_fieldName . '[]" value="' . $category->getId() . '"' . $checked . '>';
                $html .= '<label>' . $category->getName() . '</label>';
                $html .= '</div>';
            }
            if($category->getChildren()){
                $html .= $this->_getChildCategories($category->getId());
            }
            $html .= '</li>';
        }
        $html.= '</ul>';
        return $html;
    }

    public function getElementHtml()
    {
        $this->_ids = (array)$this->getValue();
        $this->_fieldName = $this->getFieldName() ? $this->getFieldName() : $this->getId();

        return '
            <div class="category-tree">
            ' . $this->_getChildCategories(Mage::app()->getStore()->getRootCategoryId()) . '
            </div>
            <style type="text/css">
                .category-tree ul ul ul{
                    margin-left: 20px;
                    line-height: 100%;
                }
                .category-tree > ul > li > ul > li{
                    padding-bottom: 10px;
                }
                .category-tree input{
                    vertical-align: middle;
                }
                .category-tree label{
                    vertical-align: middle;
                    margin-left: 5px
                }
            </style>
        ';
    }
}
