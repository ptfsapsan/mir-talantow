<?php
namespace Application\Form;

use Application\Model\Nominations;
use Application\Model\Themes;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;

/**
 * Class Order
 *
 * @package Application\Form
 */
class Order extends Form{

   /**
    * @var int|null|string
    */
   private $_sm;

   /**
    * Order constructor.
    *
    * @param ServiceManager $sm
    */
   public function __construct(ServiceManager $sm){
      parent::__construct();
      $this->_sm = $sm;

      $this
         ->add([
            'type' => 'hidden',
            'name' => 'act',
            'attributes' => [
               'value' => 'add_order',
            ]
         ])
         ->add([
            'type' => 'hidden',
            'name' => 'type',
            'attributes' => [
               'value' => 'kid',
            ]
         ])
         ->add([
            'type' => 'hidden',
            'name' => 'kind',
            'attributes' => [
               'id' => 'kind_hidd',
            ],
         ])
         ->add([
            'type' => 'hidden',
            'name' => 'blank_id',
            'attributes' => [
               'id' => 'blank_hidd',
            ],
         ])
         ->add([
            'type' => 'select',
            'name' => 'nomination_id',
            'options' => [
               'label' => 'Номинация',
            ]
         ])
         ->add([
            'type' => 'select',
            'name' => 'theme_id',
            'options' => [
               'label' => 'Тема',
            ]
         ])
         ->add([
            'type' => 'text',
            'name' => 'chief_name',
            'options' => [
               'label' => 'ФИО руководителя',
            ],
            'attributes' => [
               'placeholder' => 'Иванов Иван Иванович',
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'executor_name',
            'options' => [
               'label' => 'Имя исполнителя (до 100 символов)*'
            ],
            'attributes' => [
               'placeholder' => 'Перов Ваня',
               'required' => true
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'executor_age',
            'options' => [
               'label' => 'Возраст исполнителя',
            ],
            'attributes' => [
               'placeholder' => '7 лет',
            ],
         ])
         ->add([
            'type' => 'text',
            'name' => 'work_title',
            'options' => [
               'label' => 'Название работы (до 200 символов)*',
            ],
            'attributes' => [
               'placeholder' => 'День Победы',
               'required' => true,
            ],
         ])
         ->add([
            'type' => 'email',
            'name' => 'email',
            'options' => [
               'label' => 'Адрес электронной почты (до 100 символов)*',
            ],
            'attributes' => [
               'placeholder' => 'info@mail.ru',
               'required' => true
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
            'type' => 'checkbox',
            'name' => 'agree',
            'options' => [
               'checked_value' => 'yes',
               'unchecked_value' => 'no',
               'label' =>
                  'Вы согласны, чтобы данная работа украсила нашу галерею?',
            ],
            'attributes' => [
               'value' => 'no',
            ],
         ])
//         ->add([
//            'type' => 'checkbox',
//            'name' => 'original',
//            'options' => [
//               'checked_value' => 'yes',
//               'unchecked_value' => 'no',
//               'label' =>
//                  'Хотите, чтобы мы выслали оригинал диплома по почте?',
//            ],
//            'attributes' => [
//               'value' => 'no',
//            ],
//         ])

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


   /**
    * @param $type
    * @param $nomination
    * @param $theme
    * @param array $data
    */
   public function prepareForIndex($type, $nomination = null, $theme = null,
      array $data = []){
      $sm = $this->_sm;
      $model_nominations = new Nominations($sm);
      $nominations = $model_nominations->getAll($type);
      $this->get('nomination_id')
         ->setValueOptions(array_column($nominations, 'title', 'id'))
      ;
      if(count($data)) $this->populateValues($data);
      if(!empty($nomination))
         $this->get('nomination_id')->setValue($nomination['id']);
      
      if($type == 'kid'){
         $model_themes = new Themes($sm);
         $themes = $model_themes->getAll($type);
         $this->get('theme_id')
            ->setValueOptions(array_column($themes, 'title', 'id'))
            ->setValue($theme['id'])
         ;
      }
      else{
         $this->remove('theme_id');
         $this->remove('chief_name');
         $this->remove('executor_age');
         $this->get('type')->setValue('educator');
         $this->get('executor_name')
            ->setAttribute('placeholder', 'Иванова Марья Ивановна');
         $this->add([
            'type' => 'text',
            'name' => 'post',
            'options' => [
               'label' => 'Должность'
            ],
            'attributes' => [
               'placeholder' => 'Воспитатель',
            ],
         ]);
         $this
            ->setPriority('nomination_id', -1)
            ->setPriority('work_title', -2)
            ->setPriority('executor_name', -3)
            ->setPriority('post', -4)
            ->setPriority('email', -5)
            ->setPriority('organization_name', -6)
            ->setPriority('organization_address', -7)
            ->setPriority('agree', -8)
//            ->setPriority('original', -9)
            ->setPriority('submit', -10)
         ;
      }
   }


   /**
    * @param $type
    */
   public function prepareForEdit($type){
      $sm = $this->_sm;
      $this->get('submit')->setValue('Сохранить');
      $this->get('agree')->setLabel('Поместить работу в галерею?');
      $this->get('act')->setValue('edit_order');
      $this->add([
         'type' => 'hidden',
         'name' => 'competition_id',
      ]);

      $model_nominations = new Nominations($sm);
      $nominations = $model_nominations->getAll($type);
      $this->get('nomination_id')
         ->setValueOptions(array_column($nominations, 'title', 'id'));

      if($type == 'kid'){
         $model_themes = new Themes($sm);
         $themes = $model_themes->getAll($type);
         $this->get('theme_id')
            ->setValueOptions(array_column($themes, 'title', 'id'));
      }

      $this->clearPlaceholders();
   }
   
   public function clearPlaceholders(){
      foreach($this as $el) $el->setAttribute('placeholder', '');
   }



}