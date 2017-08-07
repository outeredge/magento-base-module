<?php

namespace OuterEdge\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;
use DOMDocument;

class Image extends AbstractHelper
{
    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @param Context $context
     * @param Repository $assetRepository
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Context $context,
        Repository $assetRepository,
        DirectoryList $directoryList
    ) {
        $this->_assetRepo = $assetRepository;
        $this->_directoryList = $directoryList;
        parent::__construct($context);
    }

    public function getSvg($name)
    {
        $image = $this->_directoryList->getPath(DirectoryList::STATIC_VIEW) . '/' . $this->_assetRepo->getStaticViewFileContext()->getPath() . '/' . $name;
        $imageInfo = pathinfo($image);
        $fileExists = new \Zend_Validate_File_Exists($imageInfo['dirname']);
        if ($fileExists->isValid($imageInfo['basename'])) {
            $doc = new DOMDocument();
            $doc->load($image);
            return $doc->saveHTML();
        }
        return '';
    }
}
