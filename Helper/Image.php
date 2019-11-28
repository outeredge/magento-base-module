<?php

namespace OuterEdge\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Image\Adapter\ConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
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
     * @var ConfigInterface
     */
    protected $config;

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
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        Database $coreFileStorageDatabase,
        StoreManagerInterface $storeManager,
        ConfigInterface $config
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->storeManager = $storeManager;
        $this->config = $config;
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
     * Get an image url
     * Saves file to FS if exists in database and not FS
     *
     * @param string $filename
     * @return string
     */
    public function get($filename)
    {
        $image = $this->prepareFilename($filename);
        $this->fileExists($image);
        return $this->getMediaImageUrl($image);
    }

    /**
     * Setup the image object ready for resizing/cropping
     *
     * @param string $image
     * @param int|null $width
     * @param int|null $height
     * @param array $options
     * @return \Magento\Framework\Image\Adapter\AdapterInterface
     */
    protected function setup($image, $width = null, $height = null, $options = [])
    {
        $imageResize = $this->imageFactory->create($this->config->getAdapterAlias());
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
     * @param string $filename
     * @param int|null $width
     * @param int|null $height
     * @param array $options
     * @return string
     */
    public function resize($filename, $width = null, $height = null, $options = [])
    {
        $image = $this->prepareFilename($filename);

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
     * @param string $filename
     * @param int $width
     * @param int $height
     * @param array $options
     * @return string
     */
    public function crop($filename, $width, $height, $options = [])
    {
        $image = $this->prepareFilename($filename);

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
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
     */
    public function fileExists($filename)
    {
        $filename = $this->prepareFilename($filename);

        if ($this->getMediaDirectory()->isFile($filename)) {
            return true;
        } else {
            return $this->coreFileStorageDatabase->saveFileToFilesystem(
                $this->getMediaDirectory()->getAbsolutePath($filename)
            );
        }
    }

    /**
     * Get media image front-end url
     *
     * @param string|null $filename
     * @return string
     */
    public function getMediaImageUrl($filename)
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $filename;
    }

    /**
     * Prepare filename for handling by stripping any web path (i.e. when non-relative is passed tp helper)
     *
     * @param string $urlorfilename
     * @return string relative path
     */
    protected function prepareFilename($urlorfilename)
    {
        return str_replace($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '', $urlorfilename);
    }
}
