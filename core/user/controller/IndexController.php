<?php

namespace core\user\controller;

use core\base\controller\BaseController;

class IndexController extends BaseController
{
    protected function inputData()
    {
        $name = 'Daniil';

        $header  = $this->render(TEMPLATE . 'header');
        $content = $this->render('', compact('name'));
        $footer  = $this->render(TEMPLATE . 'footer');

        return compact('header', 'content', 'footer');
    }

    protected function outputData()
    {
        $vars = func_get_arg(0);

        $this->page = $this->render(TEMPLATE . 'templater', $vars);
    }
}