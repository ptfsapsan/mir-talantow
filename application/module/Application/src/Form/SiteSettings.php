<?php
namespace Application\Form;

use Application\Model\Settings;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;


class SiteSettings extends Form{
   public function __construct(ServiceManager $sm, $user_id){
      parent::__construct();

      $this
         ->add([
            'type' => 'hidden',
            'name' => 'act',
            'attributes' => [
               'value' => 'save_settings',
            ]
         ])
         ->add([
            'type' => 'text',
            'name' => 'color_1',
            'options' => [
               'label' => 'Цвет-1',
            ],
            'attributes' => [
               'class' => 'jscolor',
               'readonly' => true,
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'color_2',
            'options' => [
               'label' => 'Цвет-2',
            ],
            'attributes' => [
               'class' => 'jscolor',
               'readonly' => true,
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'title',
            'options' => [
               'label' => 'Заголовок сайта',
            ],
            'attributes' => [
               'class' => 'text_in',
            ]
         ])
         ->add([
            'type' => 'checkbox',
            'name' => 'blog',
            'options' => [
               'label' => 'Добавить блог на сайт?',
               'checked_value' => 'yes',
               'unchecked_value' => 'no',
            ],
            'attributes' => [
               'id' => 'blog',
            ],
         ])
         ->add([
            'type' => 'checkbox',
            'name' => 'blog_comments',
            'options' => [
               'label' => 'Добавить возможность оставить комментарии статей блога?',
               'checked_value' => 'yes',
               'unchecked_value' => 'no',
            ],
            'attributes' => [
               'id' => 'blog_comments',
            ],
         ])
         ->add([
            'type' => 'checkbox',
            'name' => 'contacts',
            'options' => [
               'label' => 'Добавить страничку обратной связи?',
               'checked_value' => 'yes',
               'unchecked_value' => 'no',
            ],
            'attributes' => [
               'id' => 'contacts',
            ],
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

      $model_settings = new Settings($sm);
      $settings = $model_settings->getSettingsByUserId($user_id);
      $this->populateValues($settings);
   }

}