<?php
namespace Application\Controller;

use Application\Model\Articles;
use Application\Model\Files;
use Application\Model\Nominations;
use Application\Model\Orders;
use Application\Model\SitePages;
use Application\Model\TempFiles;
use Application\Model\Themes;
use Zend\Http\Headers;
use Zend\Http\Response\Stream;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Auth;
use Zend\Mvc\MvcEvent;


class ServiceController extends AbstractActionController{
   
   private $_sm;

   public function onDispatch(MvcEvent $e){
      $this->_sm = $e->getApplication()
         ->getServiceManager();
      parent::onDispatch($e);
   }

   public function logoutAction(){
      $sm = $this->_sm;
      $auth = new Auth($sm);
      $auth->logout();
      return $this->redirect()->toRoute('home');
   }

   public function robotsAction(){
      header('Content-Type: text/plain');
      $view = new ViewModel();
      return $view->setTerminal(true);
   }

   public function sitemapAction(){
      $sm = $this->_sm;
      $model_nominations = new Nominations($sm);
      $model_themes = new Themes($sm);
      $model_articles = new Articles($sm);
      $model_orders = new Orders($sm);
      $model_site_pages = new SitePages($sm);
      
      header('Content-Type: text/xml');
      $view = new ViewModel();
      return $view->setTerminal(true)->setVariables([
         'kid_nominations' => $model_nominations->getAll(),
         'educator_nominations' => $model_nominations->getAll('educator'),
         'themes' => $model_themes->getAll(),
         'article_themes' => $model_articles->getAllThemes(),
         'articles' => $model_articles->getArticlesForSitemap(),
         'gallery' => $model_orders->getForSitemap(),
         'blogs' => $model_site_pages->getAllBlogPages(),
      ]);
   }
   
   public function fileContentAction(){
      $id = $this->params()->fromRoute('id', 0);
      $type = $this->params()->fromRoute('type', '');
      $sm = $this->_sm;
      
      if(empty($type)){
         $model_files = new Files($sm);
         $file = $model_files->getFileById($id);
         $name = FILES.$file['dir'].$file['name'];
      }
      else{
         $model_files = new TempFiles($sm);
         $file = $model_files->getById($id);
         $name = ROOT_DIR.'/files/temp_files/'.$file['phpsessid'].'/'
            .$file['name'];
      }
      $response = new Stream();
      $response->setStream(fopen($name, 'r'));
      $response->setStatusCode(200);
      $response->setStreamName($file['name']);

      $headers = new Headers();
      $headers->addHeaders([
         'Content-Disposition' => 'attachment; filename="'.$file['name'].'"',
         'Content-Type' => $file['mime_type'],
         'Content-Length' => $file['size'],
      ]);
      return $response->setHeaders($headers);
   }
   
   public function changeVersionAction(){

   }

   public function loadFileAction(){
      $sm = $this->_sm;
      $id = $this->params()->fromRoute('id');
      $model_files = new Files($sm);
      $file = $model_files->getFileById($id);

      if(empty($file) || $file['in_gallery'] != 'yes'){
         $this->redirect()->toUrl('/');
      };

      $f = FILES.$file['dir'].$file['name'];

      header('Content-Description: File Transfer');
      header('Content-Disposition: attachment; filename="'.$file['name'].'"');
      header('Content-Type: '.pathinfo(basename($file['name']),
            PATHINFO_EXTENSION));
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($f));
      // читаем файл и отправляем его пользователю
      if ($fd = fopen($f, 'rb')) {
         while (!feof($fd)) {
            print fread($fd, 1024);
         }
         fclose($fd);
      }
      exit();
   }

   

}
