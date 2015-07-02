<?php

class Edge_Base_Model_Resource_Core_File_Storage_Database extends Mage_Core_Model_Resource_File_Storage_Database
{
    /**
     * Return directory file listing without content
     *
     * @param string $directory
     * @return mixed
     */
    public function getDirectoryFilesWithoutContent($directory)
    {
        $directory = trim($directory, '/');
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
                array('e' => $this->getMainTable()),
                array(
                    'filename',
                    'directory'
                )
            )
            ->where($adapter->prepareSqlCondition('directory', array('seq' => $directory)))
            ->order('file_id');

        return $adapter->fetchAll($select);
    }
}

