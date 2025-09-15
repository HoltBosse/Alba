<?php
namespace HoltBosse\Alba\Core;

use \Exception;

Class ErrorManager {
    public static function initPhpErrorLevels() {
        if ($_ENV['debug'] === 'true') {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            if($_ENV['debugwarnings'] === 'true') {
                error_reporting(E_ALL);
            } else {
                error_reporting(E_ERROR);
            }
        }
    }

    public static function exceptionHandler($e) {
        http_response_code(500);
        echo "<div style='
            background: #ffeeee;
            border: 1px solid #cc0000;
            padding: 16px;
            font-family: monospace;
            color: #333;
            margin:16px auto;
            border-radius:8px;
        '>";
            echo "<h3 style='margin-top:0;color:#cc0000;'>Uncaught Exception</h3>";
            echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "<strong>Code:</strong> " . $e->getCode() . "<br>";
            echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . "<br>";
            echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
            echo "<details style='margin-top:8px;'><summary>Stack Trace</summary>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</details>";
        echo "</div>";
    }

    public static function generateNiceException(Exception $e): string {
        $data = $e->getMessage() . '|' . $e->getLine() . '|' . $e->getFile();
        $compressed = gzcompress($data, 9);
        $base64 = base64_encode($compressed);
        $base64 = rtrim(strtr($base64, '+/', '-_'), '=');
        return 'E_' . $base64;
    }

    public static function decodeNiceException(string $errorCode): ?array {
        if (strpos($errorCode, 'E_') !== 0) return null;
        $base64 = substr($errorCode, 2);
        // Pad base64 if necessary
        $pad = strlen($base64) % 4;
        if ($pad > 0) {
            $base64 .= str_repeat('=', 4 - $pad);
        }
        $compressed = base64_decode(strtr($base64, '-_', '+/'));
        //@phpstan-ignore-next-line
        if ($compressed === false) return null;
        $data = gzuncompress($compressed);
        if ($data === false) return null;
        $parts = explode('|', $data, 3);
        if (count($parts) !== 3) return null;
        return [
            'message' => $parts[0],
            'line'    => $parts[1],
            'file'    => $parts[2],
        ];
    }
}