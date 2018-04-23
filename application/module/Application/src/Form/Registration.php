<?php
namespace Application\Form;

use Zend\Form\Form;

class Registration extends Form{
   public function __construct(){
      parent::__construct();

      $this
         ->add([
            'name' => 'act',
            'type' => 'hidden',
            'attributes' => [
               'value' => 'registration',
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
            'name' => 'password2',
            'type' => 'password',
            'options' => [
               'label' => 'Пароль еще раз*',
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