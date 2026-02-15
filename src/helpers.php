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
        if (preg_match('#\.\.[/\\\\]#', $path)) {
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
        static $loaded = false;

        // Lazy load .env once if FX_ROOT is defined
        if (!$loaded && defined('FX_ROOT')) {
            $envPath = rtrim(FX_ROOT, '/\\') . '/.env';

            // Simple traversal guard
            if (strpos($envPath, '..') !== false) {
                fx_abort(500, 'Invalid env path');
            }

            if (is_file($envPath)) {
                $vars = parse_ini_file($envPath, false, INI_SCANNER_RAW);
                if ($vars !== false) {
                    foreach ($vars as $k => $v) {
                        $k = trim((string)$k);
                        if ($k === '') {
                            continue;
                        }
                        $v = is_string($v) ? trim($v) : $v;
                        putenv($k . '=' . $v);
                        $_ENV[$k] = $v;
                        $_SERVER[$k] = $v;
                    }
                }
            }

            $loaded = true;
        }

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

        if (preg_match('#\.\.[/\\\\]#', $configPath)) {
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

if (!function_exists('route')) {
    /**
     * SECURITY: Build a safe route URL by replacing {param} tokens and rejecting traversal.
     */
    function route(string $path, array $params = []): string
    {
        if (preg_match('#\.{2}[\\/]#', $path)) {
            fx_abort(400, 'Invalid route path');
        }

        $resolved = $path;
        foreach ($params as $key => $value) {
            $token = '{' . $key . '}';
            if (str_contains($resolved, $token)) {
                $resolved = str_replace($token, rawurlencode((string)$value), $resolved);
                unset($params[$key]);
            }
        }

        // Append remaining params as query string
        if (!empty($params)) {
            $resolved .= (str_contains($resolved, '?') ? '&' : '?') . http_build_query($params);
        }

        // Normalize leading slash
        return '/' . ltrim(preg_replace('#/+#', '/', $resolved) ?: '/', '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * SECURITY: Redirect to a safe route; uses fx-router Response if available.
     */
    function redirect(string $path, int $status = 302, array $params = [])
    {
        $url = route($path, $params);

        // If fx-router Response exists, return it to allow caller to send later.
        if (class_exists('Dis\\FictionX\\Router\\Response')) {
            /** @var class-string $respClass */
            $respClass = 'Dis\\FictionX\\Router\\Response';
            return $respClass::text('', $status, ['Location' => $url]);
        }

        // Fallback: immediate header emit and exit.
        header('Location: ' . $url, true, $status);
        exit;
    }
}