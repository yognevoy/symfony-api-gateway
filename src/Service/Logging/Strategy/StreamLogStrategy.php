<?php

namespace App\Service\Logging\Strategy;

use App\ValueObject\LoggingConfig;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;

class StreamLogStrategy extends AbstractLogStrategy
{
    public function createHandler(LoggingConfig $config, string $channelName): HandlerInterface
    {
        return new StreamHandler('php://stdout', $this->getLogLevel($config->level));
    }

    public function getType(): string
    {
        return 'stream';
    }
}
