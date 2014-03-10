<?php
/**
 * Adminhtml page
 *
 * @category    Edge
 * @package     Edge_Base
 * @author      outer/eadge team <hello@outeredgeuk.com>
 */
class Edge_Base_Block_Adminhtml_Page extends Mage_Adminhtml_Block_Page
{
    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        Mage::getDesign()->setTheme('edge');
        parent::__construct();
    }
}
