<?php

namespace OuterEdge\Base\Logger\Handler;

use Exception;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Exception as ExceptionHandler;
use Magento\Framework\Logger\Handler\Base;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use Monolog\Level;
use Monolog\Logger;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use Psr\Log\LogLevel;
use OpenTelemetry\API\Globals;

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
        $handler = new Handler(
            Globals::loggerProvider(),
            LogLevel::INFO,
            true,
        );
        $logger = new Logger(
            'example',
            [$handler],
        );

        $logger->info('Bruno Test');

        /*$handler = new Handler(
            OpenTelemetry\API\Globals::loggerProvider(),
            LogLevel::INFO, //or `Logger::INFO`, or `Level::Info` depending on monolog version
            true,
        );
        $logger = new Logger(
            'example',
            [$handler],
        );

        $logger->info('hello, otel');
        $logger->error('something went wrong', [
            'foo' => 'bar',
            'exception' => new Exception('something went wrong', 500, new Exception('the first exception', 99)),
        ]);*/



        //print_r($record); die();

        if (isset($record['context']['exception'])) {

        $loggerProvider = new OpenTelemetry\SDK\Logs\LoggerProvider($record['context']['exception']);
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
