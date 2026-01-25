<?php

namespace App\Service\Logging\Strategy;

use App\ValueObject\LoggingConfig;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;

class FileLogStrategy extends AbstractLogStrategy
{
    public function createHandler(LoggingConfig $config, string $channelName): HandlerInterface
    {
        $filePath = sprintf('%s/%s.log', $this->getLogDir(), $channelName);
        return new StreamHandler($filePath, $this->getLogLevel($config->level));
    }

    public function getType(): string
    {
        return 'file';
    }

    protected function getLogDir(): string
    {
        return $_ENV['KERNEL_LOGS_DIR'] ?? dirname(__DIR__, 4) . '/var/log';
    }
}
