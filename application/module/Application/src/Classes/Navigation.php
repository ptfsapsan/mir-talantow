<?php

namespace Application\Classes;

class Navigation{
   public static function getNavigation(){
      $date = date('Y-m-d');
      return [
         'default' => [
            [
               'label' => 'Главная',
               'route' => 'home',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Конкурсы для детей',
               'route' => 'kid',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Конкурсы для педагогов',
               'route' => 'educator',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Статьи',
               'route' => 'articles',
               'lastmod' => $date,
               'changefreq' => 'daily',
               'pages' => [
                  [
                     'route' => 'add-article',
                     'label' => '123',
                  ],
               ]
            ],
            [
               'label' => 'Образцы дипломов',
               'route' => 'diploms',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Правила',
               'route' => 'rules',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Результаты конкурсов',
               'route' => 'results',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Галерея работ',
               'route' => 'gallery',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Отзывы о сайте',
               'route' => 'comments',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Контакты',
               'route' => 'contacts',
               'lastmod' => $date,
               'changefreq' => 'daily',
            ],
            [
               'label' => 'Войти',
               'route' => 'login',
            ]
         ],
         'user' => [
            [
               'label' => 'Главная',
               'route' => 'home',
            ],
            [
               'label' => 'Личные данные',
               'route' => 'user',
               'action' => 'settings',
            ],
            [
               'label' => 'Конкурсы для детей',
               'route' => 'user',
               'action' => 'kid-contest'
            ],
            [
               'label' => 'Конкурсы для педагогов',
               'route' => 'user',
               'action' => 'educator-contest'
            ],
            [
               'label' => 'Благодарности',
               'route' => 'user',
               'action' => 'thank'
            ],
            [
               'label' => 'Настройки сайта',
               'route' => 'user',
               'action' => 'site-settings',
            ],
            [
               'label' => 'Настройки главной страницы',
               'route' => 'user',
               'action' => 'site-index',
            ],
            [
               'label' => 'Статьи блога',
               'route' => 'user',
               'action' => 'site-blog',
            ],
            [
               'label' => 'Комментарии статей блога',
               'route' => 'user',
               'action' => 'site-comments',
            ],
            [
               'label' => 'Выход',
               'route' => 'logout',
            ],
         ],
         
         'site' => [
            [
               'label' => 'Главная',
               'route' => 'site',
               'action' => 'index',
               'params' => [
                  'site_code' => '111111',
               ],
            ],
            [
               'label' => 'Блог',
               'route' => 'site',
               'action' => 'blog',
               'params' => [
                  'site_code' => '111111',
               ],
            ],
            [
               'label' => 'Контакты',
               'route' => 'site',
               'action' => 'contacts',
               'params' => [
                  'site_code' => '111111',
               ],
            ],
         ],

         'admin' => [
            [
               'label' => 'Конкурсы',
               'route' => 'admin',
               'action' => '',
               'pages' => [
                  [
                     'label' => 'Заказы',
                     'route' => 'admin',
                     'action' => 'orders',
                  ],
                  [
                     'label' => 'Оплата',
                     'route' => 'admin',
                     'action' => 'pays',
                  ],
                  [
                     'label' => 'Бланки дипломов',
                     'route' => 'admin',
                     'action' => 'blanks',
                  ],
                  [
                     'label' => 'Галерея',
                     'route' => 'admin',
                     'action' => 'gallery',
                  ],
                  [
                     'label' => 'Клиенты',
                     'route' => 'admin',
                     'action' => 'clients',
                  ],
                  [
                     'label' => 'Пустые результаты',
                     'route' => 'admin',
                     'action' => 'results',
                  ],
                  [
                     'label' => 'Благодарности',
                     'route' => 'admin',
                     'action' => 'thanks',
                  ],
               ],
            ],
            [
               'label' => 'Статьи',
               'route' => 'admin',
               'action' => '',
               'pages' => [
                  [
                     'label' => 'Темы статей',
                     'route' => 'admin',
                     'action' => 'article-themes',
                  ],
                  [
                     'label' => 'Статьи',
                     'route' => 'admin',
                     'action' => 'articles',
                  ],
                  [
                     'label' => 'Комментарии статей',
                     'route' => 'admin',
                     'action' => 'article-comments',
                  ],
                  [
                     'label' => 'Бланки сертификатов',
                     'route' => 'admin',
                     'action' => 'certificate',
                  ],
               ],
            ],
            [
               'label' => 'Общие',
               'route' => 'admin',
               'action' => '',
               'pages' => [
                  [
                     'label' => 'Отзывы',
                     'route' => 'admin',
                     'action' => 'comments',
                  ],
                  [
                     'label' => 'Сбросить кеш',
                     'route' => 'ajax',
                     'action' => 'clear-cache',
                  ],
                  [
                     'label' => 'Выход',
                     'route' => 'logout',
                  ],
               ],
            ],
            [
               'label' => 'Детские конкурсы',
               'route' => 'admin',
               'action' => '',
               'pages' => [
                  [
                     'label' => 'Номинации',
                     'route' => 'admin',
                     'action' => 'kid-nominations'
                  ],
                  [
                     'label' => 'Темы конкурсов',
                     'route' => 'admin',
                     'action' => 'themes'
                  ],
               ]
            ],
            [
               'label' => 'Конкурсы для педагогов',
               'route' => 'admin',
               'action' => '',
               'pages' => [
                  [
                     'label' => 'Номинации',
                     'route' => 'admin',
                     'action' => 'educator-nominations'
                  ],
               ],
            ],
         ],

      ];
   }
}