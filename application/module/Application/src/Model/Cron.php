<?php
namespace Application\Model;

/**
 * Class Cron
 *
 * @package Application\Model
 */
use Zend\Validator\EmailAddress;

/**
 * Class Cron
 *
 * @package Application\Model
 */
class Cron extends Base{

    /**
     * меняем статус заявки на удаленные
     *
     * @throws \Exception
     */
    public function deleteOldOrders(){
        $orders = $this->fetchAll("SELECT * FROM orders WHERE paid < 1
         AND date < ? AND status = 'archive'",
            gmdate('Y-m-d H:i:s', strtotime('-6 days')));
        if(!count($orders)) return;
        $subject = "Ваша заявка удалена";
        $model_mail = new Mail($this->_sm);
        $v = new EmailAddress();
        $ids = [];
        foreach($orders as $order){
            if(!$v->isValid($order['email'])) continue;
            try{
                $model_mail->sendView($order['email'], $subject, 'delete_order',
                    $order);
            }
            catch(\Exception $e){}
            $ids[] = $order['id'];
        }
        if(count($ids))
            $this->update('orders', ['status' => 'deleted'],
                "id IN (".implode(',', $ids).")");
    }

    /**
     * переносим заявки в архив
     *
     * @throws \Exception
     */
    public function oldOrdersInArchive(){
        $orders = $this->fetchAll("SELECT * FROM orders WHERE paid < 1
         AND date < ? AND status = 'new'",
            date('Y-m-d H:i:s', strtotime('-3 days')));
        if(!count($orders)) return;

        $subject = "Ваша заявка перенесена в архив";
        $model_mail = new Mail($this->_sm);
        $v = new EmailAddress();
        $ids = [];
        foreach($orders as $order){
            if(!$v->isValid($order['email'])) continue;
            try{
                $model_mail->sendView($order['email'], $subject, 'remind_pay',
                    $order);
            }
            catch(\Exception $e){}
            $ids[] = $order['id'];
        }
        if(count($ids))
            $this->update('orders', ['status' => 'archive'],
                "id IN (".implode(',', $ids).")");
    }

    /**
     * удаляем старые файлы
     * @throws \Exception
     *
     */
    public function deleteOldFiles(){
        // файлы из галереи храним 6 месяцев
        // файлы не из галереи храним 2 месяца
        $files = $this->fetchAll("SELECT * FROM files WHERE
         (date < ? AND in_gallery = 'yes') OR
         (date < ? AND in_gallery = 'no')",
            [date('Y-m-d H:i:s', strtotime('-6 months')),
                date('Y-m-d H:i:s', strtotime('-2 months'))]
        );
        if(!count($files)) return;

        foreach($files as $file){
            $f = FILES . $file['dir'] . $file['name'];
            if (file_exists($f)) {
                if (is_dir($f)) rmdir($f);
                else unlink($f);
            }
            $f = FILES . $file['dir'] . $file['thumb'];
            if (file_exists($f)) {
                if (is_dir($f)) rmdir($f);
                else unlink($f);
            }
        }
        $ids = array_column($files, 'id');
        $this->delete('files', "id IN (".implode(',', $ids).")");
    }


    /**
     * добавляем пустые результаты
     */
    public function addEmptyResults(){
        $n_kid = 1;
        $n_educator = 1;

        $kinds = Orders::getKinds();
        $k = array_combine(range(1, 3), array_keys($kinds));

        // добавляем результат в детские конкурсы
        $nom = $this->fetchAll("SELECT * FROM nominations WHERE type = 'kid'");
        $nomination = array_column($nom, 'title', 'id');
        foreach(range(1, $n_kid) as $n){
            $a = $this->fetchRow("SELECT * FROM t_orders WHERE ispname <> ''
            ORDER BY RAND() LIMIT 1");
            $this->insert('orders', [
                'date' => date('c'),
                'type' => 'kid',
                'kind' => $k[rand(2, 3)],
                'nomination_id' => array_rand($nomination),
                'theme_id' => $this->fetchOne("SELECT id FROM themes
               WHERE type = 'kid' ORDER BY RAND() LIMIT 1"),
                'work_title' => $a['title'],
                'executor_name' => $a['ispname'],
                'result' => rand(1, 4),
                'send_date' => date('c'),
            ]);
        }

        // добавляем результат в конкурсы для педагогов
        $nom = $this->fetchAll("SELECT * FROM nominations
         WHERE type = 'educator'");
        $nomination = array_column($nom, 'title', 'id');
        foreach(range(1, $n_educator) as $n){
            $a = $this->fetchRow("SELECT * FROM t_orders WHERE rukname <> ''
            ORDER BY RAND() LIMIT 1");
            $this->insert('orders', [
                'date' => date('c'),
                'type' => 'educator',
                'kind' => $k[rand(2, 3)],
                'nomination_id' => array_rand($nomination),
                'work_title' => $a['title'],
                'executor_name' => $a['rukname'],
                'result' => rand(1, 4),
                'send_date' => date('c'),
            ]);
        }
    }

    // отправляем диплом по оплаченной и оцененной заявке
    /**
     *
     */
    public function sendOrder(){
        //$this->sendAd();  // посылаем спам

        $order = $this->fetchRow("SELECT * FROM orders WHERE status = 'paid' 
          AND result > 0");
        if(empty($order)) return;

        $model_orders = new Orders($this->_sm);
        try{
            $model_orders->sendDiploma($order['id']);
        }
        catch(\Exception $e){}

    }

    // удаляем временные файлы
    /**
     * @throws \Exception
     */
    public function deleteTempFiles(){
        $temp = glob(FILES . '/temp_files/*');
        $time_file = time() - (60 * 60 * 24);
        $time_dir = $time_file - (60 * 60 * 24 * 6);
        if(count($temp)) foreach($temp as $file){
            if(is_dir($file)){
                if($time_dir > filemtime($file)) Images::removeDir($file);
            }
            else{
                if($time_file > filemtime($file)) unlink($file);
            }
        }
        $this->delete('temp_files', "date < NOW() - INTERVAL 7 DAY");
    }

    public function sendAd(){
        $link = APPLICATION_ROOT.'/data/emails.txt';
        $emails = explode(',', file_get_contents($link));
        if(!count($emails)) return;

        $emails = array_unique($emails);
        $email = array_shift($emails);
        file_put_contents($link, implode(',', $emails));
        $model_mail = new Mail($this->_sm);
        $subject = 'Всероссийский центр Мир Талантов';
        $model_mail->sendView($email, $subject, 'ad', []);
    }

    /** удаляем пустые каталоги
     *
     * @param string $dir
     */
    public function recRemoveEmptyDir($dir = ROOT_DIR . '/files/')
    {
        if (!is_dir($dir)) return;
        $files = glob($dir . '*');
        if (!count($files)) {
            rmdir($dir);
            return;
        }
        foreach ($files as $file) {
            if (strpos($file, 'temp_files') !== false) continue;
            if (is_dir($file)) $this->recRemoveEmptyDir($file);
        }
    }


}