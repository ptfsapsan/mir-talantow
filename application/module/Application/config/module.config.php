<?php

namespace Application;

use Zend\Cache\StorageFactory;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\Db\Adapter\AdapterServiceFactory;
use Zend\Log\LoggerAbstractServiceFactory;

return [
   'router' => Classes\Router::getRouter(),
   'navigation' => Classes\Navigation::getNavigation(),
   'service_manager' => [
      'factories' => [
         LoggerAbstractServiceFactory::class,
         'dbAdapter' => AdapterServiceFactory::class,
         'navigation-index' => DefaultNavigationFactory::class,
         'navigation-user' => Classes\UserNavigationFactory::class,
         'navigation-site' => Classes\SiteNavigationFactory::class,
         'navigation-admin' => Classes\AdminNavigationFactory::class,
         'cache' => StorageFactory::class,
      ],
      'services' => [
         'Acl' => Classes\Acl::class,
       ]
   ],

   'controllers' => [
      'factories' => [
         Controller\IndexController::class => InvokableFactory::class,
         Controller\UserController::class => InvokableFactory::class,
         Controller\AdminController::class => InvokableFactory::class,
         Controller\AjaxController::class => InvokableFactory::class,
         Controller\CronController::class => InvokableFactory::class,
         Controller\ErrorController::class => InvokableFactory::class,
         Controller\ServiceController::class => InvokableFactory::class,
         Controller\SiteController::class => InvokableFactory::class,
      ],
   ],
   'session_config' => [
      'cookie_lifetime' => 60*60*24*15,
      'gc_maxlifetime'     => 60*60*24*15,
   ],
   'session_manager' => [
      'validators' => [
//         \Zend\Session\Validator\RemoteAddr::class,
//         \Zend\Session\Validator\HttpUserAgent::class,
      ]
   ],
   'caches' => [
      'redis' => [
         'adapter' => [
            'name' => 'redis',
            'options' => [
               //2 hours
               'ttl' => 7200,
               'server' => [
                  'host' => 'localhost',
                  'port' => 6379,
               ],
            ],
         ],
         'serializer',
      ],
   ],
   'session_storage' => [
      'type' => \Zend\Session\Storage\SessionArrayStorage::class
   ],
   'view_manager' => [
      'display_not_found_reason' => true,
      'display_exceptions' => true,
      'doctype' => 'HTML5',
      'not_found_template' => 'error/404',
      'exception_template' => 'error/index',
      'template_map' => Classes\Templates::getTemplateMap(),
      'template_path_stack' => Classes\Templates::getTemplatePathStack(),
   ],
      'mail' => [
         'smtp_options' => [
   //         'name' => 'smtp.mail.yahoo.com',
   //         'host' => 'smtp.mail.yahoo.com',
   //         'connection_class' => 'login',
   //         'connection_config' => [
   //            'username' => 'ptfsapsan@yahoo.com',
   //            'password' => 'a11235813',
   //            'port' => 465,
   //            'ssl' => 'tls',
   //         ],

            'name' => 'localhost.localdomain',
            'host' => 'cpanel4.d.fozzy.com',
            'connection_class' => 'login',
            'connection_config' => [
               'username' => 'transport@mir-talantow.ru',
               'password' => '3w}8TM!rMBe6',
               'port' => 465,
            ],
         ],
         'admin_email' => 'info@mir-talantow.ru',//'ptfsapsan@yahoo.com',//
      ],
   'social' => [
      'vkontakte' => 'https://vk.com/mirtalantowru',
      'odnoklassniki' => 'https://ok.ru/group/53313948287061',
      'facebook' => 'https://www.facebook.com/mirtalantow',
      'instagram' => 'https://www.instagram.com/mirtalantow/',
      'twitter' => 'https://twitter.com/mirtalantow',
      'google+' => 'https://plus.google.com/+Mir-talantowRu',
   ],
   'site_name' => 'Мир Талантов',
   'skype' => 'mir-talantow.ru',
   'email' => 'info@mir-talantow.ru',
];
