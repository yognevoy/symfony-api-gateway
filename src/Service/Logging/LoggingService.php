<?php

namespace App\Service\Logging;

use App\Service\Logging\Strategy\LogStrategyInterface;
use App\ValueObject\LoggingConfig;
use Monolog\Logger;

class LoggingService
{
    private array $strategies;

    public function __construct(iterable $strategies)
    {
        $this->strategies = [];
        /** @var LogStrategyInterface $strategy */
        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->getType()] = $strategy;
        }
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

        $strategy = $this->strategies[$config->type] ?? $this->strategies['stream'];
        $handler = $strategy->createHandler($config, $channelName);

        $logger->pushHandler($handler);

        return $logger;
    }
}
