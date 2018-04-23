<?php
namespace Application\Form;

use Zend\Form\Form;

class Questions extends Form{

   public function __construct($sm){
      parent::__construct();

      $this
         ->add([
            'type' => 'hidden',
            'name' => 'act',
            'attributes' => [
               'value' => 'add_question',
            ],
         ])
         ->add([
            'type' => 'hidden',
            'name' => 'type',
         ])
         ->add([
            'type' => 'email',
            'name' => 'email',
            'options' => [
               'label' => 'Если вы хотите получить ответ на электронный 
               адрес, введите сюда свой email',
            ],
            'attributes' => [
               'required' => false,
            ],
         ])
         ->add([
            'type' => 'textarea',
            'name' => 'question',
            'options' => [
               'label' => 'Ваш вопрос',
            ],
            'attributes' => [
               'style' => 'width: 100%',
               'required' => true,
            ],
         ])
         ->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
               'value' => 'Отправить',
            ]

         ])

      ;
   }
}