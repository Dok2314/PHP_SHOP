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

        $files['gallery_img'] = [
          'red.jpg',
          'green.jpg',
          'blue.jpg',
          'black.jpg'
        ];

        $files['img'] = 'main_img.jpg';

//        $_POST['name'] = 'Daniil';

        $res = $db->showColumns('teachers');

//        $db->add($table, [
//             'fields' => ['name' => 'Daniil', 'content' => 'Hello World'],
//             'except' => ['name'],
//             'files'  => $files
//        ]);

        exit('I am admin panel');
    }
}