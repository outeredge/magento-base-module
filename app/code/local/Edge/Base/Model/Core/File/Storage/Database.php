<?php

class Edge_Base_Model_Core_File_Storage_Database extends Mage_Core_Model_File_Storage_Database
{
    /**
     * Get resource instance
     * outer/edge - Connection name was set on first request only
     *              Ensure connection name is always set
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _getResource()
    {
        $connectionName = $this->getConnectionName();
        if (empty($connectionName)) {
            $connectionName = $this->getConfigConnectionName();
        }

        $resource = parent::_getResource();
        $resource->setConnectionName($connectionName);

        return $resource;
    }

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
