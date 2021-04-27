<?php

namespace OuterEdge\Block\Adminhtml\Widget;

Class TextField extends \Magento\Backend\Block\Template {
    protected $_elementFactory;
/**
 * @param \Magento\Backend\Block\Template\Context $context
 * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
 * @param array $data
 */
public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Data\Form\Element\Factory $elementFactory,
    array $data = []
) {
    $this->_elementFactory = $elementFactory;
    parent::__construct($context, $data);
}

/**
 * Prepare chooser element HTML
 *
 * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
 * @return \Magento\Framework\Data\Form\Element\AbstractElement
 */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $input = $this->_elementFactory->create("textarea", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-textarea admin__control-text");
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData('after_element_html', $input->getElementHtml());
        return $element;
    }
}
