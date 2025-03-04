<?php
require __DIR__ . "/../inc.php";

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\JsonFormatter;

class Logging
{
    private Logger $logger;
    private string $logPath;
    private int $maxFiles;

    /**
     * Constructor initializes the logger.
     *
     * @param string $logName Name of the log file (default: app.log).
     * @param string $logPath Path to log directory (default: logs/).
     * @param int $maxFiles Maximum days to retain logs.
     * @param int $logLevel Logging level (default: Logger::DEBUG).
     */
    public function __construct(
        string $logName = 'app.log',
        string $logPath = __DIR__ . '/../../logs/',
        int $maxFiles = 90,
        int $logLevel = Logger::DEBUG
    ) {
        $this->logPath = rtrim($logPath, '/') . '/';
        $this->maxFiles = $maxFiles;

        // Ensure log directory exists
        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0777, true);
        }

        // Initialize Logger
        $this->logger = new Logger('app_logger');

        // Add RotatingFileHandler for daily log rotation
        $handler = new RotatingFileHandler($this->logPath . $logName, $this->maxFiles, $logLevel);

        // Set JSON formatter
        $handler->setFormatter(new JsonFormatter());

        // Attach handler to logger
        $this->logger->pushHandler($handler);

        $this->registerExceptionHandler();
    }

    /**
     * Log an informational message.
     *
     * @param string $message
     * @param array $context Additional data for the log.
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Log a warning message.
     *
     * @param string $message
     * @param array $context Additional data for the log.
     */
    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Log an error message.
     *
     * @param string $message
     * @param array $context Additional data for the log.
     */
    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Log a debug message.
     *
     * @param string $message
     * @param array $context Additional data for the log.
     */
    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Log an exception.
     *
     * @param Throwable $exception
     * @return string Correlation ID for the logged exception.
     */
    public function logException(Throwable $exception): string
    {
        $correlationId = strtoupper(uniqid());
        $this->logger->error($exception->getMessage(), [
            'correlationId' => $correlationId,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        return $correlationId;
    }

    /**
     * Register global exception and error handlers.
     */
    public function registerExceptionHandler(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
    }

    /**
     * Handle uncaught exceptions.
     *
     * @param Throwable $exception
     */
    public function handleException(Throwable $exception): void
    {
        $correlationId = $this->logException($exception);
        if (Auth::is_admin()) {
            Utils::print_error($exception . "<hr><b>Correlation ID: </b> $correlationId", needs_bootstrap: true);
        } else {
            Utils::print_error("Si è verificato un errore imprevisto. Contattare l'Amministratore di Sistema.<hr><b>Correlation ID: </b> $correlationId", true);
        }
        exit;
    }

    /**
     * Convert PHP errors to exceptions.
     *
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public function handleError(int $severity, string $message, string $file, int $line): void
    {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public static function get_logger(): Logging
    {
        if(isset($GLOBALS['LOGGER'])) {
            return $GLOBALS['LOGGER'];
        }
        $GLOBALS['LOGGER'] = new Logging();
        return $GLOBALS['LOGGER'];

    }

    public function get_logs(string $date): array
    {
        $path = $this->logPath . "app-$date.log";
        if (!file_exists($path)) {
            return [];
        }
        $logs = [];

        $logs = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $decoded = json_decode($line, true);
            if ($decoded !== null) {
                $logs[] = $decoded;
            }
        }
        return $logs;
    }
}
