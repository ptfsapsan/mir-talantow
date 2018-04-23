<?php
namespace Application\Form;

use Zend\Form\Form;

class Page extends Form{

   public function __construct(){
      parent::__construct();

      $this
         ->add([
            'name' => 'act',
            'type' => 'hidden',
            'attributes' => [
               'value' => 'save_page',
            ]
         ])
         ->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
               'label' => 'Заголовок страницы',
            ],
         ])
         ->add([
            'name' => 'text',
            'type' => 'textarea',
            'options' => [
               'label' => 'Содержание страницы',
            ],
         ])
         ->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
               'type' => 'submit',
               'value' => 'Сохранить',
               'class' => 'button',
            ],
         ])

      ;
   }

}