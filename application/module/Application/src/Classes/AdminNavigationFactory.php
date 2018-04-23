<?php

namespace Application\Classes;

use Zend\Navigation\Service\AbstractNavigationFactory;

/**
 * Default navigation factory.
 */
class AdminNavigationFactory extends AbstractNavigationFactory{


   protected function getName(){

      return 'admin';
   }
}
