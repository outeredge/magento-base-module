<?php

namespace OuterEdge\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\File;

class Asset extends AbstractHelper
{
    /**
     * @var Repository
     */
    private $assetRepository;

    /*
     * @var array
     */
    private $assetCache = [];

    /**
     * @param Context $context
     * @param Repository $assetRepository
     */
    public function __construct(
        Context $context,
        Repository $assetRepository
    ) {
        $this->assetRepository = $assetRepository;
        parent::__construct($context);
    }

    /**
     * Return asset file object
     *
     * @param string $asset
     * @return File
     */
    public function getAsset($asset)
    {
        if (!isset($this->assetCache[$asset])) {
            $this->assetCache[$asset] = $this->assetRepository->createAsset($asset);
        }
        return $this->assetCache[$asset];
    }

    /**
     * Get the url for an asset file
     *
     * @param string $asset
     * @return string
     */
    public function getAssetUrl($asset)
    {
        return $this->getAsset($asset)->getUrl();
    }

    /**
     * Get the content for an asset file
     *
     * @param string $asset
     * @return string
     */
    public function getAssetContent($asset)
    {
        return $this->getAsset($asset)->getContent();
    }
}
