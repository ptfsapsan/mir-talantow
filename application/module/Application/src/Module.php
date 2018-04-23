<?php

namespace Application;

use Application\Model\Acl;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Module{

   public function onBootstrap(MvcEvent $e){
      $application = $e->getApplication();
      $eventManager = $application->getEventManager();

      $eventManager->attach(MvcEvent::EVENT_ROUTE,
         [$this, 'accessControl'], 1);
      $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR,
         [$this, 'dispatchErrorHandler']);
      $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR,
         [$this, 'renderErrorHandler']);
      $serviceManager = $application->getServiceManager();
      $sessionManager = $serviceManager->get(SessionManager::class);
      $sessionManager->start();

      $adapter = $application->getServiceManager()->get('dbAdapter');
      GlobalAdapterFeature::setStaticAdapter($adapter);
   }
   
   /**
    * Обработчик для ненайденного пути
    *
    * @param MvcEvent $e
    */
   public function dispatchErrorHandler(MvcEvent $e){
      /** @var ViewModel $layout */
      $layout = $e->getViewModel();
      $layout->setTemplate('layout/error');
   }

   /**
    * Обработчик исключительной ошибки
    *
    * @param MvcEvent $e
    */
   public function renderErrorHandler(MvcEvent $e){
      $layout = $e->getViewModel();
      $layout->setTemplate('layout/error');
   }


   public function getConfig(){
      return include __DIR__ . '/../config/module.config.php';
   }



   public function accessControl(MvcEvent $e){
      $route = $e->getRouteMatch();
      if(!$route) $this->redirect($e, 'home');
      $params = $route->getParams();
      $controller = $params['controller'];

      $auth = new \Zend\Authentication\AuthenticationService();
      $identity = $auth->getIdentity();

      $acl_model = new Acl();
      $roles = $acl_model::getAllRoles();
      $role = empty($identity) || !in_array($identity['role'], $roles)?
         $acl_model::$role_guest: $identity['role'];
      $acl = $acl_model->init();

      if(!$acl->isAllowed($role, $controller)) $this->redirect($e, 'home');

      $layout = $e->getViewModel();
      switch($controller){
         case \Application\Controller\IndexController::class:
         case \Application\Controller\UserController::class:
            $layout->setTemplate('/layout/layout');
            break;
         case \Application\Controller\SiteController::class:
            $layout->setTemplate('/layout/site');
            break;
         case \Application\Controller\AdminController::class:
            $layout->setTemplate('/layout/admin');
            break;

      }
//      $action = $params['action'];

//       //включаем кеширование страниц
//      if($controller == 'index' && in_array($action,
//            ['index', 'rules', 'diploms', 'contacts', 'sitemap'])){
//         $d = date('r', strtotime('+1 day'));
//         header("Last-Modified: $d");
//         header('Cache-Control: max-age=86400, must-revalidate');
//         header("Expires: $d");
//         header_remove('Pragma');
//      }

   }

   protected function redirect(MvcEvent $e, $route, $params = []){
      $router = $e->getRouter();
      $url = $router->assemble($params, ['name' => $route]);
      $response = $e->getResponse();
      $response->setStatusCode(302);
      //redirect to login route...
      $response->getHeaders()->addHeaderLine('Location', $url);
      $e->stopPropagation();
   }

   public function getViewHelperConfig(){
      return [
         'invokables' => [
            'ajaxPaginator' => \Application\View\Helper\AjaxPaginator::class,
            'paginatorWidget' => \Application\View\Helper\PaginatorWidget::class,
            'paginatorWidget2' => 
               \Application\View\Helper\PaginatorWidget2::class,
         ]
      ];
   }

}
