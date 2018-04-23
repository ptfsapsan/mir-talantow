<?php
namespace Application\Controller;

use Application\Form\Contacts;
use Application\Form\SiteComment;
use Application\Model\Auth;
use Application\Model\Settings;
use Application\Model\SiteImages;
use Application\Model\SitePages;
use Application\Model\Mail;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\ServiceManager\ServiceManager;

class SiteController extends AbstractActionController{
   /**
    * @var ServiceManager
    */
   private $_sm;
   private $_post;
   private $_get;
   private $_user_id;
   /**
    * @var FlashMessenger
    */
   private $_fm;

   public function onDispatch(MvcEvent $e){
      $this->_sm = $e->getApplication()->getServiceManager();
      $sm = $this->_sm;
      $site_code = $this->params()->fromRoute('site_code', 0);

      $model_settings = new Settings($sm);
      $settings = $model_settings->getSettingsBySiteCode($site_code);
      if(empty($settings)) $this->redirect()->toRoute('home');
      $user_id = $settings['user_id'];
      $model_site_images = new SiteImages($sm);
      $site_images = $model_site_images->getByUser($user_id);
      $model_auth = new Auth($sm);
      $user = $model_auth->getUser($user_id);
      $this->_fm = $this->flashMessenger();

      $this->_settings = $settings;
      $post = (array)$this->params()->fromPost();
      $get = (array)$this->params()->fromQuery();
      $this->_get = $get;
      $this->_post = $post;
      $this->_user_id = $user_id;
      $this->_site_code = $site_code;

      $this->layout()->setVariables([
         'settings' => $settings,
         'site_images' => $site_images,
         'user' => $user,
      ]);

      parent::onDispatch($e);
   }

   public function indexAction(){
      $sm = $this->_sm;
      $user_id = $this->_user_id;
      $model_site_pages = new SitePages($sm);

      return [
         'page' => $model_site_pages->getPageByType('index', $user_id),
      ];
   }

   public function blogAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $user_id = $this->_user_id;
      $model_site_pages = new SitePages($sm);


      return [
         'articles' => $model_site_pages->getPageByType('blog', $user_id),

      ];
   }

   public function articleAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $user_id = $this->_user_id;
      $article_id = $this->params()->fromRoute('id');
      $model_site_pages = new SitePages($sm);
      $form_site_comment = new SiteComment();

      if(!empty($post['act'])){
         switch($post['act']){
            case 'add_comment':
               $model_site_pages->addComment($post, $article_id);
               break;
         }
         $this->redirect()->refresh();
      }

            return [
         'article' => $model_site_pages->getPageByType('blog', $user_id,
            $article_id),
         'form_site_comment' => $form_site_comment,
         'comments' => $model_site_pages->getCommentsByPage($article_id),
      ];
   }
   
   public function contactsAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $user_id = $this->_user_id;
      $form_contacts = new Contacts();

      if(!empty($post['act'])){
         switch($post['act']){
            case 'contacts':
               $model_mail = new Mail($sm);
               try{
                  $model_mail->sendFromPrivateSite($post, $user_id);
                  $fm->addSuccessMessage('Сообщение отправлено владельцу сайта');
               }
               catch(\Exception $e){
                  $fm->addErrorMessage($e->getMessage());
               }
               break;
         }
         $this->redirect()->refresh();
      }
      
      return [
         'form_contacts' => $form_contacts,
      ];
   }
   


}