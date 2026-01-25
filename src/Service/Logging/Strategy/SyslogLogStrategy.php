<?php

namespace App\Service\Logging\Strategy;

use App\ValueObject\LoggingConfig;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\SyslogHandler;

class SyslogLogStrategy extends AbstractLogStrategy
{
    public function createHandler(LoggingConfig $config, string $channelName): HandlerInterface
    {
        return new SyslogHandler('api_gateway', LOG_USER, $this->getLogLevel($config->level));
    }

    public function getType(): string
    {
        return 'syslog';
    }
}
