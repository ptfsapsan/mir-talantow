<?php
namespace Application\Form\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\StringLength;

/**
 * Class OrderFilter
 *
 * @package Application\Form\Filter
 */
class OrderFilter extends InputFilter{

   /**
    * OrderFilter constructor.
    *
    * @param $sm
    */
   public function __construct($sm){
      $this
         ->add([
            'name' => 'chief_name',
            'required' => false,
            'filters' => [
               ['name' => 'StripTags'],
               ['name' => 'StringTrim'],
            ]
         ])
         ->add([
            'name' => 'executor_name',
            'required' => true,
            'filters' => [
               ['name' => 'StripTags'],
               ['name' => 'StringTrim'],
            ]
         ])
         ->add([
            'name' => 'executor_age',
            'required' => false,
            'filters' => [
               ['name' => 'StripTags'],
               ['name' => 'StringTrim'],
            ]
         ])
         ->add([
            'name' => 'organization_name',
            'required' => false,
            'filters' => [
               ['name' => 'StripTags'],
               ['name' => 'StringTrim'],
            ]
         ])
         ->add([
            'name' => 'email',
            'continue_if_empty' => true,
            'filters' => [
               ['name' => 'StripTags'],
               ['name' => 'StringTrim'],
            ],
            'validators' => [
               [
                  'name' => 'StringLength',
                  'options' => [
                     'min' => 1,
                     'messages' => [
                        StringLength::TOO_SHORT =>
                           'Не введен адрес электронной почты'
                     ]
                  ],
               ],
               [
                  'name' => 'EmailAddress',
                  'options' => [
                     'messages' => [
                        EmailAddress::INVALID_FORMAT =>
                           'Неверный формат адреса электронной почты',
                        EmailAddress::INVALID_HOSTNAME =>
                           'Неверный формат адреса электронной почты',
                     ],
                  ],
               ],
            ],
         ])
         ->add([
            'name' => 'organization_address',
            'required' => false,
            'filters' => [
               ['name' => 'StripTags'],
               ['name' => 'StringTrim'],
            ]
         ])
         ->add([
            'name' => 'work_title',
            'required' => true,
            'filters' => [
               ['name' => 'StripTags'],
               ['name' => 'StringTrim'],
            ]
         ])


      ;
   }
}