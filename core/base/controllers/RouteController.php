<?php

namespace core\base\controllers;

use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $instance;

    private function __clone()
    {
    }

    private function __construct()
    {
        $settings = Settings::instance();
        $shopSettings = ShopSettings::instance();

    }

    static public function instance(): RouteController
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    public function route()
    {
    }
}