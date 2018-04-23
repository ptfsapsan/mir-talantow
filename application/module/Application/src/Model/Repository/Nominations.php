<?php

namespace Application\Model\Repository;


class Nominations extends AbstractRepository
{

    public function __construct()
    {
        $this->tableName = 'nominations';
        parent::__construct();
    }

    /**
     * @param string|null $type
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getAll(string $type = null)
    {
        $select = $this->sql->select()
            ->from($this->tableName)
            ->order('title');
        if (!empty($type) && in_array($type, Orders::getTypes())) {
            $select->where(['type' => $type]);
        }
        return $this->sql->prepareStatementForSqlObject($select)->execute();
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
            throw new \Exception('Не совпадает тип номинации');
        }

        if (empty($row)) {
            throw new \Exception('Нет такой номинации');
        }

        if (empty($params['title'])) {
            throw new \Exception('Не заполнено название номинации');
        }

        $row->title = $params['title'];
        $row->text = $params['text'];
        $row->save();
    }

    /**
     * @param array $params
     * @param string $type
     * @throws \Exception
     */
    public function add(array $params, string $type){
        if (!in_array($type, array_keys(Orders::getTypes()))) {
            throw new \Exception('Неверный тип');
        }

        if (empty($params['title'])) {
            throw new \Exception('Не заполнено название номинации');
        }

        $this->tableGateway->insert([
            'title' => $params['title'],
            'text' => $params['text'],
            'type' => $type,
        ]);
    }



}