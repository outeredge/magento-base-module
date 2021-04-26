<?php

namespace OuterEdge\Widget\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template;

Class Wysiwyg extends Template
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $factoryElement;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        $data = []
    ) {
        $this->factoryElement = $factoryElement;
        $this->wysiwygConfig = $wysiwygConfig;
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
        $data = $element->getData();

        if (base64_encode(base64_decode($data['value'], true)) === $data['value']){
            $data['value'] = base64_decode($data['value']);
        }

        $editor = $this->factoryElement->create('editor', ['data' => $data])
            ->setLabel('')
            ->setForm($element->getForm())
            ->setWysiwyg(true);

        $config = $this->wysiwygConfig->getConfig([
            'add_variables' => false,
            'add_widgets' => false,
            'add_images' => true
        ]);

        $pluginstoremove = [
            'magentovariable',
            'magentowidget'
        ];

        $tinymceconfig = $config['settings'];
        $tinymceconfig['toolbar1'] = str_replace($pluginstoremove, '', $tinymceconfig['toolbar1']);
        $tinymceconfig['toolbar2'] = str_replace($pluginstoremove, '', $tinymceconfig['toolbar2']);
        $tinymceconfig['plugins'] = str_replace($pluginstoremove, '', $tinymceconfig['plugins']);

        $tinymceconfig['templates'] = [
            [
                "title" => "Image Right",
                "description" => "Row with image on right",
                "content" => '
                    <section class="leftright-row">
                        <div class="leftright__item">
                            <div class="leftright__content">
                                <h2>Title here</h2>         
                                <p>Content Here</p>
                            </div>
                        </div>
                        <div class="leftright__item leftright--image">
                            <picture>
                                <img src="placeholder" />
                            </picture>
                        </div>
                    </section>'
            ],
            [
                "title" => "Image Left",
                "description" => "Row with image on left",
                "content" => '
                    <section class="leftright-row">
                        <div class="leftright__item leftright--image">
                            <picture>
                                <img src="placeholder" />
                            </picture>
                        </div>
                        <div class="leftright__item">
                            <div class="leftright__content">
                                <h2>Title here</h2>         
                                <p>Content Here</p>
                            </div>
                        </div>
                    </section>'
            ]
        ];

        $config->addData(['settings' => $tinymceconfig]);

        $editor->setConfig($config);

        if ($element->getRequired()) {
            $editor->addClass('required-entry');
        }

        $element->setData('after_element_html', $editor->getElementHtml());
        $element->setValue(''); // Hides the additional label that gets added.

        return $element;
    }
}
