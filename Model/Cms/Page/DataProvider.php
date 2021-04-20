<?php

namespace OuterEdge\Base\Model\Cms\Page;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    private $loadedData;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    public $collection;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();

            if ($model->getBannerImage()) {
                $m['banner_image'][0]['name'] = $model->getBannerImage();
                $m['banner_image'][0]['url'] = $this->getMediaUrl().$model->getBannerImage();
                $fullData = $this->loadedData;
                $this->loadedData[$model->getId()] = array_merge($fullData[$model->getId()], $m);
            }
        }

        $data = $this->dataPersistor->get('banner_image');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('banner_image');
        }

        return $this->loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'cms/bannerimage/tmp/';
        return $mediaUrl;
    }
}
