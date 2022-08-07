<?php

namespace core\base\settings;

class Settings
{
    static private $instance;

    private $routes = [
        'admin' => [
            'alias'  => 'admin',
            'path'   => 'core/admin/controller/',
            'hrUrl'  => false,
            'routes' => []
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins' => [
            'path'  => 'core/plugins/',
            'hrUrl' => false,
            'dir'   => false
        ],
        'user' => [
            'path'   => 'core/user/controller/',
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
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    public function glueProperties($class)
    {
        $baseProperties = [];

        foreach ($this as $settingsPropertyName => $settingsPropertyValue) {
            $parentPropertyValue = $class::get($settingsPropertyName);

            if(is_array($settingsPropertyValue) && is_array($parentPropertyValue)) {
                $baseProperties[$settingsPropertyName] = $this->arrayMergeRecursive($settingsPropertyValue, $parentPropertyValue);
                continue;
            }

            if(!$parentPropertyValue) $baseProperties[$settingsPropertyName] = $this->$settingsPropertyName;
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