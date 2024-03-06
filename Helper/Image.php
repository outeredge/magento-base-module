<?php

namespace OuterEdge\Base\Helper;

use Magento\Catalog\Model\Product\Image as ProductImage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Image\Adapter\ConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;

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
     * @var SynchronizationFactory
     */
    protected $synchronizationFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Write
     */
    protected $mediaDirectory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param SynchronizationFactory $synchronizationFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        SynchronizationFactory $synchronizationFactory,
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->synchronizationFactory = $synchronizationFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return Write
     */
    protected function getMediaDirectory()
    {
        if (!$this->mediaDirectory) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
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
        $imageAdapter = $this->imageFactory->create($this->config->getAdapterAlias());
        $imageAdapter->open($this->getMediaDirectory()->getAbsolutePath($image));

        $options = array_merge([
            'quality' => (int) $this->scopeConfig->getValue(ProductImage::XML_PATH_JPEG_QUALITY)
        ], $options);

        foreach ($options as $method => $value) {
            $imageAdapter->$method($value);
        }

        return $imageAdapter;
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
                $cropHorizontal = ceil(($cropWidth - $width) / 2);
                $imageCrop->resize($cropWidth, $height);
                $imageCrop->crop(0, $cropHorizontal, $cropHorizontal, 0);
            } else {
                $cropHeight = ceil($width / $originalAspectRatio);
                $cropVertical = ceil(($cropHeight - $height) / 2);
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
    public function fileExists($filename, $sync = true)
    {
        $filename = $this->prepareFilename($filename);

        if ($this->getMediaDirectory()->isFile($filename)) {
            return true;
        } elseif ($sync) {
            $this->synchronizationFactory->create(['directory' => $this->getMediaDirectory()])->synchronize($filename);
            return $this->fileExists($filename, false);
        }

        return false;
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
    public function prepareFilename($urlorfilename)
    {
        if (!$urlorfilename) {
            return '';
        }

        $mediaUrlBase = $this->storeManager->getStore(0)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $mediaUrl     = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $mediaPath    = DIRECTORY_SEPARATOR . basename($mediaUrl) . DIRECTORY_SEPARATOR;

        // remove any double slashes (except for ://)
        $urlorfilename = str_replace(':/','://', trim(preg_replace('/\/+/', '/', $urlorfilename), '/'));

        if (strpos($urlorfilename, '://') !== false) {
            if (strpos($urlorfilename, $mediaUrl) !== false) {
                // strip the known Magento media URL from the filename
                $urlorfilename = str_ireplace($mediaUrl, '', $urlorfilename);
            } elseif (strpos($urlorfilename, $mediaUrlBase) !== false) {
                // strip the default URL from the filename
                $urlorfilename = str_ireplace($mediaUrlBase, '', $urlorfilename);
            } elseif (strpos($urlorfilename, $mediaPath) !== false) {
                // trim everything up to and including the media URL's final path
                $return = stristr($urlorfilename, $mediaPath);
                if (0 === strpos($return, $mediaPath)) {
                    $return = substr($return, strlen($mediaPath));
                }
                return $return;
            } elseif (strpos($urlorfilename, '/media/') !== false) {
                // trim everything up to and including /media/
                $return = stristr($urlorfilename, '/media/');
                if (0 === strpos($return, '/media/')) {
                    $return = substr($return, strlen('/media/'));
                }
                return $return;
            }
        }

        // trim the known media path from whatever is left
        if (strpos($urlorfilename, $mediaPath) !== false) {
            $urlorfilename = str_ireplace($mediaPath, DIRECTORY_SEPARATOR, strstr($urlorfilename, $mediaPath));
        }

        if (strpos($urlorfilename, 'media' . DIRECTORY_SEPARATOR) !== false) {
            $urlorfilename = str_ireplace(
                'media' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR,
                strstr($urlorfilename,  'media' . DIRECTORY_SEPARATOR)
            );
        }

        $urlorfilename = ltrim($urlorfilename, DIRECTORY_SEPARATOR);

        return $urlorfilename;
    }
}
