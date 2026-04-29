<?php
namespace OuterEdge\Base\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\State;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\App\ResourceConnection;

class ConvertQuoteCommand extends Command
{
    const QUOTE_ID_ARGUMENT = 'quoteId';

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param State $appState
     * @param CartRepositoryInterface $quoteRepository
     * @param CartManagementInterface $quoteManagement
     * @param ResourceConnection $resource
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        CartRepositoryInterface $quoteRepository,
        CartManagementInterface $quoteManagement,
        ResourceConnection $resource,
        string $name = null
    ) {
        $this->appState = $appState;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->resource = $resource;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('outeredge:convert-quote')
            ->setDescription('Convert a stuck quote into an order')
            ->addArgument(
                self::QUOTE_ID_ARGUMENT,
                InputArgument::REQUIRED,
                'Quote ID to convert'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Area code might already be set
        }

        $output->writeln("--- Starting Quote Recovery ---");

        $quoteId = $input->getArgument(self::QUOTE_ID_ARGUMENT);

        try {
            $quote = $this->quoteRepository->get($quoteId);
            $output->writeln("Found Quote ID: {$quote->getId()}");

            if (!$quote->getIsActive()) {
                $quote->setIsActive(true);
                $this->quoteRepository->save($quote);
            }

            // Save the original payment state just in case we need to revert it
            $originalPayment = $quote->getPayment();
            $originalMethod = $originalPayment->getMethod();

            if (!$originalMethod) {
                $originalMethod = 'checkmo'; // Fallback if the quote had absolutely no method
                $quote->getPayment()->setMethod($originalMethod);
                $this->quoteRepository->save($quote);
            }

            $orderId = null;
            $methodSwapped = false;

            $output->writeln("Attempting to place order with original method: '{$originalMethod}'...");

            try {
                // First Attempt: Try to place it with the original gateway
                $orderId = $this->quoteManagement->placeOrder($quoteId);
            } catch (\Exception $e) {
                $output->writeln("");
                $output->writeln("<error>[!] Validation Error Caught: " . $e->getMessage() . "</error>");
                $output->writeln("<comment>[!] Swapping to 'checkmo' to bypass gateway validation...</comment>");

                // Strip out gateway data and apply offline method
                $quote->getPayment()->setMethod('checkmo');
                $quote->getPayment()->setAdditionalInformation([]);
                $this->quoteRepository->save($quote);

                // Second Attempt: Force it through
                $orderId = $this->quoteManagement->placeOrder($quoteId);
                $methodSwapped = true;
            }

            if ($orderId) {
                $output->writeln("");
                $output->writeln("<info>SUCCESS! Order created safely. Entity ID: " . $orderId . "</info>");

                // If we had to use the fallback, surgically update the database
                if ($methodSwapped && $originalMethod !== 'checkmo') {
                    $output->writeln("Restoring original payment method '{$originalMethod}' via direct database update...");

                    $connection = $this->resource->getConnection();
                    $tableName = $this->resource->getTableName('sales_order_payment');

                    // Raw SQL Update: UPDATE sales_order_payment SET method = 'original_method' WHERE parent_id = X
                    $binds = ['method' => $originalMethod];
                    $where = ['parent_id = ?' => (int)$orderId];

                    $connection->update($tableName, $binds, $where);

                    $output->writeln("Done. Order now reflects '{$originalMethod}' in the database.");
                }
            } else {
                $output->writeln("<error>FAILED: Order could not be created.</error>");
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

        } catch (\Exception $e) {
            $output->writeln("");
            $output->writeln("<error>FATAL ERROR: " . $e->getMessage() . "</error>");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
