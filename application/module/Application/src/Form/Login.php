<?php
namespace Application\Form;

use Zend\Form\Form;

class Login extends Form{
   public function __construct(){
      parent::__construct();

      $this
         ->add([
            'name' => 'act',
            'type' => 'hidden',
            'attributes' => [
               'value' => 'login',
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
            'name' => 'password',
            'type' => 'password',
            'options' => [
               'label' => 'Пароль*',
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