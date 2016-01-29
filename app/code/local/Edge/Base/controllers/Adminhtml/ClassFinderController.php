<?php

class Edge_Base_Adminhtml_ClassFinderController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {        
        return Mage::getSingleton('admin/session')->isAllowed('system/tools/class_finder');
    }
    
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function blockAction()
    {
        $class = $this->getRequest()->getParam('class');
        $block = $this->getLayout()->createBlock($class);
        return $this->_jsonResponse($block);
    }

    public function modelAction()
    {
        $class = $this->getRequest()->getParam('class');
        $model = Mage::getModel($class);
        return $this->_jsonResponse($model);
    }

    public function collectionAction()
    {
        $class = $this->getRequest()->getParam('class');
        $collection = Mage::getModel($class)->getCollection();
        return $this->_jsonResponse($collection);
    }

    public function helperAction()
    {
        $class = $this->getRequest()->getParam('class');
        $helper = Mage::helper($class);
        return $this->_jsonResponse($helper);
    }

    protected function _jsonResponse($class)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(get_class($class)));
        return true;
    }
}
