<?php

declare(strict_types=1);

namespace Utils;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Log
{
  private static LoggerInterface $logger;
  private static string $loggerName;
  private static string $directory;

  public static function setDefaultInstance(array $options = []): LoggerInterface
  {
    self::$loggerName = $options['log_name'] ?? 'info';
    self::$directory = $options['log_path'] ?? __DIR__;
    return self::getLogger();
  }

  public static function getLogger(): LoggerInterface
  {
    return self::$logger ?? self::$logger = self::createDefaultLogger();
  }

  private static function createDefaultLogger(): Logger
  {
    $logger = new Logger(self::$loggerName);
    $output = "[%datetime%] [%level_name%]%message%\n";
    $formatter = new LineFormatter($output);
    $filename = self::$directory . '/log.log';
    $infoStreamHandler = new RotatingFileHandler($filename, 0, Logger::INFO, true, 0775);
    $infoStreamHandler->setFormatter($formatter);
    $logger->pushHandler($infoStreamHandler);
    return $logger;
  }

  /** @deprecated don't create object from logger */
  private function __construct()
  {
  }

  private static function format(string $facility, string $message): string
  {
    return '[' . $facility . '] ' . $message;
  }

  public static function debug(string $facility, string $message): void
  {
    self::getLogger()->debug(self::format($facility, $message));
  }

  public static function info(string $facility, string $message): void
  {
    self::getLogger()->info(self::format($facility, $message));
  }

  private static function writeErr(string $message, $exception = null): string
  {
    if ($exception instanceof \Throwable)
    {
      $message .= ' ' . (string)($exception->getMessage());
    }
    elseif ($exception !== null)
    {
      $message .= " {$exception}";
    }
    return $message;
  }

  public static function error(string $facility, string $message, $exception = null): void
  {
    $message = self::writeErr($message, $exception);
    self::getLogger()->error(self::format($facility, $message));
  }

  public static function warn(string $facility, string $message, $exception = null): void
  {
    $message = self::writeErr($message, $exception);
    self::getLogger()->warning(self::format($facility, $message));
  }
}