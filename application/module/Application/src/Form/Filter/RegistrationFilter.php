<?php

namespace Application\Form\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\StringLength;
use Zend\Validator\Db\NoRecordExists;
use Zend\Validator\Identical;

class RegistrationFilter extends InputFilter{
    public function __construct($sm){
        $this
            ->add(['name' => 'email',
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
                   new NoRecordExists([
                       'table' => 'users',
                       'field' => 'email',
                       'adapter' => $sm->get('dbAdapter'),
                       'messages' => [
                           NoRecordExists::ERROR_RECORD_FOUND =>
                              'Такой пользователь в системе уже зарегистрирован',
                       ],
                       'exclude' => [
                          'field' => 'password',
                          'value' => '',
                       ]
                   ]),
               ],
            ]
            )
           ->add(['name' => 'password',
                  'required' => true,
                  'filters' => [
                     ['name' => 'StringTrim'],
                  ],
                  'validators' => [
                     new StringLength([
                        'min' => 3,
                        'max' => 20,
                        'messages' => [
                           StringLength::TOO_SHORT =>
                              'Слишком короткий пароль',
                           StringLength::TOO_LONG =>
                              'Слишком длинный пароль',
                        ],
                     ]),
                  ],
              ]
           )
           ->add(['name' => 'password2',
                  'required' => true,
                  'filters' => [
                     ['name' => 'StringTrim'],
                  ],
                  'validators' => [
                     new Identical([
                        'token' => 'password',
                        'messages' => [
                           Identical::MISSING_TOKEN => 
                              'Пароли не совпадают'
                        ],
                     ])
                  ],
              ]
           )
        ;
    }
}