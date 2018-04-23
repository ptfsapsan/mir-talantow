<?php

namespace Application\Form;

use Zend\Form\Form;

class SiteComment extends Form{
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
                    'label' => 'Ваше имя*',
                ],
                'attributes' => [
                    'required' => true,
                ],
            ])
            ->add([
                'name' => 'message',
                'type' => 'textarea',
                'options' => [
                    'label' => 'Ваш комментарий*',
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