<?php
namespace Application\Migrations;

use Zend\Db\Sql\Ddl\Column\Integer;
use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;
use Zend\Db\Sql\Ddl\CreateTable;

class Migrations
{

    protected function update_001()
    {
        $thanksTable = new CreateTable('thanks');
        $id = new Integer('id');
        $id->setNullable(false)->setOptions([
            'autoincrement' => true,
        ]);
        $ii = new PrimaryKey($id);


        $thanksTable->addColumn($id);
    }

}