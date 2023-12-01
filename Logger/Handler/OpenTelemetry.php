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
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LogRecord;
use Opentelemetry\Proto\Logs\V1\SeverityNumber;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

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
        $this->init();
        parent::__construct($filesystem, $filePath);
    }

    public function init()
    {
        putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
        putenv('OTEL_METRICS_EXPORTER=none');
        putenv('OTEL_LOGS_EXPORTER=otlp');
        putenv('OTEL_LOGS_PROCESSOR=batch');
        putenv('OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf');
        putenv('OTEL_EXPORTER_OTLP_ENDPOINT=https://otlp.eu01.nr-data.net:4318');

        //putenv('NEW_RELIC_INSERT_KEY=c15a6e66c1b2f809818332252c1e2ca67d23af99');
        //putenv('NEW_RELIC_ENDPOINT=https://otlp.eu01.nr-data.net:4318');
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
        ]);



        print_r($logger); die();




        $loggerProvider = Globals::loggerProvider();

        $licenseKey = getenv('NEW_RELIC_INSERT_KEY');

        $endpointUrl =  getenv('NEW_RELIC_ENDPOINT');

        // Needs a license key in the environment to connect to the backend server.

        if ($licenseKey == false || $endpointUrl == false) {
            echo PHP_EOL . 'KEY or ENDPOINT not found in environment';
            return;
        }

        print_r($loggerProvider);
        die();

        print_r($loggerProvider);
        die('ok');
        $handler = new Handler(
            $loggerProvider,
            LogLevel::INFO
        );

        /*$handler = new Handler(
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

        /*$loggerProvider = new OpenTelemetry\SDK\Logs\LoggerProvider($record['context']['exception']);
            $handler = new \OpenTelemetry\Contrib\Logs\Monolog\Handler(
                $loggerProvider,
                'info',
                true,
            );*/

            //$this->exceptionHandler->handle($record);

            return;
        }
        //$record['formatted'] = $this->getFormatter()->format($record);

        //parent::write($record);
    }
}
