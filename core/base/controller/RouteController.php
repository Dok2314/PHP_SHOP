<?php

namespace core\base\controller;

use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $instance;

    static public function getInstance()
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    private function __clone()
    {
    }

    private function __construct()
    {


        exit();
    }
}