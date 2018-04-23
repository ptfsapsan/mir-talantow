<?php

namespace Application\Model\Repository;


class Themes extends AbstractRepository
{

    public function __construct()
    {
        $this->tableName = 'themes';
        parent::__construct();
    }

    /**
     * @param string|null $type
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getAll(string $type = null){
        $select = $this->sql->select()
            ->from($this->tableName)
            ->order('title')
        ;
        if (!empty($type) && in_array($type, Orders::getTypes())) {
            $select->where(['type' => $type]);
        }
        return $this->sql->prepareStatementForSqlObject($select)->execute();
    }




    /** Сохраняем изменения
     *
     * @param array $params
     * @param int $id
     */
    public function saveChanges(array $params, int $id){
        $row = $this->getRowById($id);
        $params['id'] = $id;
        unset($params['act'], $params['submit']);
        $row->exchangeArray($params);
        $row->save();
    }

    /**
     * @param $params
     * @param string $type
     * @throws \Exception
     */
    public function changeTitle($params, $type)
    {
        if (!in_array($type, array_keys(Orders::getTypes()))) {
            throw new \Exception('Неверный тип');
        }

        $row = $this->getRowById($params['id']);
        if ($row->type != $type) {
            throw new \Exception('Не совпадает тип темы');
        }

        if (empty($row)) {
            throw new \Exception('Нет такой темы');
        }

        if (empty($params['title'])) {
            throw new \Exception('Не заполнено название темы');
        }

        $row->title = $params['title'];
        $row->text = $params['text'];
        $row->save();
    }


    /**
     * @param $params
     * @param string $type
     * @throws \Exception
     */
    public function add($params, $type = Orders::TYPE_KID){
        if (!in_array($type, array_keys(Orders::getTypes()))) {
            throw new \Exception('Неверный тип');
        }

        if (empty($params['title'])) {
            throw new \Exception('Не заполнено название темы');
        }

        $this->tableGateway->insert([
            'title' => $params['title'],
            'text' => $params['text'],
            'type' => $type,
        ]);
    }





}