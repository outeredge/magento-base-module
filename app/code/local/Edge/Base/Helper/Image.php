<?php

class Edge_Base_Helper_Image extends Mage_Core_Helper_Abstract
{
    public function getImage($file)
    {
        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }

        $mediaDir = Mage::getBaseDir('media');
        $imageDir = $mediaDir . $file;

        if (!file_exists($imageDir)){
            // If the file does not exist, create it from the database
            Mage::helper('core/file_storage_database')->saveFileToFilesystem($imageDir);
        }

        $mediaUrl = Mage::getBaseUrl('media');
        $imageUrl = $mediaUrl . $file;

        return $imageUrl;
    }
}
