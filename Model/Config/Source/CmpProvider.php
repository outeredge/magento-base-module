<?php

namespace OuterEdge\Base\Model\Config\Source;

class CmpProvider implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => '', 'label' => __('None')],
    ['value' => 'cookiebot', 'label' => __('Cookiebot')],
    ['value' => 'termly', 'label' => __('Termly')]
  ];
 }
}
