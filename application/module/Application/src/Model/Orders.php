<?php
namespace Application\Model;

use Application\Classes\Instagram;
use Application\Classes\Vkontakte;
use Zend\Session\SessionManager;

/**
 * Class Orders
 *
 * @package Application\Model
 */
class Orders extends Base{

   /**
    *
    */
   const MONTHLY_PRICE = 0;
   /**
    *
    */
   const URGENT_PRICE = 150;
   /**
    *
    */
   const FAST_PRICE = 100;

   /**
    * стоимость отправки оригинала диплома
    */
   const ORIGINAL_PRICE = 300;

   /**
    * @var array
    */
   private static $_kinds = [
      'monthly' => 'Ежемесячный',
      'fast' => 'Быстрый',
      'urgent' => 'Срочный',
   ];
   /**
    * @var array
    */
   private static $_types = [
      'kid' => 'Конкурсы для детей',
      'educator' => 'Конкурсы для педагогов',
   ];

   /**
    * @var array
    */
   private static $_status = [
      'new' => 'Новый',
      'paid' => 'Оплаченный',
      'sent' => 'Отправленный',
      'archive' => 'В архиве',
      'deleted' => 'Удаленный',
   ];

   /**
    * @return array
    */
   public static function getKinds(){
      return self::$_kinds;
   }

   /**
    * @return array
    */
   public static function getTypes(){
      return self::$_types;
   }

   /**
    * @return array
    */
   public static function getStatus(){
      return self::$_status;
   }

   /**
    * @param $kind
    *
    * @return int
    * @throws \Exception
    */
   private static function getPrice($kind){
      switch($kind){
         case 'monthly':
            return self::MONTHLY_PRICE;
         case 'fast':
            return self::FAST_PRICE;
         case 'urgent':
            return self::URGENT_PRICE;
         default:
            throw new \Exception(
               'Не удалось определить величину орг. взноса'
            );
      }
   }

