<?php

namespace Application\Classes;

use Zend\Navigation\Service\AbstractNavigationFactory;

class SiteNavigationFactory extends AbstractNavigationFactory{


   protected function getName(){
      return 'site';
   }
}
