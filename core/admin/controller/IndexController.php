<?php

namespace core\admin\controller;

use core\admin\model\Model;
use core\base\controller\BaseController;

class IndexController extends BaseController
{
    protected function inputData()
    {
        $db    = Model::instance();
        $table = 'teachers';

        $res = $db->get($table, [
            'fields'          => ['id', 'name'],
            'where'           => ['name' => 'masha, olya, sveta'],
            'operand'         => ['IN', '<>'],
            'condition'       => ['AND'],
            'order'           => ['fio', 'name'],
            'order_direction' => ['ASC', 'DESC'],
            'limit'           => '1'
        ]);
    }
}