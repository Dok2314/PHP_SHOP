<?php

namespace core\base\controller;

trait Singleton
{
    static private $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    static public function instance()
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }
}