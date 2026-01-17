<?php
define('FX_ROOT', __DIR__);
require __DIR__.'/vendor/autoload.php';

$loader = new \Dis\FictionX\Nucleus\Autoloader();
$loader->register();
echo "Sanitized output: " . e("<script>alert('Muscat')</script>");
