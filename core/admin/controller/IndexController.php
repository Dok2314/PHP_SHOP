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

        $colors = [
          'red',
          'green',
          'blue',
          'black'
        ];

//        'fields'            => ['id', 'name'],
//        'where'             => ['name' => "O'Raily"],

        $res = $db->get($table, [
             'fields'            => ['id', 'name'],
             'where'             => ['name' => "O'Raily"],
             'operand'           => ['IN', '<>'],
             'condition'         => ['AND'],
             'order'             => ['name'],
             'order_direction'   => ['DESC'],
             'limit'             => 1,
            'join'               => [
                [
                    'table'     => 'join_table1',
                    'fields'    => ['id as j_id', 'name as j_name'],
                    'type'      => 'left',
                    'where'     => ['name' => 'Daniil'],
                    'operand'   => '=',
                    'condition' => ['OR'],
                    'on'        => [
                        'table'  => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ],
                    'group_condition' => 'AND'
                ],
                'join_table2' => [
                    'table'     => 'join_table2',
                    'fields'    => ['id as j_id', 'name as j_name'],
                    'type'      => 'left',
                    'where'     => ['surname' => 'Surname'],
                    'operand'   => '=',
                    'condition' => ['AND'],
                    'on'        => ['id', 'parent_id']
                ]
            ]
        ]);

        exit('I am admin panel');
    }
}