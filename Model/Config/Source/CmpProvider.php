<?php

namespace OuterEdge\Base\Model\Config\Source;

class CmpProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    public const CMP_COOKIEBOT = 'cookiebot';
    public const CMP_TERMLY    = 'termly';

    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('None')],
            ['value' => self::CMP_COOKIEBOT, 'label' => __('Cookiebot')],
            ['value' => self::CMP_TERMLY, 'label' => __('Termly')]
        ];
    }
}
