<?php
namespace HoltBosse\Alba\Core;

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
}