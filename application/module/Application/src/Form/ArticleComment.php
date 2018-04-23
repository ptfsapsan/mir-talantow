<?php

namespace Application\Form;

use Zend\Form\Form;


class ArticleComment extends Form{
   public function __construct(){
      parent::__construct();

      $this
         ->add([
            'name' => 'act',
            'type' => 'hidden',
            'attributes' => [
               'value' => 'add_comment',
            ],
         ])
         ->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
               'label' => 'Имя',
            ],
            'attributes' => [
               'required' => true,
            ],
         ])
         ->add([
            'name' => 'text',
            'type' => 'textarea',
            'options' => [
               'label' => 'Содержание комментария',
            ],
            'attributes' => [
               'required' => true,
            ],
         ])
         ->add([
            'name' => 'submit',
            'attributes' => [
               'type' => 'submit',
               'value' => 'Добавить комментарий',
               'class' => 'button',
            ],
         ])
      ;
   }


   public function prepareForEdit($comment){
      $this->add([
         'name' => 'status',
         'type' => 'select',
         'options' => [
            'label' => 'Статус',
            'value_options' => [
               'new' => 'Новый',
               'no_accepted' => 'Не подтвержденный',
               'accepted' => 'Подтвержденный',
            ],
            'value' => $comment['status'],
         ],
      ]);
      $this->populateValues($comment);
      $this->get('submit')->setValue('Сохранить изменения');
      $this->get('act')->setValue('edit_comment');
   }

}