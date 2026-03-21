<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Liszted\Controller\Constants;
use Liszted\Database\Connection;

$configFile = dirname(__DIR__) . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

if (defined('CONFIG')) {
    Connection::configure(CONFIG);
}

Constants::init();
