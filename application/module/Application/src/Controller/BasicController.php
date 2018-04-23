<?php

namespace Application\Controller;

use Application\Model\Auth;
use Application\Model\Settings;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;


class BasicController extends AbstractActionController{

   /**
    * @var ServiceManager
    */
   protected $_sm;
   /**
    * @var FlashMessenger
    */
   protected $_fm;
   protected $_get;
   protected $_post;
   protected $_params;
   protected $_identity;
   /**
    * @var ViewModel
    */
   protected $_view_model;

   public function onDispatch(MvcEvent $e){
      $this->_sm = $e->getApplication()->getServiceManager();
      $sm = $this->_sm;
      $config = $sm->get('config');
      $post = (array)$this->params()->fromPost();
      $get = (array)$this->params()->fromQuery();
      $this->_get = $get;
      $this->_post = $post;
      $this->_params = array_merge($post, $get);
      $this->_view_model = new ViewModel();
      $this->_fm = $this->flashMessenger();
      $this->params()->fromRoute('controller');
      
      $model_auth = new Auth($sm);
      $identity = $model_auth->getIdentity();
      $this->_identity = $identity;
      $model_settings = new Settings($sm);
      

      $this->layout()->setVariables([
         'config' => $config,
         'identity' => $identity,
         'settings' => $identity?
            $model_settings->getSettingsByUserId($identity['id']): null,
      ]);
      parent::onDispatch($e);
   }

   protected function error403(){
      $e = $this->getEvent();
      $response = $e->getResponse();
      $response->setStatusCode(403);

      $this->layout()
         ->setTemplate('layout/error')
         ->setVariables([
            'disable_sidebar' => true
         ])
      ;

      $view = new ViewModel();
      $view->setTemplate('error/403');

      return $view;
   }

   protected function error404(){
      $e = $this->getEvent();
      $response = $e->getResponse();
      $response->setStatusCode(404);
      $this->layout()
         ->setTemplate('layout/error')
         ->setVariables([
            'disable_sidebar' => true
         ])
      ;

      $view = new ViewModel();
      $view->setTemplate('error/404');

      return $view;
   }


}
