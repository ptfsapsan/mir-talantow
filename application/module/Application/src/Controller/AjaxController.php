<?php
namespace Application\Controller;

use Application\Classes\Facebook;
use Application\Model\Articles;
use Application\Model\Auth;
use Application\Model\Blanks;
use Application\Model\Certificates;
use Application\Model\Cron;
use Application\Model\Files;
use Application\Model\Nominations;
use Application\Model\Orders;
use Application\Model\SiteImages;
use Application\Model\SitePages;
use Application\Model\TempFiles;
use Application\Model\Thanks;
use Application\Model\Themes;
use Application\Model\Tokens;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;

class AjaxController extends AbstractActionController{

   /**
    * @var ViewModel
    */
   protected $_view_model;

   /**
    * @var ServiceManager
    */
   protected $_sm;

   public function onDispatch(MvcEvent $e){
      $this->_sm = $e->getApplication()->getServiceManager();

      $view_model = new ViewModel();
      $view_model->setTerminal(true);
      $this->_view_model = $view_model;

      parent::onDispatch($e);
   }
   
   public function uploadTempFilesAction(){
      $sm = $this->_sm;
      $file = $this->params()->fromFiles('file');
      $model_temp_files = new TempFiles($sm);
      $data = ['result' => 'success'];
      try{
         $model_temp_files->addFile($file);
      }
      catch(\Exception $e){
         $data = [
            'result' => 'error',
            'message' => $e->getMessage(),
         ];
      }
      return $this->_view_model
         ->setVariables([
            'data' => $data,
         ])
         ->setTemplate('application/ajax/json');
   }

   public function getTempFilesAction(){
      $sm = $this->_sm;
      $model_temp_files = new TempFiles($sm);

      return $this->_view_model
         ->setVariables([
            'files' => $model_temp_files->getFiles(),
         ])
         ->setTemplate('application/ajax/get-temp-files');
   }

   public function deleteTempFileAction(){
      $id = $this->params()->fromPost('id', 0);
      $sm = $this->_sm;
      $model_temp_files = new TempFiles($sm);
      $model_temp_files->delFile($id);
      return $this->_view_model
         ->setTemplate('application/ajax/blank');
   }

   public function diplomaAction(){
      $code = $this->params()->fromRoute('code');
      $sm = $this->_sm;
      $model_orders = new Orders($sm);
      $order = $model_orders->getOrderByCode($code);
      if(empty($order)) $this->redirect()->toRoute('excuseme');
      $model_blanks = new Blanks($sm);
      $img = $model_blanks->getDiploma($order);

      header('Content-Type: image/jpeg');
      imagejpeg($img);
      imagedestroy($img);
   }

   public function thankAction(){
      $code = $this->params()->fromRoute('code');
      $sm = $this->_sm;
      $model_thank = new Thanks($sm);
      $thank = $model_thank->getThankByCode($code);
      if(empty($thank)) $this->redirect()->toRoute('excuseme');
      $img = $model_thank->getThank($thank);

      header('Content-Type: image/jpeg');
      imagejpeg($img);
      imagedestroy($img);
   }

   public function certificateAction(){
      $code = $this->params()->fromRoute('code');
      $sm = $this->_sm;
      $model_articles = new Articles($sm);
      $article = $model_articles->getArticleByCode($code);
      if(empty($article)) return $this->redirect()->toRoute('excuseme');
      $model_certificates = new Certificates($sm);
      $img = $model_certificates->getCertificate($article);

      header('Content-Type: image/jpeg');
      imagejpeg($img);
      imagedestroy($img);
   }
   
   public function verifyOrderAction(){
      $params = $this->params()->fromPost();
      $model_orders = new Orders($this->_sm);

      return $this->_view_model
         ->setVariables([
            'data' => 
               $model_orders->verifyByIdAndEmail($params['id'], 
                  $params['email']),
         ])
         ->setTemplate('application/ajax/string');
   }

   public function getOrderDebtAction(){
      $params = $this->params()->fromPost();
      $sm = $this->_sm;
      $res = 'error';
      $model_orders = new Orders($sm);
      $order = $model_orders->getById($params['id']);
      
      if($order && $order['email'] == $params['email']){
         $debt = $order['price'] - $order['paid'];
         if($debt > 0) $res = number_format($debt, 2, '.', '');
      }

      return $this->_view_model
         ->setVariables([
            'data' => $res,
         ])
         ->setTemplate('application/ajax/string');
   }

   public function getResultsAction(){
      $params = $this->params()->fromPost();
      $sm = $this->_sm;
      $model_orders = new Orders($sm);
      $params['on_page'] = 50;
      
      return $this->_view_model
         ->setVariables([
            'orders' => $model_orders->getForResult($params),
            'type' => $params['type'],
         ])
         ->setTemplate('application/ajax/results');
   }
   
