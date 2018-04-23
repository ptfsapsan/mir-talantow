<?php

namespace Application\Classes;

use Application\Controller\IndexController;
use Application\Controller\AjaxController;
use Application\Controller\AdminController;
use Application\Controller\ServiceController;
use Application\Controller\CronController;
use Application\Controller\ErrorController;
use Application\Controller\SiteController;
use Application\Controller\UserController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

class Router{
   public static function getRouter(){

      $arr = [
         'diploms' => 'obrazci-diplomov',
         'contacts' => 'kontakti',
         'rules' => 'pravila-provedeniya-konkursov',
         'conf' => 'politika-confidenzialnosti',
      ];
      $routes = [
         'routes' => [
            'home' => [
               'type' => Literal::class,
               'options' => [
                  'route' => '/',
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'index',
                  ],
               ],
            ],
            'kid' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/konkursi-dlya-detei[/:nomination/:theme]',
                  'constraints' => [
                     'nomination' => '[a-z0-9_-]*',
                     'theme' => '\S*',
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'kid',
                  ],
               ],
            ],
            'educator' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/konkursi-dlya-pedagogov[/:nomination]',
                  'constraints' => [
                     'nomination' => '[a-z0-9_-]*',
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'educator',
                  ],
               ],
            ],
            'articles' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/stati[/:page[/:theme]]',
                  'constraints' => [
                     'theme' => '[a-z0-9_-]*',
                     'id' => '\d+',
                     'page' => '\d+',
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'articles',
                     'page' => 1,
                  ],
               ],
            ],
            'article' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/statya/:theme/:id',
                  'constraints' => [
                     'theme' => '[a-z0-9_-]*',
                     'id' => '\d+',
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'articles',
                  ],
               ],
            ],
            'add-article' => [
               'type' => Literal::class,
               'options' => [
                  'route' => '/dobavit-statu',
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'add-article',
                  ],
               ],
            ],
            'login' => [
               'type' => Literal::class,
               'options' => [
                  'route' => '/login',
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'login',
                  ],
               ],
            ],
            'results' => [
               'type' => Segment::class,
               'options' => [
                  'route' =>
                     '/resultati-konkursov/:type/:kind',
                  'constraints' => [
                     'type' => '(kid|educator)',
                     'kind' => '(all|monthly|fast|urgent)',
                  ],
                  'defaults' => [
                     'type' => 'kid',
                     'kind' => 'all',
                     'controller' => IndexController::class,
                     'action' => 'results',
                  ],
               ],
            ],
            'gallery' => [
               'type' => Segment::class,
               'options' => [
                  'route' =>
                     '/galereya-rabot/:type',
                  'constraints' => [
                     'type' => '(kid|educator)',
                  ],
                  'defaults' => [
                     'type' => 'kid',
                     'controller' => IndexController::class,
                     'action' => 'gallery',
                  ],
               ],
            ],
            'pay' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/oplata-organizacionnogo-vznosa[/:order_id/:email]',
                  'constraints' => [
                     'order_id' => '\d+',
                     'email' => '\S+'
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'pay',
                  ],
               ],
            ],
            'comments' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/otzivi-o-saite[/:page]',
                  'constraints' => [
                     'page' => '\d+'
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'comments',
                     'page' => 1,
                  ],
               ],
            ],
            'diploma' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/diploma/:code',
                  'constraints' => [
                     'code' => '\S{10}'
                  ],
                  'defaults' => [
                     'controller' => AjaxController::class,
                     'action' => 'diploma',
                  ],
               ],
            ],
            'thank' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/thank/:code',
                  'constraints' => [
                     'code' => '\S{7}'
                  ],
                  'defaults' => [
                     'controller' => AjaxController::class,
                     'action' => 'thank',
                  ],
               ],
            ],
            'diplom' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/diplom/:code[/:act]',
                  'constraints' => [
                     'code' => '\S{10}',
                     'act' => '[a-z]+',
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'diplom',
                  ],
               ],
            ],
            'ajax-certificate' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/ajax/certificate/:code',
                  'constraints' => [
                     'code' => '\S{10}'
                  ],
                  'defaults' => [
                     'controller' => AjaxController::class,
                     'action' => 'certificate',
                  ],
               ],
            ],
            'index-certificate' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/index/certificate/:code[/:act]',
                  'constraints' => [
                     'code' => '\S{10}',
                     'act' => '[a-z]+',
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'certificate',
                  ],
               ],
            ],
            'work' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/work/:order_code',
                  'constraints' => [
                     'order_code' => '\S{10}',
                  ],
                  'defaults' => [
                     'controller' => IndexController::class,
                     'action' => 'work',
                  ],
               ],
            ],
            'admin' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/admin[/[:action]]',
                  'constraints' => [
                     'action' =>
                        '(orders|pays|themes|kid-nominations|clients'.
                        '|educator-nominations|blanks|orders|gallery'.
                        '|results|comments|article-themes|articles'.
                        '|article-comments|certificate|add-article|thanks)',
                  ],
                  'defaults' => [
                     'controller' => AdminController::class,
                     'action' => 'orders',
                  ],
               ],
            ],
            'admin-article-comment' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/admin/article-comment/:id',
                  'constraints' => [
                     'id' => '\d+'
                  ],
                  'defaults' => [
                     'controller' => AdminController::class,
                     'action' => 'article-comment-detail',
                  ],
               ],
            ],
            'admin-article' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/admin/article/:id',
                  'constraints' => [
                     'id' => '\d+'
                  ],
                  'defaults' => [
                     'controller' => AdminController::class,
                     'action' => 'article',
                  ],
               ],
            ],
            'admin-order-detail' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/admin/order-detail/:id',
                  'constraints' => [
                     'id' => '\d+'
                  ],
                  'defaults' => [
                     'controller' => AdminController::class,
                     'action' => 'order-detail',
                  ],
               ],
            ],
            'admin_client_detail' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/client-detail/:email',
                  'constraints' => [
                     'email' => '\S+'
                  ],
                  'defaults' => [
                     'controller' => AdminController::class,
                     'action' => 'client-detail',
                  ],
               ],
            ],
            'admin_thank_detail' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/thank-detail/:id',
                  'constraints' => [
                     'id' => '\d+'
                  ],
                  'defaults' => [
                     'controller' => AdminController::class,
                     'action' => 'thank-detail',
                  ],
               ],
            ],
            'user' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/user[/:action]',
                  'constraints' => [
                     'action' => '[a-z-]+'
                  ],
                  'defaults' => [
                     'controller' => UserController::class,
                     'action' => 'settings',
                  ],
               ],
            ],
            'user-article' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/user/article[/:article_id]',
                  'constraints' => [
                     'article_id' => '\d+'
                  ],
                  'defaults' => [
                     'controller' => UserController::class,
                     'action' => 'article',
                  ],
               ],
            ],
            'site' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/:site_code[/:action]',
                  'constraints' => [
                     'action' => '[a-z-]+',
                     'site_code' => '\d{6}',
                  ],
                  'defaults' => [
                     'controller' => SiteController::class,
                     'action' => 'index',
                  ],
               ],
            ],
            'site-article' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/:site_code/article/:id',
                  'constraints' => [
                     'id' => '\d+',
                     'site_code' => '\d{6}',
                  ],
                  'defaults' => [
                     'controller' => SiteController::class,
                     'action' => 'article',
                  ],
               ],
            ],
            'ajax' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/ajax/:action',
                  'constraints' => [
                     'action' => '[a-z-]+',
                  ],
                  'defaults' => [
                     'controller' => AjaxController::class,
                     'action' => 'index',
                  ],
               ],
            ],
            'file-content' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/file-content/:id[/:type]',
                  'constraints' => [
                     'id' => '\d+',
                     'type' => 'temp',
                  ],
                  'defaults' => [
                     'controller' => ServiceController::class,
                     'action' => 'file-content',
                  ],
               ],
            ],
            'load-file' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/load-file/:id',
                  'constraints' => [
                     'id' => '\d+',
                  ],
                  'defaults' => [
                     'controller' => ServiceController::class,
                     'action' => 'load-file',
                  ],
               ],
            ],
            'logout' => [
               'type' => Literal::class,
               'options' => [
                  'route' => '/logout',
                  'defaults' => [
                     'controller' => ServiceController::class,
                     'action' => 'logout',
                  ],
               ],
            ],
            '404' => [
               'type' => Literal::class,
               'options' => [
                  'route' => '/404',
                  'defaults' => [
                     'controller' => ErrorController::class,
                     'action' => 'no-page',
                  ],
               ],
            ],
            'robots-txt' => [
               'type' => Literal::class,
               'options' => [
                  'route' => '/robots.txt',
                  'defaults' => [
                     'controller' => ServiceController::class,
                     'action' => 'robots',
                  ],
               ],
            ],
            'sitemap-xml' => [
               'type' => Literal::class,
               'options' => [
                  'route' => '/sitemap.xml',
                  'defaults' => [
                     'controller' => ServiceController::class,
                     'action' => 'sitemap',
                  ],
               ],
            ],
            'cron' => [
               'type' => Segment::class,
               'options' => [
                  'route' => '/cron/:action',
                  'constraints' => [
                     'action' => '[a-z-]+',
                  ],
                  'defaults' => [
                     'controller' => CronController::class,
                     'action' => 'add-results',
                  ],
               ],
            ],
         ],
      ];

      foreach($arr as $r => $n){
         $routes['routes'][$r] = [
            'type' => Literal::class,
            'options' => [
               'route' => "/$n",
               'defaults' => [
                  'controller' => IndexController::class,
                  'action' => $r,
               ],
            ],
         ];
      }

      return $routes;
   }
}