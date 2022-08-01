<?php

namespace core\base\controllers;

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
        $s = Settings::instance();
        $s1 = ShopSettings::instance();
        exit();
    }
}