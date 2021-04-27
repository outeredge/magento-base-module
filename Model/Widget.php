<?php

namespace OuterEdge\Model;

use Magento\Widget\Model\Widget as BaseWidget;

class Widget
{
    public function beforeGetWidgetDeclaration(BaseWidget $subject, $type, $params = [], $asIs = true)
    {
        if (!strstr($type, "Magento\\")) {
            foreach ($params as $paramKey => $value) {
                if (strstr($paramKey, 'wysiwyg')) {
                    $params[$paramKey] = base64_encode($value);
                }
            }
        }

        return array($type, $params, $asIs);
    }
}
