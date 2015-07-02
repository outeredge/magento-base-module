<?php

class Edge_Base_Model_Core_File_Storage_Database extends Mage_Core_Model_File_Storage_Database
{
    /**
     * Return directory listing
     *
     * @param string $directory
     * @return mixed
     */
    public function getDirectoryFilesWithoutContent($directory)
    {
        $directory = Mage::helper('core/file_storage_database')->getMediaRelativePath($directory);
        return $this->_getResource()->getDirectoryFilesWithoutContent($directory);
    }
}
