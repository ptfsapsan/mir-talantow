<?php
namespace Application\Form;

use Zend\Form\Form;

/**
 * Class Thank
 *
 * @package Application\Form
 */
class Thank extends Form{


   /**
    * Thank constructor.
    *
    */
   public function __construct(){
      parent::__construct();

      $this
         ->add([
            'type' => 'hidden',
            'name' => 'act',
            'attributes' => [
               'value' => 'add_thank',
            ]
         ])
         ->add([
            'type' => 'hidden',
            'name' => 'user_id',
         ])
         ->add([
            'type' => 'hidden',
            'name' => 'email',
         ])
         ->add([
            'type' => 'hidden',
            'name' => 'blank_id',
            'attributes' => [
               'id' => 'blank_hidd',
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'name',
            'options' => [
               'label' => 'ФИО, кому выдается благодарность в дательном падеже',
            ],
            'attributes' => [
               'placeholder' => 'Ивановой Марии Ивановне',
               'required' => true,
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'post',
            'options' => [
               'label' => 'Должность в дательном падеже',
            ],
            'attributes' => [
               'placeholder' => 'воспитателю',
               'required' => true,
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'for_what',
            'options' => [
               'label' => 'За что выражается благодарность',
            ],
            'attributes' => [
               'placeholder' => 'За подготовку к участию в конкурсе',
               'required' => true,
            ],
         ])
         ->add([
            'type' => 'textarea',
            'name' => 'organization_name',
            'options' => [
               'label' => 'Название организации (до 200 символов)'
            ],
            'attributes' => [
               'placeholder' => 'МДОУ №218 г.Москва',
            ],
         ])
         ->add([
            'type' => 'textarea',
            'name' => 'organization_address',
            'options' => [
               'label' => 'Адрес организации (до 200 символов)'
            ],
            'attributes' => [
               'placeholder' => '',
            ],
         ])

         ->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
               'value' => 'Создать заявку',
               'class' => 'button',
            ]
         ])

      ;
   }



}