<?php

class Edge_Base_Model_Core_File_Storage_Database extends Mage_Core_Model_File_Storage_Database
{
    /**
     * Rename files in database
     *
     * @param  string $oldFilePath
     * @param  string $newFilePath
     * @return Mage_Core_Model_File_Storage_Database
     */
    public function renameFile($oldFilePath, $newFilePath)
    {
        $this->_getResource()->renameFile(
            basename($oldFilePath),
            dirname($oldFilePath),
            basename($newFilePath),
            dirname($newFilePath)
        );

        $newPath = dirname($newFilePath);
        $directory = Mage::getModel('core/file_storage_directory_database')->loadByPath($newPath);

        if (!$directory->getId()) {
            $directory = $this->getDirectoryModel()->createRecursive($newPath);
        }

        $this->loadByFilename($newFilePath);
        if ($this->getId()) {
            $this->setDirectoryId($directory->getId())->save();

            // outer/edge Quick Fix
            // Separate Database connection stops the directory_id being updated
            $db = Mage::getSingleton('core/resource')->getConnection($this->getConfigConnectionName());
            $db->update('core_file_storage', array('directory_id' => $directory->getId()), "file_id = {$this->getId()}");
        }

        return $this;
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
