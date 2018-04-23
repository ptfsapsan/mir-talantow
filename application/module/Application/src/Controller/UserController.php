<?php
namespace Application\Controller;

use Application\Form\Filter\OrderFilter;
use Application\Form\Filter\PersonalFilter;
use Application\Form\Order;
use Application\Form\Personal;
use Application\Form\SiteSettings;
use Application\Form\Thank;
use Application\Model\Auth;
use Application\Model\Blanks;
use Application\Model\Orders;
use Application\Model\Settings;
use Application\Model\SiteImages;
use Application\Model\SitePages;
use Application\Form\Page;
use Application\Model\Thanks;

class UserController extends BasicController{

   public function settingsAction(){
      $sm = $this->_sm;
      $post = $this->_post;
      $fm = $this->_fm;
      $form_personal = new Personal();
      $user_id = $this->_identity['id'];
      $model_auth = new Auth($sm);

      if(!empty($post['act'])){
         switch($post['act']){
            case 'edit':
               $form_personal->setData($post);
               $form_personal->setInputFilter(new PersonalFilter());
               if($form_personal->isValid()){
                  $model_auth->editUser($form_personal->getData());
                  $fm->addSuccessMessage('Изменения сохранены');
               }
               else{
                  $fm->addErrorMessage(
                     current(current($form_personal->getMessages()))
                  );
               }
               break;
         }
         $this->redirect()->refresh();
      }

      $user = $model_auth->getUser($user_id);
      $form_personal->populateValues($user);

      return [
         'form_personal' => $form_personal,
      ];
   }
   
   public function siteSettingsAction(){
      $sm = $this->_sm;
      $post = $this->_post;
      $get = $this->_get;
      $fm = $this->_fm;
      $model_settings = new Settings($sm);
      $user_id = $this->_identity['id'];
      $form_site_settings = new SiteSettings($sm, $user_id);
      $model_site_images = new SiteImages($sm);

      if(!empty($post['act'])){
         switch($post['act']){
            case 'save_settings':
               $form_site_settings->setData($post);
               if($form_site_settings->isValid()){
                  $model_settings->editSettings(
                     $form_site_settings->getData(), $user_id);
                  $fm->addSuccessMessage('Изменения сохранены');
               }
               else{
                  $fm->addErrorMessage(
                     current(current($form_site_settings->getMessages()))
                  );
               }
               break;
         }
         $this->redirect()->refresh();
      }
      if(!empty($get['act'])){
         switch($get['act']){
            case 'del_img': $model_site_images->deleteImage($get['id']);
               break;
         }
         $this->redirect()->refresh();
      }


      return [
         'settings' => $model_settings->getSettingsByUserId($user_id),
         'form_settings' => $form_site_settings,
      ];
   }
   
   public function kidContestAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $form_order = new Order($sm);
      $form_order->prepareForIndex('kid');
      $form_order->clearPlaceholders();
      $model_auth = new Auth($sm);
      $user = $model_auth->getUser();
      $form_order->populateValues($user);
      $form_order->get('chief_name')->setValue($user['name']);
      $form_order->remove('email');

      $model_blanks = new Blanks($sm);

      if(!empty($post['act'])){
         switch($post['act']){
            case 'add_order':
               $model_orders = new Orders($sm);
               $form_order->setData($post);
               $order_filter = new OrderFilter($sm);
               $order_filter->remove('email');
               $form_order->setInputFilter($order_filter);
               if($form_order->isValid()){
                  try{
                     $order_id =
                        $model_orders->addOrder($form_order->getData());
                     $fm->addSuccessMessage('Ваша заявка принята. '.
                        'На ваш адрес электронной почты отправлено письмо.'
                     );
                     return $post['kind'] == 'monthly'?
                        $this->redirect()->toRoute('home'):
                        $this->redirect()->toRoute('pay',
                        ['order_id' => $order_id, 'email' => $user['email']]);
                  }
                  catch(\Exception $e){
                     $fm->addErrorMessage($e->getMessage());
                  }
               }
               else{
                  $fm->addErrorMessage($form_order->getMessages());
               }
               break;
         }
         $this->redirect()->refresh();
      }

