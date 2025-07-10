<?php
namespace HoltBosse\Alba;

Class DotEnv {
    protected $path;

    public function __construct($path) {
        if (!file_exists($path) || !is_readable($path)) {
            throw new InvalidArgumentException("Cannot read .env file at $path");
        }

        $this->path = $path;
    }

    public function load() {
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Remove comments (start with # or ;)
            if (preg_match('/^\s*([#;])/', $line)) {
                continue;
            }

            // Parse key=value
            if (preg_match('/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)\s*$/', $line, $matches)) {
                $key = $matches[1];
                $value = $matches[2];

                // Remove optional surrounding quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $_ENV[$key] = $value;
            }
        }
    }
}