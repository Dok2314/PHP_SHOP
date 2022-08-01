<?php

namespace core\base\settings;

use core\base\settings\Settings;

class ShopSettings
{
    static private $instance;
    private $baseSettings;

    private $routes = [
        'admin' => [
            'name' => 'sudo'
        ],
        'admin2' => [
            'name' => 'Root'
        ]
    ];

    private $templateArr = [
        'text'     => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

    private function __clone()
    {
    }

    private function __construct()
    {
    }

    static public function get($property)
    {
        return self::instance()->$property;
    }

    static public function instance()
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        self::$instance = new self;
        self::$instance->baseSettings = Settings::instance();
        $baseProperties = self::$instance->baseSettings->glueProperties(get_class());
        self::$instance->setProperty($baseProperties);

        return self::$instance;
    }

    protected function setProperty($properties)
    {
        if($properties) {
            foreach ($properties as $name => $value) {
                $this->$name = $value;
            }
        }
    }
}