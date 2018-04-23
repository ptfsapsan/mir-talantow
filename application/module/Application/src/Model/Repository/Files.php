<?php

namespace Application\Model\Repository;


class Files extends AbstractRepository
{

    public function __construct()
    {
        $this->tableName = 'files';
        parent::__construct();
    }








}