<?php

class Edge_Base_Block_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
    public function removeLink($name)
    {
//        foreach ($this->_links as $key=>$value){
//            echo $key.'<hr>';
//        }
        if(isset($this->_links[$name])){
            unset($this->_links[$name]);
        }
        return $this;
    }
}