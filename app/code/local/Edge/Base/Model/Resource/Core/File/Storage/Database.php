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

    /**
     * Delete files that starts with given $folderName
     * Avoid using LIKE% on delete
     *
     * @param string $folderName
     */
    public function deleteFolder($folderName = '')
    {
        $folderName = rtrim($folderName, '/');
        if (!strlen($folderName)) {
            return;
        }

        /* @var $resHelper Mage_Core_Model_Resource_Helper_Abstract */
        $resHelper = Mage::getResourceHelper('core');
        $likeExpression = $resHelper->addLikeEscape($folderName . '/', array('position' => 'start'));

        $fileIds = $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()
                ->select()
                ->from($this->getMainTable(), array($this->getIdFieldName()))
                ->where(new Zend_Db_Expr('directory LIKE ' . $likeExpression))
            );

        if (!empty($fileIds)) {
            $this->_getWriteAdapter()
                ->delete($this->getMainTable(), $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . ' IN (?)', $fileIds));
        }
    }
}

