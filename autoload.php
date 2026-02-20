<?php
if (file_exists(__DIR__ . '/../../../autoload.php')) { 
    require __DIR__ . '/../../../autoload.php';
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $prefix = "FormGenerator\\";
    $baseDir = __DIR__ . "/src/";

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";

    if (file_exists($file)) {
        require $file;
    }
});
