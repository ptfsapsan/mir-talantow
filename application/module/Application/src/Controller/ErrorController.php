<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController,
   Zend\View\Model\ViewModel;

class ErrorController extends AbstractActionController{
   const ERROR_NO_ROUTE = 404;
   const ERROR_NO_CONTROLLER = 404;


   public function indexAction(){

      if(ENV == 'production') return $this->redirect()->toRoute('404');

      $error = $this->request->getMetadata('error', false);
      if(!$error){
         $error = array(
            'type' => 404,
            'message' => 'Page not found',
         );
      }

      switch($error['type']){
         case self::ERROR_NO_ROUTE:
         case self::ERROR_NO_CONTROLLER:
         default:
            // 404 error -- controller or action not found
            $this->response->setStatusCode(404);
            break;
      }
      $this->layout()->setTemplate('layout/error');
      die('1');

      return new ViewModel(array('message' => $error['message']));
   }
   
   public function excuseAction(){
      
   }
   
}
