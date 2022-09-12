<?php

namespace core\base\settings;

class Settings
{
    static private $instance;

    private $routes = [
        'admin' => [
            'alias' => 'admin',
            'path'  => 'core/admin/controller/',
            'hrUrl' => false
        ],
        'settings'  => [
            'path' => 'core/base/settings/'
        ],
        'plugins'  => [
            'path'  => 'core/plugins/',
            'hrUrl' => false,
            'dir'   => false
        ],
        'user' => [
            'path'   => 'core/user/controller/',
            'hrUrl'  => true,
            'routes' => [
                'catalog' => 'site/hello/by/'
            ]
        ],
        'default'  => [
            'controller'   => 'IndexController',
            'inputMethod'  => 'inputData',
            'outputMethod' => 'outputData'
        ]
    ];

    private $templateArr = [
        'text'     => ['name', 'phone', 'address'],
        'textarea' => ['content', 'keywords']
    ];

    private $lalala = 'lalala';

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    static public function instance(): Settings
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    static public function getPropertyByName($propertyName)
    {
        return self::instance()->has($propertyName);
    }

    protected function has($property)
    {
        return $this->$property ?? null;
    }

    public function glueProperties($resultingClass)
    {
        $baseProperties = [];

        foreach ($this as $propertyName => $propertyValue) {
            $propertyValueFromResultingClass = $resultingClass::getPropertyByName($propertyName);

            if(is_array($propertyValue) && is_array($propertyValueFromResultingClass)) {
                $baseProperties[$propertyName] = $this->arrayMergeRecursive($propertyValue, $propertyValueFromResultingClass);
                continue;
            }

            if(!$propertyValueFromResultingClass) $baseProperties[$propertyName] = $propertyValue;
        }

        return $baseProperties;
    }

    public function arrayMergeRecursive()
    {
        $arrays = func_get_args();

        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if(is_array($value) && (!isset($base[$key]) || is_array($base[$key]))) {
                    $base[$key] = $this->arrayMergeRecursive($base[$key] ?? [], $value);
                }else{
                    if(is_int($key)) {
                        if(!in_array($value, $base)) $base[] = $value;
                        //не важно выполнится или нет предыдущее условие, мы должны выйти с данной итерации чтобы не записывать лишнее
                        // потому continue
                        continue;
                    }

                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }
}