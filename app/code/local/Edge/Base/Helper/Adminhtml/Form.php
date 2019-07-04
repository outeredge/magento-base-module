<?php

class Edge_Base_Helper_Adminhtml_Form extends Mage_Core_Helper_Abstract
{
    public function productSelector($model, $fieldset, $id, $config=array(), $after='')
    {
        $selectedLabel = Mage::helper('edge')->__('Not Selected');
        $selectedId = $model->getData($id);
        if ($selectedId) {
            $selectedLabel = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($selectedId)
                ->getName() .
                ' (' . $selectedId . ')';
        }

        $model->setData($id.'value', $selectedId);

        $fieldset->addField($id.'value', 'hidden', array(
            'name' => $id
        ), $after);

        $fieldset->addField($id, 'note', array(
            'label'                 => isset($config['label']) ? $config['label'] : Mage::helper('edge')->__('Product'),
            'after_element_html'    => $this->_getChooserHtml($fieldset->getForm()->getHtmlIdPrefix().$id, $selectedLabel)
        ), $id.'value');
    }

    protected function _getChooserHtml($id, $label)
    {
        $chooseButton = Mage::app()->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setId($id.'control')
            ->setClass('btn-chooser')
            ->setLabel(Mage::helper('edge')->__('Select Product...'))
            ->setOnclick($id.'.choose()');

        return '
            <label class="widget-option-label" id="'.$id.'label">'.$label.'</label>
            <div id="'.$id.'advice-container" class="hidden"></div>
            <script>
                (function(){
                    var initChooser'.$id.' = function(){
                        window.'.$id.' = new WysiwygWidget.chooser("'.$id.'","'.$this->_getChooserUrl($id).'",{buttons:{open:"Select Product...",close:"Close"}})
                        $("'.$id.'value").advaiceContainer = "'.$id.'advice-container";
                        window.'.$id.'.setElementValue = function(value){
                            this.getElement().value = value.replace("product/","").split("/")[0];
                        }
                    }
                    if (document.loaded){
                        initChooser'.$id.'();
                    } else {
                        document.observe("dom:loaded", initChooser'.$id.');
                    }
                })();
            </script>
        ' . $chooseButton->toHtml();
    }

    protected function _getChooserUrl($id)
    {
        return Mage::getUrl('adminhtml/catalog_product_widget/chooser', array(
            'uniq_id' => $id,
            'key'     => Mage::getSingleton('core/session')->getFormKey()
        ));
    }
}