   public function getGalleryAction(){
      $params = $this->params()->fromPost();
      $sm = $this->_sm;
      $model_files = new Files($sm);
      $params['on_page'] = 50;

      return $this->_view_model
         ->setVariables([
            'files' => $model_files->getFilesForGallery($params),
         ])
         ->setTemplate('application/ajax/gallery');
   }
   
   public function redirectAction(){
      $sm = $this->_sm;
      $params = $this->params()->fromQuery();
      if(empty($params['type']) || empty($params['nomination_id']))
         return $this->redirect()->toRoute('home');
      
      $model_nominations = new Nominations($sm);
      $nomination = $model_nominations->getById($params['nomination_id']);
      if(empty($nomination)) return $this->redirect()->toRoute('home');
      
      if($params['type'] == 'educator')
         return $this->redirect()->toRoute('educator',
            ['nomination' => $nomination['trans']]);

      if(empty($params['theme_id'])) return $this->redirect()->toRoute('home');

      $model_themes = new Themes($sm);
      $theme = $model_themes->getById($params['theme_id']);
      if(empty($theme)) return $this->redirect()->toRoute('home');

      return $this->redirect()->toRoute('kid',
         ['nomination' => $nomination['trans'], 'theme' => $theme['trans']]);
   }

   public function toDiplomaAction(){
      $params = $this->params()->fromPost();
      $res = 'empty';
      if(!empty($params['email']) && !empty($params['id'])){
         $model_orders = new Orders($this->_sm);
         $order = $model_orders->getOrdersByEmailAndId(
            $params['email'], $params['id']);
         if(!empty($order)) $res = $order['code'];
      }

      return $this->_view_model
         ->setVariables([
            'data' => $res,
         ])
         ->setTemplate('application/ajax/string');
   }

   public function facebookCallbackAction(){
      file_put_contents(APPLICATION_ROOT.'/data/logs/facebook.log', '111');
      $sm = $this->_sm;
      $model_tokens = new Tokens($sm);
      $model_facebook = new Facebook();
      $token = $model_tokens->getCurrentToken('facebook');
      if(empty($token)){
         $token_data = $model_facebook->getToken();
         $token_data['type'] = 'facebook';
         file_put_contents(APPLICATION_ROOT.'/data/logs/facebook.log',
            var_export($token_data, true));
         $model_tokens->addToken($token_data);
      }
      return $this->_view_model
         ->setTemplate('application/ajax/blank');
   }

   public function facebookTestAction(){
      $model = new Facebook();
      $model->sendLoginUrl();
      return $this->_view_model
         ->setTemplate('application/ajax/blank');

   }
   
   public function activeRegistrationAction(){
      $temp_code = $this->params()->fromQuery('temp_code');
      $model_auth = new Auth($this->_sm);
      $fm = $this->flashMessenger();
      try{
         $model_auth->activeRegistration($temp_code);
         $fm->addSuccessMessage('Вы благополучно активировали аккаунт');
         $this->redirect()->toRoute('user');
      }
      catch(\Exception $e){
         $fm->addErrorMessage($e->getMessage());
         $this->redirect()->toRoute('home');
      }
   }

   public function uploadSiteImageAction(){
      $sm = $this->_sm;
      $file = $this->params()->fromFiles('file');
      $type = $this->params()->fromQuery('type');
      $model_site_images = new SiteImages($sm);
      $data = ['result' => 'success'];
      $model_auth = new Auth($sm);
      $user_id = $model_auth->getUserId();
      
      try{
         $model_site_images->addImage($file, $type, $user_id);
      }
      catch(\Exception $e){
         $data = [
            'result' => 'error',
            'message' => $e->getMessage(),
         ];
      }
      return $this->_view_model
         ->setVariables([
            'data' => $data,
         ])
         ->setTemplate('application/ajax/json');
   }

   public function getSiteImagesAction(){
      $type = $this->params()->fromPost('type');
      $model_site_images = new SiteImages($this->_sm);
      $model_auth = new Auth($this->_sm);
      $user_id = $model_auth->getUserId();

      return $this->_view_model
         ->setVariables([
            'images' => $model_site_images->getByType($type, $user_id),
         ])
         ->setTemplate('application/ajax/get-site-images');
   }

   public function setActiveBlogArticleAction(){
      $article_id = $this->params()->fromPost('id');
      $active = $this->params()->fromPost('active');
      $model_site_pages = new SitePages($this->_sm);
      $model_site_pages->changeActive($article_id, $active);

      return $this->_view_model
         ->setTemplate('application/ajax/blank');
   }

   public function setActiveBlogCommentAction(){
      $comment_id = $this->params()->fromPost('id');
      $active = $this->params()->fromPost('active');
      $model_site_pages = new SitePages($this->_sm);
      $model_site_pages->changeCommentActive($comment_id, $active);

      return $this->_view_model
         ->setTemplate('application/ajax/blank');
   }
   
   public function tAction(){
      return $this->_view_model
         ->setTemplate('application/ajax/blank');
   }


   


}
