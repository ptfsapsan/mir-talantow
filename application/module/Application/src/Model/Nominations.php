<?php

namespace Application\Model;

use Zend\ServiceManager\ServiceManager;

class Nominations extends Base{
    public function __construct(ServiceManager $sm){
        parent::__construct($sm);
    }
    
    public function getAll($type = 'kid'){
        $type = self::verifyType($type);
        return $this->fetchAll("SELECT * FROM nominations
            WHERE `type` = ? ORDER BY title", $type);
    }

    public function add($params, $type = 'kid'){
        if(empty($params['title']))
            throw new \Exception('Не заполнено название темы');
        $type = self::verifyType($type);
        $this->insert('nominations', [
           'title' => $params['title'],
           'text' => $params['text'],
           'type' => $type]);
    }

    public function del($id){
        $this->delete('nominations', "id = ".(int)$id);
    }

    public function changeTitle($params, $type = 'kid'){
        $id = (int)$params['id'];
        $type = self::verifyType($type);
        if(empty($params['title']))
            throw new \Exception('Не заполнено название темы');

        $this->update('nominations', [
            'title' => $params['title'],
            'text' => $params['text'],
        ], "id = $id AND type = '$type'");
    }

    private static function verifyType($type){
        return $type == 'educator'? 'educator': 'kid';
    }

    public function getByTrans($trans){
        return $this->fetchRow("SELECT * FROM nominations WHERE trans = ?",
           $trans);
    }

    public function getById($id){
        return $this->fetchRow("SELECT * FROM nominations WHERE id = ?", $id);
    }


}