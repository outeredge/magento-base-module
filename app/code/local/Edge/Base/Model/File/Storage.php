<?php

class Edge_Base_Model_File_Storage extends Mage_Core_Model_File_Storage
{
    public function synchronize($storage)
    {
        set_time_limit(0);
        return parent::synchronize($storage);
    }
}