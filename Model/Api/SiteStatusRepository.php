<?php

namespace OuterEdge\Base\Model\Api;

use OuterEdge\Base\Api\Data\FindersInterfaceFactory;
use OuterEdge\Base\Api\Data\FindersInterface;
use OuterEdge\Base\Model\FindersRepository;
use OuterEdge\Base\Api\Data\QuestionsInterfaceFactory;
use OuterEdge\Base\Api\Data\QuestionsInterface;
use OuterEdge\Base\Model\QuestionsRepository;
use OuterEdge\Base\Api\SiteStatusRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use OuterEdge\Base\Helper\UrlGenerator;
use OuterEdge\Base\Helper\SendEmail;
use Magento\Newsletter\Model\SubscriberFactory;

class SiteStatusRepository implements SiteStatusRepositoryInterface
{
    public function __construct(
        protected FindersRepository $findersRepository,
        protected FindersInterfaceFactory $findersInterface,
        protected QuestionsRepository $questionsRepository,
        protected QuestionsInterfaceFactory $questionsInterface,
        protected UrlGenerator $urlGeneratorHelper,
        protected SendEmail $sendEmailHelper,
        protected DataObjectHelper $dataObjectHelper,
        protected RestRequest $request,
        protected SubscriberFactory $subscriberFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getIndexer()
    {
        try {
            $finder = $this->findersRepository->getById($id);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        if (!$finder->getIsActive()) {
            return json_encode(['success' => false, 'message' => 'Finder is disabled']);
        }

        return $this->getFinderApiResponse($finder);
    }

    private function getFinderApiResponse(FindersInterface $finder) : mixed
    {
        try {
            $questions = $this->questionsRepository
                ->getAllQuestionsAndAnswersByFinderId($finder->getFinderId());

            /** @var FindersInterfaceFactory $responseItem */
            $finderDataObject = $this->findersInterface->create();
            $this->dataObjectHelper->populateWithArray(
                $finderDataObject,
                $finder->getData(),
                \OuterEdge\ProductFinder\Api\Data\FindersInterface::class
            );

            $finderDataObject->setQuestions($questions);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        return json_encode(['success' => true, 'message' => $finderDataObject->getData()]);
    }

    /**
     * @inheritdoc
     */
    public function getConfigs()
    {
        $data = $this->request->getBodyParams();

        if (!isset($data['answers']) || !isset($data['details'])) {
            return json_encode(['success' => false, 'message' => 'Missing body data']);
        }

        try {
            //Generate URL
            $url = $this->urlGeneratorHelper->getUrl($data['answers']);

            if (isset($data['return_url'])) {
                $url .= '&return_url='.$data['return_url'];
            }

            if ($url) {

                $finder = $this->findersRepository->getById($data['details']['finder_id']);
                if ($finder->getAdminEmail()) {
                    //Send Email
                    $this->sendEmailHelper->send($data['details'], $url);
                }

                if ($finder->getSubscribeToNewsletter()) {
                    //Subscribe to newsletter
                }
            }
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        return json_encode(['success' => true, 'message' => ["url" => $url]]);
    }

}
