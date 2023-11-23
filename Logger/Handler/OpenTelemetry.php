<?php

namespace OuterEdge\Base\Logger\Handler;

use Exception;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Exception as ExceptionHandler;
use Magento\Framework\Logger\Handler\Base;

class OpenTelemetry extends Base
{
    /**
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * @param DriverInterface $filesystem
     * @param ExceptionHandler $exceptionHandler
     * @param string|null $filePath
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        ExceptionHandler $exceptionHandler,
        ?string $filePath = null
    ) {
        $this->exceptionHandler = $exceptionHandler;
        parent::__construct($filesystem, $filePath);
    }

    /**
     * Writes formatted record through the handler
     *
     * @param array $record The record metadata
     * @return void
     */
    public function write(array $record): void
    {

        //print_r($record); die();

        if (isset($record['context']['exception'])) {

        $loggerProvider = new \OpenTelemetry\SDK\Logs\LoggerProvider($record['context']['exception']);
            $handler = new \OpenTelemetry\Contrib\Logs\Monolog\Handler(
                $loggerProvider,
                'info',
                true,
            );

            //$this->exceptionHandler->handle($record);

            return;
        }
        //$record['formatted'] = $this->getFormatter()->format($record);

        //parent::write($record);
    }
}
