<?

namespace Application\Form\Validator;


class EmailAddress extends \Zend\Validator\EmailAddress{

   public function __construct($tr){

      $this->messageTemplates = array(
         self::INVALID => $tr->translate(self::INVALID),
         self::INVALID_FORMAT => $tr->translate(self::INVALID_FORMAT),
         self::INVALID_HOSTNAME => $tr->translate(self::INVALID_HOSTNAME),
         self::INVALID_MX_RECORD => $tr->translate(self::INVALID_MX_RECORD),
         self::INVALID_SEGMENT => $tr->translate(self::INVALID_SEGMENT),
         self::DOT_ATOM => $tr->translate(self::DOT_ATOM),
         self::QUOTED_STRING => $tr->translate(self::QUOTED_STRING),
         self::INVALID_LOCAL_PART => $tr->translate(self::INVALID_LOCAL_PART),
         self::LENGTH_EXCEEDED => $tr->translate(self::LENGTH_EXCEEDED),
      );
      $this->setHostnameValidator(
         new \Application\Form\Validator\Hostname($tr));
      parent::__construct();
   }

}