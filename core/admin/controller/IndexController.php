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

        $res = $db->get($table, [
            'fields'          => ['id', 'name'],
            'where'           => ['name' => 'masha, olya, sveta', 'surname' => 'Sergeevna', 'fio' => 'Andrey', 'car' => 'Porsche', 'color' => $colors],
            'operand'         => ['IN', 'LIKE%', '<>', '=', 'NOT IN'],
            'condition'       => ['AND', 'OR'],
            'order'           => ['fio', 'name'],
            'order_direction' => ['DESC'],
            'limit'           => '1',
            'join'            => [
                'join' => [
                    'table'     => 'join_table1',
                    'fields'    => ['id as j_id', 'name as j_name'],
                    'type'      => 'left',
                    'where'     => ['name' => 'Sasha'],
                    'operand'   => ['='],
                    'condition' => ['OR'],
                    'on'        => [
                        'table'  => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]
                ],
                'join_table' => [
                    'table'     => 'join_table2',
                    'fields'    => ['id as j_id', 'name as j_name'],
                    'type'      => 'left',
                    'where'     => ['name' => 'Sasha'],
                    'operand'   => ['='],
                    'condition' => ['OR'],
                    'on'        => [
                        'table'  => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]
                ]
            ]
        ]);
    }
}