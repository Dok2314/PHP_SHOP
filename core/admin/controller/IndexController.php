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
            'where'  => ['id' => 173],
            'join' => [
                [
                    'table' => 'students',
                    'on' => ['student_id', 'id']
                ]
            ]
        ]);

        exit('I am admin panel');
    }
}