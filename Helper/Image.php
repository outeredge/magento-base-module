<?php

namespace OuterEdge\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Framework\Image\Adapter\Gd2;
use Magento\MediaStorage\Helper\File\Storage\Database;

class Image extends AbstractHelper
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;
    
    /**
     * @var Database
     */
    protected $coreFileStorageDatabase;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Read
     */
    protected $mediaDirectory;
    
    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param Database $coreFileStorageDatabase
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        Database $coreFileStorageDatabase,
        StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    
    /**
     * @return Read
     */
    protected function getMediaDirectory()
    {
        if (!$this->mediaDirectory) {
            $this->mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }
    
    /**
     * Setup the image object ready for resizing/cropping
     *
     * @param string $image
     * @param int|null $width
     * @param int|null $height
     * @param array $options
     * @return Gd2
     */
    protected function setup($image, $width = null, $height = null, $options = [])
    {
        $imageResize = $this->imageFactory->create();
        $imageResize->open($this->getMediaDirectory()->getAbsolutePath($image));
        if (!empty($options)) {
            foreach ($options as $method => $value) {
                $imageResize->$method($value);
            }
        }
        return $imageResize;
    }
    
    /**
     * Resize an image
     *
     * @param string $image
     * @param int|null $width
     * @param int|null $height
     * @param array $options
     * @return string
     */
    public function resize($image, $width = null, $height = null, $options = [])
    {
        if (!$this->fileExists($image)) {
            return $this->getMediaImageUrl($image);
        }
        
        $resizedImage = 'resized/' . $width . 'x' . $height . '/' . $image;
        if (!$this->fileExists($resizedImage)) {
            $imageResize = $this->setup($image, $width, $height, $options);
            $imageResize->resize($width, $height);
            $imageResize->save($this->getMediaDirectory()->getAbsolutePath($resizedImage));
        }
        
        return $this->getMediaImageUrl($resizedImage);
    }
    
    /**
     * Crop an image
     *
     * @param string $image
     * @param int $width
     * @param int $height
     * @param array $options
     * @return string
     */
    public function crop($image, $width, $height, $options = [])
    {
        if (!$this->fileExists($image)) {
            return $this->getMediaImageUrl($image);
        }

        $croppedImage = 'cropped/' . $width . 'x' . $height . '/' . $image;
        if (!$this->fileExists($croppedImage)) {
            $imageCrop = $this->setup($image, $width, $height, array_merge($options, ['constrainOnly' => false, 'keepAspectRatio' => true, 'keepFrame' => false]));
            
            $originalAspectRatio = $imageCrop->getOriginalWidth() / $imageCrop->getOriginalHeight();
            $aspectRatio = $width / $height;
            
            if ($aspectRatio < $originalAspectRatio) {
                $cropWidth = ceil($height * $originalAspectRatio);
                $cropHorizontal = ($cropWidth - $width) / 2;
                $imageCrop->resize($cropWidth, $height);
                $imageCrop->crop(0, $cropHorizontal, $cropHorizontal, 0);
            } else {
                $cropHeight = ceil($width / $originalAspectRatio);
                $cropVertical = ($cropHeight - $height) / 2;
                $imageCrop->resize($width, $cropHeight);
                $imageCrop->crop($cropVertical, 0, 0, $cropVertical);
            }
            
            $imageCrop->save($this->getMediaDirectory()->getAbsolutePath($croppedImage));
        }
        
        return $this->getMediaImageUrl($croppedImage);
    }
    
    /**
     * Get media image url
     *
     * @param string $image
     * @return string 
     */
    protected function getMediaImageUrl($image)
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $image;
    }
    
    /**
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
     */
    protected function fileExists($filename)
    {
        if ($this->getMediaDirectory()->isFile($filename)) {
            return true;
        } else {
            return $this->coreFileStorageDatabase->saveFileToFilesystem(
                $this->getMediaDirectory()->getAbsolutePath($filename)
            );
        }
    }
}
