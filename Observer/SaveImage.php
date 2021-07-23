<?php

namespace OuterEdge\Base\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

class SaveImage implements ObserverInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        ManagerInterface $messageManager
    ) {
        $this->pageRepository = $pageRepository;
        $this->messageManager = $messageManager;
    }

	public function execute(Observer $observer)
	{
		$model = $observer->getData('page');
        $request = $observer->getData('request');
        $data = $request->getPostValue();

        // Add custom image field to data
        if (isset($data['banner_image']) && is_array($data['banner_image'])){
            $data['banner_image'] = $data['banner_image'][0]['name'];
        
            $model->setData($data);

            try {
                $this->pageRepository->save($model);
            } catch (\Throwable $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }
        }

		return $this;
	}
}
