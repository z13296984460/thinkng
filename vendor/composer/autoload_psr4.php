<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return array(
    'think\\trace\\' => array($vendorDir . '/topthink/think-trace/src'),
    'think\\app\\' => array($vendorDir . '/topthink/think-multi-app/src'),
    'think\\' => array($vendorDir . '/topthink/framework/src/think', $vendorDir . '/topthink/think-helper/src', $vendorDir . '/topthink/think-orm/src'),
    'app\\' => array($baseDir . '/app'),
    'Symfony\\Polyfill\\Php80\\' => array($vendorDir . '/symfony/polyfill-php80'),
    'Symfony\\Polyfill\\Php72\\' => array($vendorDir . '/symfony/polyfill-php72'),
    'Symfony\\Polyfill\\Mbstring\\' => array($vendorDir . '/symfony/polyfill-mbstring'),
    'Symfony\\Component\\VarDumper\\' => array($vendorDir . '/symfony/var-dumper'),
    'Psr\\SimpleCache\\' => array($vendorDir . '/psr/simple-cache/src'),
    'Psr\\Log\\' => array($vendorDir . '/psr/log/Psr/Log'),
    'Psr\\Container\\' => array($vendorDir . '/psr/container/src'),
    'Psr\\Cache\\' => array($vendorDir . '/psr/cache/src'),
    'League\\Flysystem\\Cached\\' => array($vendorDir . '/league/flysystem-cached-adapter/src'),
    'League\\Flysystem\\' => array($vendorDir . '/league/flysystem/src'),
);
