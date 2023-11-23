<?php

namespace OuterEdge\Base\Plugin\Wysiwyg;

class Config
{
    protected $activeEditor;

    public function __construct(\Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor)
    {
        $this->activeEditor = $activeEditor;
    }

    public function afterGetConfig(
        \Magento\Ui\Component\Wysiwyg\ConfigInterface $subject,
        \Magento\Framework\DataObject $result
    ) {
        $tinymceConfig = $result->getData('tinymce');
        $updatedConfig = $tinymceConfig;

        $updatedConfig['toolbar'] = isset($tinymceConfig['toolbar']) ? $tinymceConfig['toolbar'] . ' anchor' : null;
        $updatedConfig['plugins'] = isset($tinymceConfig['plugins']) ? $tinymceConfig['plugins'] . ' anchor' : null;

        foreach ($updatedConfig as $config => $value) {
            if (!$value) {
                unset($updatedConfig[$config]);
                continue;
            }
            $tinymceConfig[$config] = $value;
        }

        $result->addData([
            'tinymce' => $updatedConfig
        ]);

        return $result;
    }
}

