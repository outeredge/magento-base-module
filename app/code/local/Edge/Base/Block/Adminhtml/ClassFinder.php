<?php

class Edge_Base_Block_Adminhtml_ClassFinder extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'classfinder_form'));
        $form->setUseContainer(true);

        $fieldset = $form->addFieldset('content_fieldset', array(
            'legend' => Mage::helper('adminhtml')->__('Helpers'),
            'class'  => 'fieldset-wide'
        ));

        foreach (array('block', 'model', 'collection', 'helper') as $type) {
            $fieldset->addField($type, 'text', array(
                'label' => Mage::helper('adminhtml')->__(ucfirst($type)),
                'name'  => $type,
                'after_element_html' => '<p><a data-type="' . $type . '" href="' . Mage::helper('adminhtml')->getUrl('*/*/' . $type) . '">Submit</a></p>'
            ));
        }

        $form->addFieldset('results_fieldset', array(
            'legend' => Mage::helper('adminhtml')->__('Results'),
            'class'  => 'fieldset-wide'
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
