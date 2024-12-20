<?php
namespace OuterEdge\Base\Plugin;

use Magento\Eav\Model\Config;

class PreventEavErrors
{
    protected $columnsToCheck = ['attribute_model', 'backend_model', 'frontend_model', 'source_model'];

    public function afterGetEntityAttributes(Config $subject, $attributes)
    {
        foreach($attributes as $attribute) {
            $eavAttr = $attribute->getData();
            foreach($this->columnsToCheck as $column) {
                if (!is_null($eavAttr[$column]) && !class_exists($eavAttr[$column])) {
                    $attributes[$eavAttr['attribute_code']]->setData($column, null);
                }
            }
        }

        return $attributes;
    }
}
