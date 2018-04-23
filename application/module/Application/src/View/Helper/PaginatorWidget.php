<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PaginatorWidget extends AbstractHelper{

   public function __invoke($params){
      $page = $params['page'];
      $count = $params['count'];
      $on_page = $params['on_page'];
      $pages = ceil($count / $on_page);
      if($pages < 1 || $pages == 1) return '';//$pages = 1;
      return $this->getView()->partial('application/helper/paginator',
         ['page' => $page, 'pages' => $pages, 'on_page' => $on_page]);
   }
}

?>
