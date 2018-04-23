<?php

namespace Application\Form;

use Zend\Form\Form;


class Article extends Form{
   public function __construct(){
      parent::__construct();

      $this
         ->add([
            'name' => 'act',
            'type' => 'hidden',
            'attributes' => [
               'value' => 'add_article',
            ],
         ])
         ->add([
            'name' => 'article_theme_id',
            'type' => 'select',
            'options' => [
               'label' => 'Тема статьи',
            ],
         ])
         ->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
               'label' => 'Название статьи (до 200 символов)*',
            ],
            'attributes' => [
               'required' => true,
            ],
         ])
         ->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
               'label' => 'ФИО автора *',
            ],
            'attributes' => [
               'required' => true,
            ],
         ])
         ->add([
            'name' => 'email',
            'type' => 'email',
            'options' => [
               'label' => 'E-mail автора *',
            ],
            'attributes' => [
               'required' => true,
            ],
         ])
         ->add([
            'name' => 'post',
            'type' => 'text',
            'options' => [
               'label' => 'Должность автора',
            ],
         ])
         ->add([
            'name' => 'organization_name',
            'type' => 'text',
            'options' => [
               'label' => 'Название организации (до 200 символов)',
            ],
         ])
         ->add([
            'name' => 'organization_address',
            'type' => 'text',
            'options' => [
               'label' => 'Адрес организации (до 200 символов)',
            ],
         ])
         ->add([
            'name' => 'text',
            'type' => 'textarea',
            'options' => [
               'label' => 'Текст статьи *',
            ],
         ])
//         ->add([
//            'name' => 'original',
//            'type' => 'checkbox',
//            'options' => [
//               'checked_value' => 'yes',
//               'unchecked_value' => 'no',
//               'label' =>
//                  'Хотите, чтобы мы выслали оригинал сертификата по почте?',
//            ],
//            'attributes' => [
//               'value' => 'no',
//               'id' => 'original',
//            ],
//         ])
         ->add([
            'name' => 'certificate_id',
            'type' => 'hidden',
         ])
         ->add([
            'name' => 'submit',
            'attributes' => [
               'type' => 'submit',
               'value' => 'Добавить статью',
               'class' => 'button',
            ],
         ])
      ;
   }

   public function prepareForm($themes){
      $themes = array_column($themes, 'title', 'id');
      $this->get('article_theme_id')->setOptions([
         'value_options' => $themes
      ]);
   }
   
   public function prepareForEdit($article){
      $this->add([
         'name' => 'status',
         'type' => 'select',
         'options' => [
            'value_options' => [
               'new' => 'Новый',
               'no_accepted' => 'Не подтверждено',
               'accepted' => 'Подтверждено',

            ],
            'label' => 'Статус статьи',
         ],
      ]);
      $this->setPriority('status', 1);

      $this->populateValues($article);
      $this->get('certificate_id')->setValue($article['certificate_id']);
      $this->get('submit')->setValue('Сохранить изменения');
      $this->get('act')->setValue('edit_article');
   }

}