<?php
namespace Application\Classes;

class Templates{

   private static $_email_templates = [
      'from_footer',
      'from_admin',
      'new_order',
      'diploma',
      'confirm_pay',
      'delete_order',
      'remind_pay',
      'confirm_pay',
      'not_enough',
      'add_article',
      'send_certificate',
      'forgot',
      'active_registration',
      'site-contacts',
      'new_thank',
      'send_thank',
      'ad',
      'comment',
   ];

   public static function getTemplatePathStack(){
      $dir = realpath(__DIR__.'/../../../Application').'/';
      $res = ['views' => $dir.'view/'];
      $res['template'] = $dir.'view/emails/layouts/template.phtml';
      $res['site-template'] = $dir.'view/emails/layouts/site-template.phtml';

      foreach(self::$_email_templates as $t)
         $res[$t] = $dir.'view/emails/'.$t.'.phtml';

      return $res;
   }
   
   public static function getTemplateMap(){
      $dir = realpath(__DIR__.'/../../../Application/view').'/';
      return [
         'layout/layout' => $dir.'layout/layout.phtml',
         'layout/site' => $dir.'layout/site.phtml',
         'layout/admin' => $dir.'layout/admin.phtml',
         'layout/error' => $dir.'layout/error.phtml',
         'error/404' => $dir.'error/404.phtml',
         'error/403' => $dir.'error/403.phtml',
         'error/index' => $dir.'error/500.phtml',
      ];
   }
   
}