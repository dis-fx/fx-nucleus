<?php
// src/helpers.php
// DIS_LICENSE.txt HEADER REQUIRED ABOVE THIS LINE

if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $version = substr(md5_file(__FILE__), 0, 8);
        return "/public/{$path}?v={$version}";
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null) {
        static $config = null;
        
        if ($config === null) {
            $config = require FX_ROOT . '/config/app.php';
        }
        
        return $config[$key] ?? $default;
    }
}

// Add 7 more helpers as needed (fx_abort(), route(), etc.)