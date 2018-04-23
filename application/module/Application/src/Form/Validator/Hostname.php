<?

namespace Application\Form\Validator;


class Hostname extends \Zend\Validator\Hostname{

   public function __construct($tr){

      $this->messageTemplates = array(
         self::CANNOT_DECODE_PUNYCODE  =>
            $tr->translate(self::CANNOT_DECODE_PUNYCODE),
         self::INVALID_DASH            =>
            $tr->translate(self::INVALID_DASH),
         self::INVALID_HOSTNAME_SCHEMA =>
            $tr->translate(self::INVALID_HOSTNAME_SCHEMA),
         self::INVALID_LOCAL_NAME      =>
            $tr->translate(self::INVALID_LOCAL_NAME),
         self::INVALID_URI             =>
            $tr->translate(self::INVALID_URI),
         self::IP_ADDRESS_NOT_ALLOWED  =>
            $tr->translate(self::IP_ADDRESS_NOT_ALLOWED),
         self::LOCAL_NAME_NOT_ALLOWED  =>
            $tr->translate(self::LOCAL_NAME_NOT_ALLOWED),
         self::UNDECIPHERABLE_TLD      =>
            $tr->translate(self::UNDECIPHERABLE_TLD),
         self::UNKNOWN_TLD             =>
            $tr->translate(self::UNKNOWN_TLD),
      );
      parent::__construct();
   }

}