<?php

namespace Application\Model;

use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter as AuthAdapter;
use Zend\ServiceManager\ServiceManager;
use Zend\Authentication\AuthenticationService;

class Auth extends Base{

   protected $_sm;
   private $_auth;

   public function __construct(ServiceManager $sm){
      parent::__construct($sm);
      $this->_sm = $sm;
      $this->_auth = new AuthenticationService();
   }

   public function login($params){
      if(!$this->isEmpty()) $this->logout();
      $sm = $this->_sm;
      $authAdapter = new AuthAdapter(
          $sm->get('dbAdapter'),
         'users',
         'email',
         'password'
      );
      $authAdapter
         ->setIdentity($params['email'])
         ->setCredential(Crypt::encrypt($params['password']));

      $auth = $this->_auth;
      $auth->setAdapter($authAdapter);
      $result = $auth->authenticate($authAdapter);

      if(!$result->isValid()) throw new \Exception('Неверный пароль');

      $data = (array)$authAdapter->getResultRowObject(null, ['password']);
      if($data['active'] == 'no') $this->logout();
      else $auth->getStorage()->write($data);
   }

   public function isEmpty(){
      return !$this->_auth->hasIdentity();
   }

   public function logout(){
      if($this->isEmpty()) return;
      $this->_auth->clearIdentity();
   }

   public function getIdentity(){
      return $this->isEmpty()? false: $this->_auth->getIdentity();
   }
   
   public function forgot($email){
      $password = $this->fetchOne("SELECT password FROM users WHERE email = ?",
         $email);
      $password = Crypt::decrypt($password);
      $model_mail = new Mail($this->_sm);
      $subject = 'Восстановление пароля на сайте Мир Талантов';
      $model_mail->sendView($email, $subject, 'forgot',
         ['password' => $password]);
   }

   public function registration($params){
      $temp_code = md5(bin2hex(Crypt::encrypt(substr(md5(rand()), 5, 10))));
      $d = [
         'email' => $params['email'],
         'password' => Crypt::encrypt($params['password']),
         'role' => 'user',
         'temp_code' => $temp_code,
      ];
      $a = $this->fetchRow("SELECT * FROM users WHERE email = ?",
         $params['email']);
      if(empty($a)) $this->insert('users', $d);
      else $this->update('users', $d, "id = {$a['id']}");

      $model_mail = new Mail($this->_sm);
      $subject = 'Активация аккаунта на сайте Мир Талантов';
      $model_mail->sendView($params['email'], $subject, 'active_registration',
         ['temp_code' => $temp_code]);
   }

   public function activeRegistration($temp_code){
      $user = $this->fetchRow("SELECT * FROM users WHERE temp_code = ?",
         $temp_code);
      if(empty($user)) throw new \Exception('Устаревшая ссылка');

      $user_id = $user['id'];
      $this->update('users', [
         'active' => 'yes',
         'temp_code' => '',
      ], "id = $user_id");
      $this->update('orders', ['user_id' => $user_id],
         "email = '{$user['email']}'");

      $f = true;
      $site_code = 0;
      while($f){
         $site_code = self::generateDigitCode(6);
         $f = $this->fetchRow("SELECT * FROM settings WHERE site_code = ?",
            $site_code);
      }
      $this->insert('settings', [
         'user_id' => $user['id'],
         'site_code' => $site_code,
      ]); 
      $this->login([
         'email' => $user['email'],
         'password' => Crypt::decrypt($user['password']),
      ]);
   }

   public function getUser($user_id = null){
      if(empty($user_id)) $user_id = $this->getUserId();
      return $this->fetchRow("SELECT * FROM users WHERE id = ?", $user_id);
   }
   
   public function editUser($params){
      unset($params['act']);
      unset($params['submit']);
      unset($params['email']);
      $user_id = $this->getUserId();
      $this->update('users', $params, "id = $user_id");
   }
   
   public function getUserId(){
      if($this->isEmpty()) return 0;
      return $this->getIdentity()['id'];
   }



}