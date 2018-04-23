<?php
namespace Application\Model;

   /**
    * Class Pays
    *
    * @package Application\Model
    */
/**
 * Class Pays
 *
 * @package Application\Model
 */
class Pays extends Base{

   /**
    * @var array
    */
   private static $_ways
      = [
         'sb' => 'Сбербанк',
         'wm' => 'WebMoney',
         'yd' => 'Яндекс.Деньги',
      ];

   /**
    * @return array
    */
   public static function getWays(){
      return self::$_ways;
   }


   /**
    * добавляем платеж
    *
    * @param      $order_id
    * @param null $pay_way
    * @param null $sum
    *
    * @throws \Exception
    */
   public function addPay($order_id, $pay_way = null, $sum = null){
      $pay_way = in_array($pay_way, array_keys(self::$_ways))? $pay_way: null;
      $model_orders = new Orders($this->_sm);
      $order = $model_orders->getById($order_id);
      if(empty($sum)){
         $sum = $order['price'];
      }

      $this->insert('pays', [
         'date' => date('c'),
         'order_id' => $order_id,
         'pay_way' => $pay_way,
         'sum' => $sum,
      ]
      );
      $order['paid'] += $sum;
      $order['sum'] = $sum;
      $this->update('orders', [
         'paid' => $order['paid'],
         'status' => 'paid',
         'send_date' => date('c'),
      ], "id = $order_id"
      );

      $model_mail = new Mail($this->_sm);
      if($order['price'] == $order['paid']){
         $subject = "Заявка №$order_id на сайте Мир Талантов".
            " допущена к участию";
         $model_mail->sendView($order['email'], $subject, 'confirm_pay',
            $order
         );
      }
      else{
         $subject = "Платеж по заявке №$order_id на сайте Мир Талантов".
            " подтвержден, но сумма платежа недостаточна";
         $model_mail->sendView($order['email'], $subject, 'not_enough',
            $order
         );
      }
   }

   /**
    * удаляем все платежи по заказу
    *
    * @param $order_id
    */
   public function removePay($order_id){
      $order_id = (int)$order_id;
      $this->update('orders', [
         'status' => 'new',
         'paid' => 0,
      ], "id = $order_id");
      $this->delete('pays', "order_id = $order_id");
   }

   /**
    * получаем платежи постранично
    *
    * @param $session
    *
    * @return array
    */
   public function getAll($session){
      $where = "WHERE sum > 0";
      $count = $this->fetchOne("SELECT COUNT(*) FROM pays $where");
      $res = self::forPagingData($session, $count);
      if(empty($count)) return $res;

      $res['data'] = $this->fetchAll("SELECT * FROM pays $where
         ORDER BY date DESC LIMIT ?, ?",
         [($res['page'] - 1) * $res['on_page'], $res['on_page']]
      );
      return $res;
   }

   /**
    * удаляем платеж
    *
    * @param $id
    *
    * @throws \Exception
    */
   public function deletePay($id){
      $pay = $this->fetchRow("SELECT * FROM pays WHERE id = ?", $id);
      if(empty($pay)){
         throw new \Exception('Такого платежа нет');
      }

      $this->delete('pays', "id = $id");
      $this->_db->query("UPDATE orders SET paid = paid - ? WHERE id = ?",
         [$pay['sum'], $pay['order_id']]
      );
   }

}