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

        $files = [];
//        $files['gallery_img'] = [

//        ];

//        $files['img'] = 'main_black.jpg';

//        $_POST['id'] = 7;

//        $_POST['name'] = '';

//        $_POST['content'] = "<h2>New's Article</h2>";


        $res = $db->edit($table, ['files' => $files]);

        exit('I am admin panel');
    }
}