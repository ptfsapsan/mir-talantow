<?php

namespace Application\Model;

use Zend\ServiceManager\ServiceManager;

class Themes extends Base{
    public function __construct(ServiceManager $sm){
        parent::__construct($sm);
    }

    private static function verifyType($type){
        return $type == 'educator'? 'educator': 'kid';
    }


    public function getAll($type = 'kid'){
        $type = self::verifyType($type);
        return $this->fetchAll("SELECT * FROM themes WHERE `type` = ?
            ORDER BY title", $type);
    }

    public function add($params, $type = 'kid'){
        if(empty($params['title']))
            throw new \Exception('Не заполнено название темы');
        $type = self::verifyType($type);
        $this->insert('themes', [
           'title' => $params['title'],
           'text' => $params['text'],
           'type' => $type,
        ]);
    }

    public function del($id){
        $this->delete('themes', "id = ".(int)$id);
    }

    public function changeTitle($params, $type = 'kid'){
        if(empty($params['title']))
            throw new \Exception('Не заполнено название темы');

        $id = (int)$params['id'];
        $type = self::verifyType($type);

        $this->update('themes', [
           'title' => $params['title'],
           'text' => $params['text'],
        ], "id = $id AND type = '$type'");
    }

    public function getByTrans($trans){
        return $this->fetchRow("SELECT * FROM themes WHERE trans = ?",
           $trans);
    }

    public function getById($id){
        return $this->fetchRow("SELECT * FROM themes WHERE id = ?", $id);
    }


}