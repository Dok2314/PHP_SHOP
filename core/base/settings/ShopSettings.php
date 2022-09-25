<?php

namespace core\base\settings;

use core\base\controller\Singleton;

class ShopSettings
{
    use Singleton;

    private $baseSettings;

    private $routes = [
        'plugins'  => [
            'dir'    => 'controller',
            'routes' => []
        ]
    ];

    private $templateArr = [
        'text'     => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

    static private function getInstance(): ShopSettings
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        self::instance()->baseSettings = Settings::instance();
        $baseProperties = self::$instance->baseSettings->glueProperties(get_class());
        self::$instance->setProperty($baseProperties);

        return self::$instance;
    }

    static public function getPropertyByName($propertyName)
    {
        return self::getInstance()->has($propertyName);
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