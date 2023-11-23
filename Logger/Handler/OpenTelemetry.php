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
        putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
        putenv('OTEL_METRICS_EXPORTER=none');
        putenv('OTEL_LOGS_EXPORTER=otlp');
        putenv('OTEL_LOGS_PROCESSOR=batch');
        putenv('OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf');
        putenv('OTEL_EXPORTER_OTLP_ENDPOINT=https://otlp.eu01.nr-data.net:4318');
        putenv('OTEL_EXPORTER_OTLP_CLIENT_KEY=NRAK-7ZX2SPADU1KU6LNYYDYP0ZZWKOK');
        putenv('OTEL_EXPORTER_OTLP_CLIENT_KEY_ID=1EA8E3120801608EC01E11BBDD7A0821D77A35E315B1206A7CB57B1DD29D3199');

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
