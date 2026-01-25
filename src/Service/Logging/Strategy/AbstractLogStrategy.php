<?php

namespace App\Service\Logging\Strategy;

abstract class AbstractLogStrategy implements LogStrategyInterface
{
    protected function getLogLevel(string $level): \Monolog\Level
    {
        return match (strtolower($level)) {
            'emergency' => \Monolog\Level::Emergency,
            'alert' => \Monolog\Level::Alert,
            'critical' => \Monolog\Level::Critical,
            'error' => \Monolog\Level::Error,
            'warning' => \Monolog\Level::Warning,
            'notice' => \Monolog\Level::Notice,
            'info' => \Monolog\Level::Info,
            'debug' => \Monolog\Level::Debug,
            default => \Monolog\Level::Info,
        };
    }
}
