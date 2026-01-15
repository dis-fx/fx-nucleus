<?php
// src/Autoloader.php
// DIS_LICENSE.txt HEADER REQUIRED ABOVE THIS LINE
namespace Dis\FictionX\Nucleus;

final class Autoloader
{
    private string $baseDir;

    public function __construct(string $baseDir = __DIR__)
    {
        $this->baseDir = rtrim($baseDir, '/\\');
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    private function loadClass(string $class): void
    {
        $prefix = 'Dis\\FictionX\\';
        
        // Only load DIS classes
        if (strpos($class, $prefix) !== 0) {
            return;
        }

        // Convert namespace to path
        $relativeClass = substr($class, strlen($prefix));
        $file = $this->baseDir . '/' . str_replace('\\', '/', $relativeClass) . '.php';

        // Security: Prevent directory traversal
        if (strpos($file, $this->baseDir) !== 0) {
            throw new \Exception("Invalid class path: {$class}");
        }

        // Only load if file exists
        if (file_exists($file)) {
            require $file;
        }
    }
}