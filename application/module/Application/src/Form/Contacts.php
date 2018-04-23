<?php

namespace Application\Form;

use Zend\Form\Form;

class Contacts extends Form{
    public function __construct(){
        parent::__construct();

        $this
            ->add([
               'name' => 'act',
               'type' => 'hidden',
               'attributes' => [
                   'value' => 'contacts',
               ],
            ])
            ->add([
                'name' => 'name',
                'type' => 'text',
                'options' => [
                    'label' => 'Ваше имя*',
                ],
                'attributes' => [
                    'required' => true,
                ],
            ])
            ->add([
                'name' => 'email',
                'type' => 'email',
                'options' => [
                    'label' => 'Ваш e-mail*',
                ],
                'attributes' => [
                    'required' => true,
                ],
            ])
            ->add([
                'name' => 'message',
                'type' => 'textarea',
                'options' => [
                    'label' => 'Ваше сообщение*',
                ],
                'attributes' => [
                    'required' => true,
                ],
            ])
           ->add([
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Отправить',
                    'class' => 'button',
                ],
            ])
        ;
    }
}