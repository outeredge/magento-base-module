<?php

class Edge_Base_Block_Scripts extends Mage_Core_Block_Template
{
    protected $_scripts = array();

    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('edge/scripts/scripts.phtml');
        }
    }

    public function addScript($name, $params=null)
    {
        $this->_scripts[] = new Varien_Object(array(
            'name'   => $name,
            'script' => null,
            'params' => $params
        ));
    }

    public function addScriptRaw($script, $params=null)
    {
        $this->_scripts[] = new Varien_Object(array(
            'name'   => null,
            'script' => $script,
            'params' => $params
        ));
    }

    public function addSkinJs($name, $params=null)
    {
        $this->_scripts[] = new Varien_Object(array(
            'name'   => $this->getSkinUrl($name),
            'script' => null,
            'params' => $params
        ));
    }

    public function getScripts()
    {
        return $this->_scripts;
    }
}