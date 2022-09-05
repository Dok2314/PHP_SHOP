<?php

namespace core\base\controllers;

use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    private static $instance;

    private function __clone()
    {
    }

    public static function instance(): RouteController
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    public function route()
    {
    }

    private function __construct()
    {
        $settings     = Settings::instance();
        $shopSettings = ShopSettings::instance();

//        var_dump($shopSettings::get('routes'));
//        var_dump($settings::get('routes'));
    }
}