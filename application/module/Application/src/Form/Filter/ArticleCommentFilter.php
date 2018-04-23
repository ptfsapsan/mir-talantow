<?php

namespace Application\Form\Filter;

use Zend\InputFilter\InputFilter;

class ArticleCommentFilter extends InputFilter{

   public function __construct(){

      $this
         ->add([
               'name' => 'name',
               'required' => true,
               'filters' => [
                  ['name' => 'StripTags'],
                  ['name' => 'StringTrim'],
               ],
            ]
         )
         ->add([
               'name' => 'text',
               'required' => true,
               'filters' => [
                  ['name' => 'StripTags'],
                  ['name' => 'StringTrim'],
               ],
            ]
         );
   }
}