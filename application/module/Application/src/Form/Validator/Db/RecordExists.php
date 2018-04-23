<?

namespace Application\Form\Validator\Db;


class RecordExists extends \Zend\Validator\Db\RecordExists{

   public function __construct($tr, $options){

      $this->messageTemplates = array(
         self::ERROR_NO_RECORD_FOUND =>
            $tr->translate(self::ERROR_NO_RECORD_FOUND),
         self::ERROR_RECORD_FOUND =>
            $tr->translate(self::ERROR_RECORD_FOUND),
      );
      parent::__construct($options);
   }

}