<?php

if (!dl('php_azure')) {
    echo 'Failed to load php_azure.dll.'.PHP_EOL;

    exit(1);
}

$func = function($from, $to) {
    ob_start();
    include $from;
    $value = ob_get_contents();
    ob_end_clean();

    file_put_contents($to, $value);
};

$func(__DIR__.'/OpenPNE.yml', __DIR__.'/../../config/OpenPNE.yml');
$func(__DIR__.'/databases.yml', __DIR__.'/../../config/databases.yml');
