<?php

namespace Application\Form\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;

class ContactsFilter extends InputFilter{
    
    public function __construct(){
    
        $this
            ->add([
                'name' => 'name',
                'required' => true,
                'filters' => [
                   ['name' => 'StripTags'],
                   ['name' => 'StringTrim'],
                ],
            ])
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
            ])
            
        ;
    }
}