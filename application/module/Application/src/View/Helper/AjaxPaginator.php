<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class AjaxPaginator extends AbstractHelper{

   public function __invoke($params){
      $page = $params['page'];
      $count = $params['count'];
      $on_page = $params['on_page'];
      $pages = ceil($count / $on_page);
      if($pages < 1 || $pages == 1) return '';
      return $this->getView()->partial('application/helper/ajax-paginator',
         ['page' => $page, 'pages' => $pages, 'on_page' => $on_page]);
   }
}

?>
