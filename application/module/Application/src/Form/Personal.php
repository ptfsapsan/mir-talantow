<?php
namespace Application\Form;

use Zend\Form\Form;


class Personal extends Form{
   public function __construct(){
      parent::__construct();

      return $this
         ->add([
            'type' => 'hidden',
            'name' => 'act',
            'attributes' => [
               'value' => 'edit',
            ]
         ])
         ->add([
            'type' => 'email',
            'name' => 'email',
            'attributes' => [
               'readonly' => true,
            ],
            'options' => [
               'label' => 'Ваш email (логин)',
            ]
         ])
         ->add([
            'type' => 'text',
            'name' => 'name',
            'options' => [
               'label' => 'ФИО',
            ]
         ])
         ->add([
            'type' => 'text',
            'name' => 'post',
            'options' => [
               'label' => 'Должность',
            ]
         ])
         ->add([
            'type' => 'text',
            'name' => 'organization_name',
            'options' => [
               'label' => 'Название организации',
            ]
         ])
         ->add([
            'type' => 'text',
            'name' => 'organization_address',
            'options' => [
               'label' => 'Адрес организации',
            ]
         ])
         ->add([
            'name' => 'submit',
            'attributes' => [
               'type' => 'submit',
               'value' => 'Сохранить',
               'class' => 'button',
            ],
         ])

      ;
   }

}