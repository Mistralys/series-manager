<?php

declare(strict_types=1);

use Mistralys\SeriesManager\Manager;

const APP_ROOT = __DIR__;

if(!file_exists(__DIR__.'/vendor/autoload.php'))
{
    die('Autoloader missing, run <code>composer install</code>.');
}

require_once __DIR__.'/vendor/autoload.php';

if(!file_exists(__DIR__.'/config-local.php'))
{
    die('Local configuration file missing.');
}

require_once __DIR__.'/config-local.php';

date_default_timezone_set('Europe/Paris');

Manager::getInstance()->start();
