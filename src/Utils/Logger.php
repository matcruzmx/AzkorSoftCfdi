<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Utils;

final class Logger
{
    private static string $logPath = __DIR__ . '/../../../logs/cfdi.log';

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $date = date('Y-m-d H:i:s');
        $contextStr = $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$date}] {$level}: {$message} {$contextStr}\n";

        @file_put_contents(self::$logPath, $line, FILE_APPEND);
    }
}
