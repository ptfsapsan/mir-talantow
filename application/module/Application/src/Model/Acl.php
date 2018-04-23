<?php

namespace Application\Model;

use Application\Controller\AdminController;
use Application\Controller\AjaxController;
use Application\Controller\CronController;
use Application\Controller\ErrorController;
use Application\Controller\IndexController;
use Application\Controller\ServiceController;
use Application\Controller\SiteController;
use Application\Controller\UserController;
use Zend\Permissions\Acl\Acl as Permissions;

class Acl extends Permissions
{

   public static $role_guest = 'guest';
   public static $role_user = 'user';
   public static $role_admin = 'admin';


   public static $_resources = [
      'index' => IndexController::class,
      'user' => UserController::class,
      'ajax' => AjaxController::class,
      'admin' => AdminController::class,
      'service' => ServiceController::class,
      'error' => ErrorController::class,
      'cron' => CronController::class,
      'site' => SiteController::class,
   ];

   public static function getAllRoles() : array
   {
      return [self::$role_guest, self::$role_user, self::$role_admin];
   }

   public function init(){
      // роли
      $roles = self::getAllRoles();
      foreach($roles as $role) $this->addRole($role);
      // ресурсы
      foreach(self::$_resources as $resource) $this->addResource($resource);

      return $this
         // запрещения
         ->deny()
         // разрешения
         ->allow($roles, [
            self::$_resources['service'],
            self::$_resources['ajax'],
            self::$_resources['error'],
            self::$_resources['cron'],
            self::$_resources['site'],
            self::$_resources['index'],
         ])
         ->allow(self::$role_guest, [
            self::$_resources['index'],
         ])
         ->allow(self::$role_user, [
            self::$_resources['user'],
         ])
         ->allow(self::$role_admin, [
            self::$_resources['admin'],
         ])
      ;
   }
}
