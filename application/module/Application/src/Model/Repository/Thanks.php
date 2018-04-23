<?php

namespace Application\Model\Repository;


class Thanks extends AbstractRepository
{

    public function __construct()
    {
        $this->tableName = 'thanks';
        parent::__construct();
    }

    public function getAll($page, $onPage){
        $sql = $this->sql;
        $select = $sql->select()->from($this->tableName);

        $res = $this->prepareResultForPaging($page, $onPage, $select);
        if(empty($res['count'])) return $res;

        $select = $this->prepareLimit($res['page'], $res['on_page'], $select);
        $select->order('id DESC');
        $res['data'] = $sql->prepareStatementForSqlObject($select)->execute();

        return $res;
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




}