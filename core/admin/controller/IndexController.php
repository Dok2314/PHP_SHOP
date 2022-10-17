<?php

namespace core\admin\controller;

use core\admin\model\Model;
use core\base\controller\BaseController;

class IndexController extends BaseController
{
    protected function inputData()
    {
        $db = Model::instance();

        $table = 'teachers';

        $res = $db->delete($table, [
            'fields' => ['id', 'name', 'img'],
            'where'  => ['id' => 1]
        ]);

        exit('I am admin panel');
    }
}