<?php

namespace OuterEdge\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends AbstractHelper
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->_assetRepo = $assetRepository;
        $this->_directoryList = $directoryList;
    }

    public function getSvg($name)
    {
        $image = $this->_directoryList->getPath(DirectoryList::STATIC_VIEW) . '/' . $this->_assetRepo->getStaticViewFileContext()->getPath() . '/' . $name;
        if (file_exists($image)) {
            return file_get_contents($image);
        }
        return '';
    }
}