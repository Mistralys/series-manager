<?php

    define('APP_ROOT', dirname(__FILE__));
    define('APP_TVDB_API_KEY', 'DD47E4235E98B099');
    
    if(!file_exists('vendor/autoload.php'))
    {
        die('Autoloader missing, run <code>composer update</code>.');
    }
    
    require_once 'vendor/autoload.php';
    
    if(!file_exists('config-local.php'))
    {
        die('Local configuration file missing.');
    }
    
    require_once 'config-local.php';
    
    date_default_timezone_set('Europe/Paris');
    
    ini_set('display_errors', 1);error_reporting(E_ALL);
    
    $manager = Manager::getInstance();
    $manager->start();
