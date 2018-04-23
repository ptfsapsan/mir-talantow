<?php

use Zend\Mvc\Application;
use Zend\Stdlib\ArrayUtils;
ini_set("display_errors", 1);

// define application root for better file path definitions
$domain = ((isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443'))?
      'https://':
      'http://').
   $_SERVER['HTTP_HOST'];
define('DOMAIN', $domain);
define('ROOT_DIR', realpath(__DIR__));
define('APPLICATION_ROOT',
   realpath(ROOT_DIR . '/../applications/mir-talantow.ru'));
define('IMG', realpath(ROOT_DIR . '/images'));
define('FILES', realpath(ROOT_DIR . '/files'));
define('SITE_NAME', 'Мир Талантов');

$session_time = 60*60*24*10;
ini_set("session.gc_maxlifetime", $session_time) ;
ini_set('session.cookie_lifetime', $session_time);
ini_set('session.save_path', APPLICATION_ROOT.'/data/sessions');

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if(php_sapi_name() === 'cli-server'){
   $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
   if(__FILE__ !== $path && is_file($path)){
      return false;
   }
   unset($path);
}

// Composer autoloading
include APPLICATION_ROOT . '/vendor/autoload.php';

if(!class_exists(Application::class)){
   throw new RuntimeException(
      "Unable to load application.\n"
      . "- Type `composer install` if you are developing locally.\n"
      . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
      . "- Type `docker-compose run zf composer install` if you are using Docker.\n"
   );
}

// Retrieve configuration
$appConfig = require APPLICATION_ROOT . '/config/application.config.php';
if(file_exists(APPLICATION_ROOT . '/config/development.config.php')){
   $appConfig = ArrayUtils::merge(
      $appConfig, require APPLICATION_ROOT . '/config/development.config.php'
   );
}

// Run the application!
Application::init($appConfig)
   ->run();
