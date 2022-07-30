<?php

namespace core\base\settings;

class Settings
{
    static private $_instance;

    private $routes = [
        'admin' => [
            'name'  => 'admin',
            'path'  => 'core/admin/controllers/',
            'hrUrl' => false
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins' => [
            'path'  => 'core/plugins/',
            'hrUrl' => false
        ],
        'user' => [
            'path'   => 'core/user/controllers/',
            'hrUrl'  => true,
            'routes' => []
        ],
        'default' => [
            'controller'   => 'IndexController',
            'inputMethod'  => 'inputData',
            'outputMethod' => 'outputData'
        ]
    ];

    private $templateArr = [
        'text'     => ['name', 'phone', 'address'],
        'textarea' => ['content', 'keywords']
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

        return self::$_instance = new self;
    }

    public function glueProperties($class)
    {
        $baseProperties = [];

        //Прохожусь по свойствам текущего класса Settings
        foreach ($this as $propertyName => $insideBaseProperty) {
            // Запрашиваю свойства класса ShopSettings на основе свойств базового Settings
            // $insideProperty = полученые данные из массива routes и templateArr
            $insideProperty = $class::get($propertyName);

            //Если полученые  $insideProperty массив и $insideBaseProperty, тоже массив
            // то я формирую массив с аналогичными ключами а в значение применяю функцию которая будет клеить мои массивы
            // базовый с массивом из ShopSettings
            if(is_array($insideProperty) && is_array($insideBaseProperty)) {
                //$baseProperties['routes'] = $this->arrayMergeRecursive($this->routes, 'routes' из ShopSettings)
                //$baseProperties['templateArr'] = $this->arrayMergeRecursive($this->templateArr, 'templateArr' из ShopSettings)
                $baseProperties[$propertyName] = $this->arrayMergeRecursive($insideBaseProperty, $insideProperty);
                continue;
            }

            //Если у ShopSettings не существует таких свойств как у Settings, то записую те которые в Settings
            if(!$insideProperty) $baseProperties[$propertyName] = $this->$propertyName;
        }

        return $baseProperties;
    }

    public function arrayMergeRecursive($base, ...$arrays)
    {
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && (!isset($base[$key]) || is_array($base[$key]))) {
                    $base[$key] = $this->arrayMergeRecursive($base[$key] ?? [], $value);
                } else {
                    if(is_int($key)) {
                        if(!in_array($value, $base)) {
                            $base[] = $value;
                        }
                        continue;
                    }
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }
}