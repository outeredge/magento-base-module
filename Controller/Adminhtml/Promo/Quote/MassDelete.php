<?php

namespace OuterEdge\Base\Controller\Adminhtml\Promo\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Mass Delete sales rules
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::promo_quote';

    public function __construct(
        Context $context,
        protected CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Execute mass delete action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $ruleIds = $this->getRequest()->getParam('rule_id');

        if (!is_array($ruleIds)) {
            $this->messageManager->addErrorMessage(__('Please select rule(s).'));
        } else {
            try {
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('rule_id', ['in' => $ruleIds]);
                $deletedCount = 0;

                foreach ($collection as $rule) {
                    try {
                        $rule->delete();
                        $deletedCount++;
                    } catch (LocalizedException $e) {
                        $this->messageManager->addErrorMessage(
                            __('Error deleting rule %1: %2', $rule->getName(), $e->getMessage())
                        );
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(
                            __('Error deleting rule %1: %2', $rule->getName(), $e->getMessage())
                        );
                    }
                }

                if ($deletedCount > 0) {
                    $this->messageManager->addSuccessMessage(
                        __('A total of %1 record(s) have been deleted.', $deletedCount)
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while deleting the rules.')
                );
            }
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales_rule/promo_quote/index');
    }
}