<?php

class Edge_Base_Helper_Image extends Mage_Core_Helper_Abstract
{
    protected $_scheduleModify = false;

    protected $_width  = null;
    protected $_height = null;
    protected $_quality = 100;

    protected $_keepAspectRatio;
    protected $_keepFrame;
    protected $_constrainOnly;
    protected $_backgroundColor;

    protected $_crop;

    public function getImage($file)
    {
        if ($this->_scheduleModify) {
            $imageUrl = $this->_getModifiedImage($file);
        } else {
            $imageUrl = Mage::getBaseUrl('media') . $file;
        }

        // Remove the helper from registry
        // Ensures non singleton
        Mage::unregister('_helper/edge/image');

        return $imageUrl;
    }

    protected function _getModifiedImage($file)
    {
        $mediaDir = Mage::getBaseDir('media') . DS;
        $filePath = $mediaDir . $file;

        if ($this->_width || $this->_height) {
            if (!$this->_width) {
                $this->_width = $this->_height;
            }
            if (!$this->_height) {
                $this->_height = $this->_width;
            }
        }

        $mediaUrl = Mage::getBaseUrl('media');

        $fileParts = explode('/', $file);
        $fileName = array_pop($fileParts);
        $fileParts[] = $this->_width . 'x' . $this->_height;
        if ($this->_crop) {
            $fileParts[] = 'crop';
        }
        $fileParts[] = '';
        $modifiedPath = implode(DS, $fileParts);
        $modifiedUrl = $mediaDir . $modifiedPath . $fileName;

        if (!file_exists($modifiedUrl)) {

            $image = new Varien_Image($filePath);
            $image->quality($this->_quality);

            if ($this->_keepAspectRatio) {
                $image->keepAspectRatio($this->_keepAspectRatio);
            }
            if ($this->_keepFrame) {
                $image->keepFrame($this->_keepFrame);
            }
            if ($this->_constrainOnly) {
                $image->constrainOnly($this->_constrainOnly);
            }
            if ($this->_backgroundColor) {
                $image->backgroundColor($this->_backgroundColor);
            }

            $originalAspectRatio = $image->getOriginalWidth() / $image->getOriginalHeight();
            $aspectRatio = $this->_width / $this->_height;

            if ($this->_crop) {
                if ($aspectRatio < $originalAspectRatio) {
                    // Narrower than original
                    $newWidth = ceil($this->_height * $originalAspectRatio);
                    $cropHorizontal = ($newWidth - $this->_width) / 2;
                    $image->resize($newWidth, $this->_height);
                    $image->crop(0, $cropHorizontal, $cropHorizontal, 0);
                }
                else {
                    // Shorter than original
                    $newHeight = ceil($this->_width / $originalAspectRatio);
                    $cropVertical = ($newHeight - $this->_height) / 2;
                    $image->resize($this->_width, $newHeight);
                    $image->crop($cropVertical, 0, 0, $cropVertical);
                }
            }
            else {
                $image->resize($this->_width, $this->_height);
            }

            $modifiedUrl = $mediaDir . $modifiedPath . $fileName;
            $image->save($modifiedUrl);
        }

        return $mediaUrl . $modifiedPath . $fileName;
    }

    public function setSize($width, $height=null)
    {
        $this->_width = $width;
        $this->_height = $height;
        $this->_scheduleModify = true;
        return $this;
    }

    public function setWidth($width)
    {
        $this->_width = $width;
        $this->_scheduleModify = true;
        return $this;
    }

    public function setHeight($height)
    {
        $this->_height = $height;
        $this->_scheduleModify = true;
        return $this;
    }

    public function setQuality($quality)
    {
        $this->_quality = $quality;
        return $this;
    }

    public function setKeepAspectRatio($keepAspectRatio)
    {
        $this->_keepAspectRatio = $keepAspectRatio;
        return $this;
    }

    public function setKeepFrame($keepFrame)
    {
        $this->_keepFrame = $keepFrame;
        return $this;
    }

    public function setConstrainOnly($constrainOnly)
    {
        $this->_constrainOnly = $constrainOnly;
        return $this;
    }

    public function setCrop($crop)
    {
        $this->_crop = $crop;
        return $this;
    }

    public function setBackgroundColor($backgroundColor)
    {
        $this->_backgroundColor = $backgroundColor;
        return $this;
    }
}
