<?php

namespace App\Service\Logging\Strategy;

use App\ValueObject\LoggingConfig;
use Monolog\Handler\HandlerInterface;

interface LogStrategyInterface
{
    public function createHandler(LoggingConfig $config, string $channelName): HandlerInterface;

    public function getType(): string;
}
