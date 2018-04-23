<?php
namespace Application\Form;

use Zend\Form\Form;

class Forgot extends Form{
   public function __construct(){
      parent::__construct();

      $this
         ->add([
            'name' => 'act',
            'type' => 'hidden',
            'attributes' => [
               'value' => 'forgot',
            ],
         ])
         ->add([
            'name' => 'email',
            'type' => 'email',
            'options' => [
               'label' => 'Ваш e-mail*',
            ],
            'attributes' => [
               'required' => true,
            ],
         ])
         ->add([
            'name' => 'submit',
            'attributes' => [
               'type' => 'submit',
               'value' => 'Отправить',
               'class' => 'button',
            ],
         ])
      ;
   }
}