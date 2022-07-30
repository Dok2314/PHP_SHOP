<?php

namespace core\base\settings;

use core\base\settings\Settings;

class ShopSettings
{
    static private $_instance;
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
        if(self::$_instance instanceof self) {
            return self::$_instance;
        }

        self::$_instance = new self;
        self::$_instance->baseSettings = Settings::instance();

        $arr1 = [
            'admin' => [
                'name' => 'root',
                'pwd' => '/etc',
                'values' => [1,2,10]
            ]
        ];

        $arr2 = [
            'admin' => [
                'name' => 'sudo',
                'values' => [1,4]
            ]
        ];

        $newArr = self::$_instance->baseSettings->arrayMergeRecursive(
            $arr1,
            $arr2
        );

        var_dump($newArr);

        die;

        $baseProperties = self::$_instance->baseSettings->glueProperties(get_class());
        self::$_instance->setProperty($baseProperties);

        return self::$_instance;
    }

    protected function setProperty($properties)
    {
        if($properties) {
            foreach ($properties as $name => $property) {
                $this->$name = $property;
            }
        }
    }
}