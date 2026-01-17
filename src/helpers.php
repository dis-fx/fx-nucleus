<?php
// DIS PROPRIETARY CODE - NO THIRD-PARTY DEPENDENCIES
// © 2026 Digital Intelligence Solutions LLC, Muscat, Oman
// src/helpers.php
// DIS_LICENSE.txt HEADER REQUIRED ABOVE THIS LINE
declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * SECURITY: Escape output for HTML contexts.
     */
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('fx_abort')) {
    /**
     * SECURITY: Halt execution with HTTP status and sanitized message.
     */
    function fx_abort(int $code = 500, string $message = 'Internal Error'): void
    {
        http_response_code($code);
        exit(e($message));
    }
}

if (!function_exists('asset')) {
    /**
     * SECURITY: Return public asset path with deterministic version hash.
     */
    function asset(string $path): string
    {
        if (preg_match('/\.{2}[\\\/]/', $path)) {
            fx_abort(400, 'Invalid asset path');
        }

        $version = substr(md5_file(__FILE__), 0, 8);
        return '/public/' . ltrim($path, '/\\') . '?v=' . $version;
    }
}

if (!function_exists('env_safe')) {
    /**
     * SECURITY: Read environment variables with fallback and trimmed value.
     */
    function env_safe(string $key, $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        $value = is_string($value) ? trim($value) : $value;
        return $value === '' ? $default : $value;
    }
}

if (!function_exists('config')) {
    /**
     * SECURITY: Load config from FX_ROOT/config/app.php once with path guard.
     */
    function config(string $key, $default = null): mixed
    {
        static $config = null;

        if (!defined('FX_ROOT')) {
            fx_abort(500, 'FX_ROOT not defined');
        }

        $configPath = rtrim(FX_ROOT, '/\\') . '/config/app.php';

        if (preg_match('/\.{2}[\\\/]/', $configPath)) {
            fx_abort(500, 'Invalid config path');
        }

        if ($config === null) {
            if (!file_exists($configPath)) {
                fx_abort(500, 'Config file missing');
            }

            $config = require $configPath;

            if (!is_array($config)) {
                fx_abort(500, 'Config must return array');
            }
        }

        return $config[$key] ?? $default;
    }
}

// TODO: add router-aware helpers (route(), redirect()) once router package is ready.