<?php

namespace core\base\settings;

class ShopSettings
{
    static private $instance;

    private $baseSettings;

    private $routes = [
        'plugins'  => [
            'path'  => 'core/plugins/',
            'hrUrl' => false,
            'dir'   => 'controller'
        ]
    ];

    private $templateArr = [
        'text'     => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    static public function instance(): ShopSettings
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

    static public function getPropertyByName($propertyName)
    {
        return self::instance()->has($propertyName);
    }

    protected function has($property)
    {
        return $this->$property ?? null;
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