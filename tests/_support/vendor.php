<?php

$autoloadPaths = [
    dirname(__DIR__, 2) . '/vendor/autoload.php',
    dirname(__DIR__, 5) . '/vendor/autoload.php',
];

foreach ($autoloadPaths as $autoloadFile) {
    if (is_file($autoloadFile)) {
        require_once $autoloadFile;

        spl_autoload_register(static function (string $class): void {
            $prefix = 'andmemasin\\emailsvalidator\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $file = dirname(__DIR__, 2) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });

        return $autoloadFile;
    }
}

throw new RuntimeException('Unable to locate Composer autoload.php for emails validator tests.');