      return [
         'form_order' => $form_order,
         'blanks' => $model_blanks->getAll(true),
      ];
   }
   
   public function educatorContestAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $form_order = new Order($sm);
      $form_order->prepareForIndex('educator');
      $form_order->clearPlaceholders();
      $model_auth = new Auth($sm);
      $user = $model_auth->getUser();
      $form_order->populateValues($user);
      $form_order->get('executor_name')->setValue($user['name']);
      $form_order->remove('email');

      $model_blanks = new Blanks($sm);

      if(!empty($post['act'])){
         switch($post['act']){
            case 'add_order':
               $model_orders = new Orders($sm);
               $form_order->setData($post);
               $order_filter = new OrderFilter($sm);
               $order_filter->remove('email');
               $form_order->setInputFilter($order_filter);
               if($form_order->isValid()){
                  try{
                     $order_id =
                        $model_orders->addOrder($form_order->getData());
                     $fm->addSuccessMessage('Ваша заявка принята. '.
                        'На ваш адрес электронной почты отправлено письмо.'
                     );
                     return $post['kind'] == 'monthly'?
                        $this->redirect()->toRoute('home'):
                        $this->redirect()->toRoute('pay',
                        ['order_id' => $order_id, 'email' => $user['email']]);
                  }
                  catch(\Exception $e){
                     $fm->addErrorMessage($e->getMessage());
                  }
               }
               else{
                  $fm->addErrorMessage($form_order->getMessages());
               }
               break;
         }
         $this->redirect()->refresh();
      }

      return [
         'form_order' => $form_order,
         'blanks' => $model_blanks->getAll(true),
      ];
   }
   
   public function siteIndexAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $user_id = $this->_identity['id'];
      $model_site_pages = new SitePages($sm);
      $form_page = new Page();
      $page = $model_site_pages->getPageByType('index', $user_id);
      $form_page->populateValues($page);
      
      if(!empty($post['act'])){
         switch($post['act']){
            case 'save_page':
               $model_site_pages->savePage($post, 'index', $user_id);
               break;
         }
         $this->redirect()->refresh();
      }
      
      
      return [
         'form_page' => $form_page,
      ];
   }

   public function siteBlogAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $get = $this->_get;
      $user_id = $this->_identity['id'];
      $model_site_pages = new SitePages($sm);

      if(!empty($get['act'])){
         switch($get['act']){
            case 'delete': $model_site_pages->deletePage($get['id']);
               break;
         }
         $this->redirect()->refresh();
      }


      return [
         'pages' => $model_site_pages->getPageByType('blog', $user_id),
      ];
   }
   
   public function articleAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $form_site_pages = new Page();
      $user_id = $this->_identity['id'];
      $article_id = $this->params()->fromRoute('article_id');
      $model_site_pages = new SitePages($sm);
      if(!empty($article_id)){
         $page = $model_site_pages->getById($article_id);
         $form_site_pages->populateValues($page);
      }

      if(!empty($post['act'])){
         switch($post['act']){
            case 'save_page':
               $model_site_pages->savePage($post, 'blog', $user_id,
                  $article_id);
               break;
         }
         $this->redirect()->refresh();
      }
      
      
      return [
         'form_site_pages' => $form_site_pages,
         'is_edit' => !empty($article_id),
      ];
   }
   
   public function siteCommentsAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $get = $this->_get;
      $user_id = $this->_identity['id'];
      $model_site_pages = new SitePages($sm);

      if(!empty($get['act'])){
         switch($get['act']){
            case 'delete':
               $model_site_pages->deleteComment($get['id'], $user_id);
               break;
         }
         $this->redirect()->refresh();
      }


      return [
         'comments' => $model_site_pages->getCommentsByUserId($user_id),
      ];
   }

   public function thankAction(){
      $sm = $this->_sm;
      $fm = $this->_fm;
      $post = $this->_post;
      $form_thank = new Thank($sm);
      $model_blanks = new Blanks($sm);
      $model_thanks = new Thanks($sm);
      $model_auth = new Auth($sm);
      $user = $model_auth->getUser();
      $user['user_id'] = $user['id'];
      $form_thank->populateValues($user);

      if(!empty($post['act'])){
         switch($post['act']){
            case 'add_thank':
               $model_thanks->addThank($post);
               $fm->addSuccessMessage('Ваша заявка принята. '.
                  'На ваш адрес электронной почты отправлено письмо.'
               );
               break;
         }
         $this->redirect()->refresh();
      }

      return [
         'form_thank' => $form_thank,
         'blanks' => $model_blanks->getAll(true),
      ];
   }

}