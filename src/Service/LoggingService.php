<?php

namespace App\Service;

use App\ValueObject\LoggingConfig;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Level;
use Monolog\Logger;

class LoggingService
{
    public function __construct()
    {
    }

    /**
     * Create a logger instance based on the logging configuration.
     */
    public function getLogger(LoggingConfig $config, string $channelName = 'api_gateway'): Logger
    {
        if (!$config->enabled) {
            // Return a logger that doesn't actually log anything
            $logger = new Logger($channelName);
            return $logger;
        }

        $logger = new Logger($channelName);

        switch ($config->type) {
            case 'stream':
                $handler = new StreamHandler('php://stdout', $this->getLogLevel($config->level));
                break;

            case 'syslog':
                $handler = new SyslogHandler('api_gateway', LOG_USER, $this->getLogLevel($config->level));
                break;

            case 'file':
                $filePath = sprintf('%s/%s.log', $this->getLogDir(), $channelName);
                $handler = new StreamHandler($filePath, $this->getLogLevel($config->level));
                break;

            default:
                $handler = new StreamHandler('php://stdout', $this->getLogLevel($config->level));
        }

        $logger->pushHandler($handler);

        return $logger;
    }

    protected function getLogLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'emergency' => Level::Emergency,
            'alert' => Level::Alert,
            'critical' => Level::Critical,
            'error' => Level::Error,
            'warning' => Level::Warning,
            'notice' => Level::Notice,
            'info' => Level::Info,
            'debug' => Level::Debug,
            default => Level::Info,
        };
    }

    protected function getLogDir(): string
    {
        return $_ENV['KERNEL_LOGS_DIR'] ?? (dirname(__DIR__, 2) . '/var/log');
    }
}
