<?

namespace Application\Form\Validator;


class Identical extends \Zend\Validator\Identical{

   public function __construct($tr, $options){

      $this->messageTemplates = array(
         self::NOT_SAME => $tr->translate(self::NOT_SAME),
         self::MISSING_TOKEN => $tr->translate(self::MISSING_TOKEN),
      );
      parent::__construct($options);
   }

}