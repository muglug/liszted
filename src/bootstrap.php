<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Liszted\Controller\Constants;

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF8');

Constants::init();