   /**
    * добавляем заказ
    *
    * @param $params
    *
    * @throws \Exception
    * @return int
    */
   public function addOrder($params){
      $sm = $this->_sm;
      $sess_manager = new SessionManager();
      $phpsessid = $sess_manager->getId();
      $model_temp_files = new TempFiles($sm);
      $files = $model_temp_files->getFiles($phpsessid);
      $model_auth = new Auth($sm);
      $is_auth = !$model_auth->isEmpty();
      $user = null;
      if($is_auth){
         $user = $model_auth->getUser();
         $email = $user['email'];
      }
      else $email = $params['email'];

      $v = new \Zend\Validator\Uri();
      $links = [];
      if(!empty($params['link']) && count($params['link']))
         foreach($params['link'] as $i)
            if(!empty($i) && $v->isValid($i)) $links[] = $i;

      if(!count($files) && !count($links))
         throw new \Exception(
            'Не загружен ни один файл и не введен ни один линк');

//      $price = self::getPrice($params['kind']) +
//         ($params['original'] == 'yes'? self::ORIGINAL_PRICE: 0);
      $price = self::getPrice($params['kind']);
      $discount = 0;
      if($price > 0){
         $last_discount = (int)$this->fetchOne("SELECT MAX(discount) FROM orders
            WHERE email = ? AND ROUND(paid) >= ROUND(price) AND
             ROUND(price) > 0", $email);
         $discount = $last_discount >= 20? 20: $last_discount + 1;
         $price = $price * (100 - $discount) / 100;
      }

      $d = [
         'date' => date('c'),
         'kind' => $params['kind'],
         'type' => $params['type'],
         'executor_name' => $params['executor_name'],
         'blank_id' => $params['blank_id'],
         'nomination_id' => $params['nomination_id'],
         'work_title' => $params['work_title'],
         'email' => $email,
         'organization_name' => $params['organization_name'],
         'organization_address' => $params['organization_address'],
         'price' => round($price, 2),
         'discount' => $discount,
         'agree' => $params['agree'],
      ];
      if($params['type'] == 'kid'){
         $d['theme_id'] = $params['theme_id'];
         $d['executor_age'] = $params['executor_age'];
         $d['chief_name'] = $params['chief_name'];
      }
      else $d['post'] = $params['post'];

      if($is_auth) $user_id = $user['id'];
      else{
         $user_id = $this->fetchRow("SELECT id FROM users WHERE email = ?",
            $email);
         if(empty($user_id)){
            $this->insert('users', ['email' => $email]);
            $user_id = $this->lastInsertId();
         }
      }
      $d['user_id'] = empty($user_id)? 0: $user_id;

      $this->insert('orders', $d);
      $order_id = $this->lastInsertId();
      
      $f = true;
      $code = '';
      while($f){
         $code = strtolower(self::generateCode(10));
         $f = (bool) $this->fetchRow("SELECT * FROM orders WHERE code = ?",
            $code);
      }
      $this->update('orders', ['code' => $code], "id = $order_id");


      // линки
      if(count($links)) foreach($links as $link)
         $this->insert('links', [
            'order_id' => $order_id,
            'link' => $link,
         ]);

      // файлы
      $dir_source = FILES . '/temp_files/'.
         ($is_auth? $model_auth->getUserId(): $phpsessid).'/';
      $dir = Images::getDirById($order_id);
      $dir_target = FILES . $dir;
      if(!file_exists($dir_target) || !is_dir($dir_target))
         mkdir($dir_target, 0777, true);

      foreach($files as $file){
         rename($dir_source.$file['name'], $dir_target.$file['name']);
         if(!empty($file['thumb']))
            rename($dir_source.$file['thumb'], $dir_target.$file['thumb']);
         $this->insert('files', [
            'order_id' => $order_id,
            'date' => date('c'),
            'mime_type' => $file['mime_type'],
            'name' => $file['name'],
            'thumb' => $file['thumb'],
            'size' => $file['size'],
            'dir' => $dir,
            'in_gallery' => $params['agree'],
         ]);
         $this->delete('temp_files', "id = {$file['id']}");
      }
      Images::removeDir($dir_source);
      
      $model_mail = new Mail($this->_sm);
      $site_name = $this->getConfig()['site_name'];
      $submit = "Заявка на конкурс на сайте $site_name";
      try{
         $model_mail->sendView($email, $submit, 'new_order',
            $this->getById($order_id)
         );
      }
      catch(\Exception $e){
         throw new \Exception($e->getMessage());
      }

      if($price == 0) $this->changePaid($order_id);

      return $order_id;
   }

   /**
    * @param $session
    *
    * @return array
    */
   public function getAll($session){
      $where = "WHERE 1";
      if(!empty($session->type) &&
         in_array($session->type, array_keys(self::$_types)))
         $where .= " AND `type` = '{$session->type}'";
      if(!empty($session->kind) &&
         in_array($session->kind, array_keys(self::$_kinds)))
         $where .= " AND `kind` = '{$session->kind}'";
      if(!empty($session->nomination))
         $where .= " AND `nomination_id` = '{$session->nomination}'";
      if(!empty($session->theme))
         $where .= " AND `theme_id` = '{$session->theme}'";
      if(!empty($session->status) &&
         in_array($session->status, array_keys(self::$_status)))
         $where .= " AND `status` = '{$session->status}'";

      if(!empty($session->from)){
         list($d, $m, $y) = sscanf($session->from, '%2d.%2d.%4d');
         $where .= " AND `date` > '".date('c', strtotime("$y-$m-$d"))."'";
      }
      if(!empty($session->to)){
         list($d, $m, $y) = sscanf($session->to, '%2d.%2d.%4d');
         $where .= " AND `date` < '".
            date('c', strtotime("$y-$m-$d 23:59:59"))."'";
      }
      if(!empty($session->search)){
         $s = $this->quoteValue('%'.$session->search.'%');
         $where .= " AND (id = ".(int)$session->search." OR
            work_title LIKE $s OR email LIKE $s OR
            executor_name LIKE $s OR chief_name LIKE $s)";
      }

      $count = $this->fetchOne("SELECT COUNT(*) FROM orders $where");
      $res = self::forPagingData($session, $count);
      $res['all_price'] = 0;
      $res['all_paid'] = 0;
      if(empty($count)) return $res;

      $data = $this->fetchAll("SELECT *,
         (SELECT title FROM nominations WHERE id = orders.nomination_id)
            nomination_title,
         (SELECT title FROM themes WHERE id = orders.theme_id) theme_title
         FROM orders $where
         ORDER BY id DESC LIMIT ?, ?",
         [($res['page'] - 1) * $res['on_page'], $res['on_page']]);

      $res['data'] = [];
      foreach($data as $i){
         $i['files'] = $this->fetchAll("SELECT * FROM files
            WHERE order_id = ?", $i['id']);
         $i['links'] = $this->fetchAll("SELECT * FROM links
            WHERE order_id = ?", $i['id']);
         $res['data'][] = $i;
      }

      $res['all_price'] = $this->fetchOne("SELECT SUM(price) FROM orders
         $where");
      $res['all_paid'] = $this->fetchOne("SELECT SUM(paid) FROM orders
         $where");

      return $res;
   }

   /**
    * заявка оплачена
    *
    * @param $id
    *
    * @throws \Exception
    */
   public function changePaid($id){
      $order = $this->getById($id);
      if(empty($order)) throw new \Exception('Такого заказа нет');

      $model_pays = new Pays($this->_sm);
      if($order['status'] == 'new'){
         $model_pays->addPay($order['id']);
      }
      else $model_pays->removePay($order['id']);
   }

   /**
    * @param $id
    *
    * @return array|bool
    */
   public function getById($id){
      $res = $this->fetchRow("SELECT *,
       (SELECT `name` FROM blanks WHERE id = orders.blank_id) blank_name,
       (SELECT title FROM nominations WHERE id = orders.nomination_id)
         nomination_title,
       (SELECT title FROM themes WHERE id = orders.theme_id) theme_title
       FROM orders WHERE id = ?", $id);
      if (empty($res)) return $res;

      $res['files'] = $this->fetchAll("SELECT * FROM files
            WHERE order_id = ?", $id);
      $res['links'] = $this->fetchAll("SELECT * FROM links
            WHERE order_id = ?", $id);

      return $res;
   }

   /**
    * редактируем заявку
    *
    * @param $params
    * @param $id
    *
    * @throws \Exception
    */
   public function editOrder($params, $id){
      $order = $this->getById($id);
      if(empty($order)) throw new \Exception('Такого заказа нет');

      $d = [
         'executor_name' => $params['executor_name'],
         'nomination_id' => $params['nomination_id'],
         'work_title' => $params['work_title'],
         'email' => $params['email'],
         'organization_name' => $params['organization_name'],
         'organization_address' => $params['organization_address'],
         'agree' => $params['agree'],
//         'original' => $params['original'],
      ];
      if($params['type'] == 'kid'){
         $d['theme_id'] = $params['theme_id'];
         $d['executor_age'] = $params['executor_age'];
         $d['chief_name'] = $params['chief_name'];
      }
      else $d['post'] = $params['post'];

      $this->update('orders', $d, "id = $id");

      $this->update('files', [
         'in_gallery' => $params['agree'],
      ], "order_id = $id");
   }

   /**
    * @param $params
    *
    * @return array
    */
   public function getForResult($params){
      $type = $params['type'];
      $page = $params['page'];
      $on_page = $params['on_page'];
      $begin = date('c', strtotime('-2 month'));
      $where = "WHERE result > 0 AND `type` = '$type' AND
         date > '$begin'";

      if(!empty($params['kind']) &&
         in_array($params['kind'], array_keys($this->getKinds())))
         $where .= " AND `kind` = '{$params['kind']}'";

      if(!empty($params['nomination'])){
         $n = (int)$params['nomination'];
         $where .= " AND nomination_id = 
            (SELECT id FROM nominations WHERE id = $n AND `type` = '$type')";
      }
      if(!empty($params['theme'])){
         $t = (int)$params['theme'];
         $where .= " AND theme_id = 
            (SELECT id FROM themes WHERE id = $t AND `type` = '$type')";
      }

      return $this->fetchAll("SELECT *,
         (SELECT title FROM nominations WHERE id = orders.nomination_id)
            nomination_title,
         (SELECT title FROM themes WHERE id = orders.theme_id)
            theme_title
         FROM orders $where
         ORDER BY send_date DESC LIMIT ?, ?",
         [($page - 1) * $on_page, $on_page]);
   }

   /**
    * @param $params
    *
    * @return array
    */
   public function getForGallery($params){
      $type = $params['type'];
      $page = (int)$params['page'];
      $on_page = (int)$params['on_page'];
      $where = "WHERE status = 'sent' AND `type` = '$type'
         AND agree = 'yes'";

      if(!empty($params['nomination'])){
         $n = (int)$params['nomination'];
         $where .= " AND nomination_id = 
            (SELECT id FROM nominations WHERE id = $n AND `type` = '$type')";
      }
      if(!empty($params['theme'])){
         $t = (int)$params['theme'];
         $where .= " AND theme_id = 
            (SELECT id FROM themes WHERE id = $t AND `type` = '$type')";
      }

      return $this->fetchAll("SELECT * FROM files WHERE order_id IN
         (SELECT id FROM orders $where) ORDER BY id DESC LIMIT ?, ?",
         [($page - 1) * $on_page, $on_page]);
   }

   /**
    * @param $id
    * @param $email
    *
    * @return string
    */
   public function verifyByIdAndEmail($id, $email){
      if(empty($id))
         return 'Введите номер заявки';
      if(empty($email))
         return 'Введите адрес электронной почты';
      $order = $this->fetchRow("SELECT * FROM orders WHERE id = ? AND
         email = ?", [$id, $email]);
      if(empty($order))
         return 'Неверно введен номер заявки либо адрес 
            электронной почты, указанный при подаче заявки';
      elseif($order['paid'] == $order['price'])
         return 'Заявка уже оплачена';
      elseif($order['paid'] > $order['price'])
         return 'Вы заплатили больше, 
            чем требовалось для участия в конкурсе. Свяжитесь пожалуйста с 
            администрацией сайта, чтобы разрешить эту ситуацию.';
      elseif($order['paid'] != 0)
         return 'Заявка оплачена частично, необходимо 
            оплатить еще '.($order['price'] - $order['paid']).' рублей';
      else return "Заявка не оплачена либо оплата еще не обработана
       администрацией сайта. Организационный сбор - {$order['price']} рублей";
   }

   /**
    * Меняем статус заказа 
    * 
    * @param $params
    */
   public function changeStatus($params){
      $id = (int)$params['id'];
      $this->update('orders', [
         'status' => $params['status'],
      ], "id = $id");

//      $model_mail = new Mail($this->_sm);
//      $subject = 'Ваша заявка на конкурс во всероссийском центре '.
//         SITE_NAME.' №'.$order['id'].' перенесена в архив, '.
//         'работа до конкурса не допущена!';
//      $model_mail->sendView($order['email'], $subject, 'delete_order', $order);
   }

   /**
    * Меняем результат (место)
    * 
    * @param $id
    * @param $result
    */
   public function changeResult($id, $result){
      $id = (int) $id;
      $order = $this->getById($id);
      $result = $result > 4 || $result == $order['result']? 0: $result;
      $this->update('orders', [
         'result' => $result,
      ], "id = $id");
   }

   /**
    * отправляем письмо с дипломом
    * 
    * @param $id
    *
    * @throws \Exception
    */
   public function sendDiploma($id){
      $id = (int)$id;
      $order = self::getById($id);
      if(empty($order)) throw new \Exception('Нет такой заявки');
      
      $model_mail = new Mail($this->_sm);
      $model_mail->sendDiploma($order);
      $this->update('orders', [
         'send_date' => date('c'),
         'status' => 'sent',
      ], "id = $id");

      // постим в соцсети
      $imgs = $this->fetchAll("SELECT * FROM files WHERE order_id = ? AND
         mime_type LIKE 'image/%' AND in_gallery = 'yes'", $id);
      if(!count($imgs)) return;

      $message = $order['work_title'].' - '.DOMAIN.'/work/'.$order['code'];
      $vk = new Vkontakte();
      $instagram = new Instagram();
      foreach($imgs as $img){
         $file = FILES . $img['dir'] . $img['name'];
         // VK
         $vk->post($message, $file);
         // Instagram
         $instagram->post($file, $message);
         // Facebook
//         $model_tokens = new Tokens($this->_sm);
//         $model_tokens->facebookPost($message, $file);
      }
   }

   /**
    * возвращает id и цену неоплаченных заказов
    * 
    * @return array
    */
   public function getNoPaidOrderIds(){
      return $this->fetchAll("SELECT id, price FROM orders WHERE paid != price
         ORDER BY id DESC");
   }

   /**
    * возвращает клиентов для админки постранично
    * 
    * @param $session
    *
    * @return array
    */
   public function getClientsForAdmin($session){
      $where = "WHERE o.email <> ''";
      if(!empty($session->email))
         $where .= " AND o.email LIKE '%{$session->email}%'";

      $count = $this->fetchOne("SELECT COUNT(DISTINCT email) FROM orders o
         $where");
      $res = self::forPagingData($session, $count);
      if(empty($count)) return $res;

      $res['data'] = $this->fetchAll("SELECT COUNT(*) count, email, 
         executor_name, chief_name
         FROM orders o $where GROUP BY email
         ORDER BY email LIMIT ?, ?",
         [($res['page'] - 1) * $res['on_page'], $res['on_page']]);
      return $res;
   }

   /**
    * @param string $email
    * @param object $session
    * 
    * @return array
    */
   public function getOrdersByEmail($email, $session){
      $where = "WHERE email = '$email'";
      $count = $this->fetchOne("SELECT COUNT(*) FROM orders $where");
      $page = (int) $session->page;
      $on_page = (int) $session->on_page;
      $res = ['data' => [], 'count' => $count, 'page' => $page,
         'on_page' => $on_page];
      if(empty($count)) return $res;
      
      $res['data'] = $this->fetchAll("SELECT * FROM orders $where
         ORDER BY id DESC LIMIT ?, ?", [($page - 1) * $on_page, $on_page]);
      return $res;
   }
   
   public function getOrderByCode($code){
      $id = $this->fetchOne("SELECT id FROM orders WHERE code = ?", $code);
      return $this->getById($id);
   }

   public function addResultFromAdmin($type, $params){
      $this->insert('orders', [
         'date' => $params['date'],
         'type' => $type,
         'nomination_id' => $params['nomination'],
         'theme_id' => empty($params['theme'])? 0: $params['theme'],
         'work_title' => $params['work_title'],
         'email' => 'ptfsapsan@mail.ru',
         'executor_name' => $params['executor_name'],
         'price' => 50,
         'blank_id' => 5,
         'discount' => 3,
         'result' => $params['result'],
      ]);
   }

   public function getOrdersByEmailAndId($email, $id){
      return $this->fetchRow("SELECT * FROM orders
       WHERE email = ? AND id = ? AND status = 'sent'", [$email, $id]);
   }

   public function deleteOrder($id){
      $id = (int)$id;
      $this->delete('orders', "id = $id");
   }
   
   public function getForSitemap(){
      return $this->fetchAll("SELECT * FROM orders WHERE agree = 'yes' AND
         result > 0");
   }

    public function allDiplomant($session)
    {
        $nomination = (int)$session->nomination;
        $theme = (int)$session->theme;
        $month = date('m', strtotime('-1 month'));
        switch ($session->type) {
            case 'kid':
                if (empty($nomination) || empty($theme)) return;
                $this->update('orders', ['result' => 4],
                    "result = 0 AND type = 'kid' AND nomination_id = $nomination AND
                     theme_id = '$theme' AND MONTH(date) = $month AND status = 'paid'");
                break;
            case 'educator':
                if (empty($nomination)) return;
                $this->update('orders', ['result' => 4],
                    "result = 0 AND type = 'educator' AND nomination_id = $nomination AND
                    MONTH(date) = $month AND status = 'paid'");
                break;
        }
    }



}