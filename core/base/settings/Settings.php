<?php

namespace core\base\settings;

class Settings
{
    private $routes = [
        'admin' => [
            'name'   => 'admin',
            'path'   => 'core/admin/controllers/',
            'hrUrl'  => false
        ],
        'settings'  => [
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
        'text'      => ['name', 'phone', 'address'],
        'textarea'  => ['content', 'keywords']
    ];

    private $lalala = 'lalala';

    static private $instance;

    static public function instance(): Settings
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    static public function get($property)
    {
        return self::instance()->$property ?? null;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function glueProperties($class)
    {
        $baseProperties = [];

        foreach ($this as $name => $value) {
            $property = $class::get($name);

            if(is_array($property) && is_array($value)) {
                $baseProperties[$name] = $this->arrayMergeRecursive($value, $property);
                continue;
            }

            if(!$property) $baseProperties[$name] = $this->$name;
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