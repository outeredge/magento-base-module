<?php

namespace OuterEdge\Base\Controller\Adminhtml\Promo\Catalog;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterfaceFactory;
use Magento\CatalogRule\Model\Flag;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Mass Duplicate catalog price rules
 */
class MassDuplicate extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Magento_CatalogRule::promo_catalog';

    public function __construct(
        Context $context,
        protected CollectionFactory $collectionFactory,
        protected CatalogRuleRepositoryInterface $ruleRepository,
        protected RuleInterfaceFactory $ruleFactory,
        protected Flag $flag
    ) {
        parent::__construct($context);
    }

    /**
     * Execute mass duplicate action
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
                $duplicatedCount = 0;

                foreach ($collection as $rule) {
                    try {
                        // Create a new rule instance
                        $newRule = $this->ruleFactory->create();

                        // Copy all data from the original rule
                        $data = $rule->getData();

                        // Remove the rule_id to create a new rule
                        unset($data['rule_id']);

                        // Modify the name to indicate it's a duplicate
                        $data['name'] = $data['name'] . ' (Copy)';

                        // Set the rule as inactive by default
                        $data['is_active'] = 0;

                        // Set the data to the new rule
                        $newRule->setData($data);

                        // Copy conditions and actions
                        if ($rule->getConditions()) {
                            $newRule->getConditions()->loadArray($rule->getConditions()->asArray());
                        }

                        // Save the new rule
                        $this->ruleRepository->save($newRule);
                        $duplicatedCount++;
                    } catch (LocalizedException $e) {
                        $this->messageManager->addErrorMessage(
                            __('Error duplicating rule %1: %2', $rule->getName(), $e->getMessage())
                        );
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(
                            __('Error duplicating rule %1: %2', $rule->getName(), $e->getMessage())
                        );
                    }
                }

                if ($duplicatedCount > 0) {
                    $this->flag->loadSelf()->setState(1)->save();
                    $this->messageManager->addSuccessMessage(
                        __('A total of %1 record(s) have been duplicated.', $duplicatedCount)
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while duplicating the rules.')
                );
            }
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog_rule/promo_catalog/index');
    }
}
