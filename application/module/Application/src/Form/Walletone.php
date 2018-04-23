<?php
namespace Application\Form;

use Zend\Form\Form;
use Application\Model\Walletone as ModelWalletone;

class Walletone extends Form{
   public function __construct(){
      parent::__construct();

      $this
         ->setAttributes([
            'method' => 'post',
            'action' => 'https://wl.walletone.com/checkout/checkout/Index',
         ])
         ->add([
            'name' => 'WMI_MERCHANT_ID',
            'type' => 'hidden',
            'attributes' => [
               'value' => ModelWalletone::WMI_MERCHANT_ID,
            ]
         ])
         ->add([
            'name' => 'WMI_PAYMENT_AMOUNT',
            'type' => 'text',
            'attributes' => [
               'readonly' => true,
            ]
         ])
         ->add([
            'name' => 'WMI_CURRENCY_ID',
            'type' => 'hidden',
            'attributes' => [
               'value' => ModelWalletone::WMI_CURRENCY_ID,
            ]
         ])
         ->add([
            'name' => 'WMI_DESCRIPTION',
            'type' => 'hidden',
            'attributes' => [
               'value' => 'BASE64:'.
                  base64_encode(ModelWalletone::WMI_DESCRIPTION),
            ]
         ])
         ->add([
            'name' => 'WMI_SUCCESS_URL',
            'type' => 'hidden',
            'attributes' => [
               'value' => ModelWalletone::WMI_SUCCESS_URL,
            ]
         ])
         ->add([
            'name' => 'WMI_FAIL_URL',
            'type' => 'hidden',
            'attributes' => [
               'value' => ModelWalletone::WMI_FAIL_URL,
            ]
         ])
         ->add([
            'name' => 'WMI_PAYMENT_NO',
            'type' => 'number',
         ])
         ->add([
            'name' => 'WMI_SIGNATURE',
            'type' => 'hidden',
         ])
         ->add([
            'name' => 'wo_email',
            'type' => 'email',
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

   public function prepareForm($order){
      if(empty($order)) return;
      $this->get('WMI_PAYMENT_NO')->setValue($order['id']);
      $this->get('wo_email')->setValue($order['email']);
      $debt = number_format($order['price'] - $order['paid'], 2);
      $this->get('WMI_PAYMENT_AMOUNT')->setValue($debt);
   }


}