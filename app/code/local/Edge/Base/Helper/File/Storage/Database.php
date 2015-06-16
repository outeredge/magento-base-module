<?php

/**
 * Database saving file helper
 *
 * @category    Edge
 * @package     Edge_Base
 * @author      outer/edge <hello@outeredgeuk.com>
 */
class Edge_Base_Helper_File_Storage_Database extends Mage_Core_Helper_File_Storage_Database
{
    /**
     * Check if cache before saving to DB storage
     *
     * @param string $filename
     */
    public function saveFile($filename)
    {
        if(strpos($filename, DS . 'cache' . DS)) {
            return false;
        }
        
        parent::saveFile($filename);
    }
}
