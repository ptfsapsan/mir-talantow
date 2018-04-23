<?php

namespace Application\Model\Repository;


use Zend\Db\RowGateway\RowGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

abstract class AbstractRepository
{
    /** @var \Zend\Db\Adapter\Adapter */
    protected $adapter;

    /** @var Sql */
    protected $sql;

    /** @var TableGateway */
    protected $tableGateway;

    /** @var string */
    protected $tableName;

    public function __construct()
    {
        $this->adapter = GlobalAdapterFeature::getStaticAdapter();
        $this->sql = new Sql($this->adapter);
        $this->tableGateway = new TableGateway(
            $this->tableName,
            $this->adapter,
            new RowGatewayFeature('id')
        );
    }

    /**
     * @param int $id
     * @return RowGateway
     */
    public function getRowById(int $id)
    {
        return $this->tableGateway->select(['id' => $id])->current();
    }

    /**
     * @param int $id
     */
    public function deleteById(int $id)
    {
        $row = $this->getRowById($id);
        $row->delete();
    }

    /**
     * @return Paginator
     */
    public function getAllPagination()
    {
        $select = $this->sql->select()
            ->from($this->tableName)
            ->order('id DESC');
        $adapter = new DbSelect($select, $this->adapter);

        return new Paginator($adapter);
    }

    /**
     * @param int $page
     * @param int $onPage
     * @param Select $select
     * @return array
     */
    protected function prepareResultForPaging(int $page, int $onPage, Select $select)
    {
        $s = $select->columns(['count' => new Expression('COUNT(*)')]);
        $count = $this->sql->prepareStatementForSqlObject($s)->execute()->current()['count'];

        $page = (int)$page;
        $onPage = (int)$onPage;
        $max_page = ceil($count / $onPage);
        $page = $page > $max_page ? $page = $max_page : ($page < 1 ? 1 : $page);
        $res = [
            'data' => [],
            'count' => $count,
            'page' => $page,
            'on_page' => $onPage,
        ];
        return $res;
    }

    /**
     * @param int $page
     * @param int $onPage
     * @param Select $select
     * @return Select
     */
    protected function prepareLimit(int $page, int $onPage, Select $select)
    {
        $select
            ->limit($onPage)
            ->offset(($page - 1) * $onPage)
        ;
        return $select;
    }

    /**
     * @param array $where
     * @return array
     */
    public function getByWhere(array $where = [])
    {
        return $this->tableGateway->select($where)->toArray();
    }

}