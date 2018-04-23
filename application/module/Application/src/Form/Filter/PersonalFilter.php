<?php

namespace Application\Form\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\StringLength;

class PersonalFilter extends InputFilter{
    public function __construct(){
        $this
            ->add([
               'name' => 'email',
               'required' => true,
               'filters' => [
                   ['name' => 'StripTags'],
                   ['name' => 'StringTrim'],
               ],
               'validators' => [
                   new EmailAddress([
                      'messages' => [
                         EmailAddress::INVALID_FORMAT =>
                            'Неверный формат адреса электронной почты',
                         EmailAddress::INVALID_HOSTNAME =>
                            'Неверный формат адреса электронной почты',
                      ],
                   ]),
               ],
            ]
            )
           ->add([
              'name' => 'name',
              'required' => false,
              'filters' => [
                 ['name' => 'StripTags'],
                 ['name' => 'StringTrim'],
              ],
           ])
           ->add([
              'name' => 'post',
              'required' => false,
              'filters' => [
                 ['name' => 'StripTags'],
                 ['name' => 'StringTrim'],
              ],
           ])
           ->add([
              'name' => 'organization_name',
              'required' => false,
              'filters' => [
                  ['name' => 'StripTags'],
                  ['name' => 'StringTrim'],
              ],
              'validators' => [
                  new StringLength([
                     'max' => 200,
                     'messages' => [
                        StringLength::TOO_LONG =>
                           'Слишком длинное название организации',
                     ],
                  ]),
              ],
           ])
           ->add([
              'name' => 'organization_address',
               'required' => false,
               'filters' => [
                  ['name' => 'StripTags'],
                  ['name' => 'StringTrim'],
               ],
               'validators' => [
                  new StringLength([
                     'max' => 200,
                     'messages' => [
                        StringLength::TOO_LONG =>
                           'Слишком длинный адрес организации',
                     ],
                  ]),
               ],
           ])
        ;
    }
}