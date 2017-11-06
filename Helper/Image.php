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
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
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
     *
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
     *
     * @return string
     */
    public function resize($image, $width = null, $height = null, $options = [])
    {
        $imageResize = $this->setup($image, $width, $height, $options);
        $imageResize->resize($width, $height);
        
        $resizedImage = 'resized/' . $width . 'x' . $height . '/' . $image;
        $imageResize->save($this->getMediaDirectory()->getAbsolutePath($resizedImage));
        
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $resizedImage;
    }
    
    /**
     * Crop an image
     *
     * @param string $image
     * @param int $width
     * @param int $height
     * @param array $options
     *
     * @return string
     */
    public function crop($image, $width, $height, $options = [])
    {
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
        
        $croppedImage = 'cropped/' . $width . 'x' . $height . '/' . $image;
        $imageCrop->save($this->getMediaDirectory()->getAbsolutePath($croppedImage));
        
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $croppedImage;
    }
}
