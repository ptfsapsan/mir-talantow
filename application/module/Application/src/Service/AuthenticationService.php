<?php
namespace Application\Service;

use Zend\Authentication\AuthenticationService as Service;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Authentication\Adapter\AdapterInterface;

class AuthenticationService extends Service{
   
   public function __construct(StorageInterface $storage,
      AdapterInterface $adapter
   ){
      parent::__construct($storage, $adapter);
      return $this;
   }
}