<?php

namespace Application\Model\Repository;

use Zend\Db\Sql\Expression;

class Orders extends AbstractRepository
{
    const TYPE_KID = 'kid';
    const TYPE_EDUCATOR = 'educator';

    const KIND_MONTHLY = 'monthly';
    const KIND_FAST = 'fast';
    const KIND_URGENT = 'urgent';

    const STATUS_NEW = 'new';
    const STATUS_PAID = 'paid';
    const STATUS_SENT = 'sent';
    const STATUS_ARCHIVE = 'archive';
    const STATUS_DELETED = 'deleted';

    const PLACE_1 = 1;
    const PLACE_2 = 2;
    const PLACE_3 = 3;
    const PLACE_GRADUATE = 4;


    private static $types = [
        self::TYPE_KID => 'Конкурсы для детей',
        self::TYPE_EDUCATOR => 'Конкурсы для педагогов',
    ];

    private static $kinds = [
        self::KIND_MONTHLY => 'Ежемесячный',
        self::KIND_FAST => 'Быстрый',
        self::KIND_URGENT => 'Срочный',
    ];

    private static $statuses = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_PAID => 'Оплаченный',
        self::STATUS_SENT => 'Отправленный',
        self::STATUS_ARCHIVE => 'В архиве',
        self::STATUS_DELETED => 'Удаленный',
    ];

    private static $places = [
        self::PLACE_1 => '1 место',
        self::PLACE_2 => '2 место',
        self::PLACE_3 => '3 место',
        self::PLACE_GRADUATE => 'дипломант',
    ];



    public function __construct()
    {
        $this->tableName = 'orders';
        parent::__construct();
    }

    public static function getTypes()
    {
        return self::$types;
    }

    public static function getKinds()
    {
        return self::$kinds;
    }

    public static function getStatuses()
    {
        return self::$statuses;
    }

    public static function getPlaces()
    {
        return self::$places;
    }



    public function getAll($session){
        $sql = $this->sql;
        $select = $sql->select()->from($this->tableName);

        if(!empty($session->type) &&
            in_array($session->type, array_keys(self::getTypes())))
            $select->where(['type' => $session->type]);

        if(!empty($session->kind) &&
            in_array($session->kind, array_keys(self::getKinds())))
            $select->where(['kind' => $session->kind]);

        if(!empty($session->nomination))
            $select->where(['nomination_id' => $session->nomination]);

        if(!empty($session->theme))
            $select->where(['theme_id' => $session->theme]);

        if(!empty($session->status) &&
            in_array($session->status, array_keys(self::getStatuses())))
            $select->where(['status' => $session->status]);

        if(!empty($session->from)){
            list($d, $m, $y) = sscanf($session->from, '%2d.%2d.%4d');
            $select->where("`date` > '" . date('c', strtotime("$y-$m-$d")) . "'");
        }

        if(!empty($session->to)){
            list($d, $m, $y) = sscanf($session->to, '%2d.%2d.%4d');
            $select->where("`date` < '" . date('c', strtotime("$y-$m-$d 23:59:59")) . "'");
        }

        if(!empty($session->search)){
            $s = $this->adapter->platform->quoteValue('%'.$session->search.'%');
            $select->where("(id = " . (int)$session->search . " OR
                work_title LIKE $s OR email LIKE $s OR
                executor_name LIKE $s OR chief_name LIKE $s)");
        }

        $res = $this->prepareResultForPaging($session->page, $session->onPage, $select);
        $res['all_price'] = 0;
        $res['all_paid'] = 0;
        if(empty($res['count'])) return $res;

        $select = $this->prepareLimit($res['page'], $res['on_page'], $select);

        $select->columns([
                '*',
                'nomination_title' =>
                    new Expression("(SELECT title FROM nominations WHERE id = orders.nomination_id)"),
                'theme_title' =>
                    new Expression("(SELECT title FROM themes WHERE id = orders.theme_id)"),
            ])
            ->order('id DESC')
        ;

        $data = $sql->prepareStatementForSqlObject($select)->execute();

        $res['data'] = [];
        foreach($data as $i){
            $i['files'] = RepositoryFactory::factory('files')->getByWhere(['order_id' => $i['id']]);
            $i['links'] = RepositoryFactory::factory('links')->getByWhere(['order_id' => $i['id']]);
            $res['data'][] = $i;
        }

        $s = $select->columns(['all_price' => new Expression('SUM(price)')]);
        $res['all_price'] = $sql->prepareStatementForSqlObject($s)->execute()->current()['all_price'];

        $s = $select->columns(['all_paid' => new Expression('SUM(paid)')]);
        $res['all_paid'] = $sql->prepareStatementForSqlObject($s)->execute()->current()['all_paid'];

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

    /**
     * редактируем заявку
     *
     * @param $params
     * @param $id
     *
     * @throws \Exception
     */
    public function editOrder($params, $id){
        $row = $this->getRowById($id);
        if(empty($row)) throw new \Exception('Такого заказа нет');

        $data = [
            'executor_name' => $params['executor_name'],
            'nomination_id' => $params['nomination_id'],
            'work_title' => $params['work_title'],
            'email' => $params['email'],
            'organization_name' => $params['organization_name'],
            'organization_address' => $params['organization_address'],
            'agree' => $params['agree'],
        ];
        if($params['type'] == 'kid'){
            $data['theme_id'] = $params['theme_id'];
            $data['executor_age'] = $params['executor_age'];
            $data['chief_name'] = $params['chief_name'];
        }
        else $data['post'] = $params['post'];

        $this->tableGateway->update($data, ['id' => $id]);

        RepositoryFactory::factory('files')->tableGateway
            ->update(['in_gallery' => $params['agree']], ['order_id' => $id]);
    }





}