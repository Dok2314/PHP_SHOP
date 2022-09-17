<?php

namespace core\user\controller;

use core\base\controller\BaseController;

class IndexController extends BaseController
{
    protected function inputData()
    {
        $template = $this->render(false, ['name' => 'Daniil','age' => 19]);
        exit($template);
    }

    protected function outputData()
    {
        
    }
}