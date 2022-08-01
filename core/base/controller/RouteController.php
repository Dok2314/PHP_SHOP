<?php

namespace core\base\controller;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $instance;

    protected $routes;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    static public function getInstance()
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    private function __clone()
    {
    }

    private function __construct()
    {
        //Запрашиваемый адрес после домена "/"
        $address_str = $_SERVER['REQUEST_URI'];

        // Если последнее вхождение '/' в строку равно длинне строки - 1 и последнее вхождение не равно корню сайта (0)
        // то редирект на страницу без этого слеша '/' со статусом 301
        if(strrpos($address_str, '/') === strlen($address_str) - 1 && strrpos($address_str, '/') !== 0) {
        //  $this->redirect(rtrim($address_str, '/'), 301);
        }

        // $_SERVER['PHP_SELF'] - имя файла скрипта, который сейчас выполняется, относительно корня документов,
        // так как единая точка входа это index.php, $_SERVER['PHP_SELF'] = /index.php
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        if($path == PATH) {
            // Поместили в свойство routes текущего класса данные из routes класса Settings
            $this->routes = Settings::get('routes');

            if(!$this->routes) throw new RouteException('Сайт находится на техническом обслуживании!');

            // Если в адресе элиас админа('admin') идёт сразу после слеша, значит мы стучимся в админку
            if(strpos($address_str, $this->routes['admin']['alias']) === strlen(PATH)) {

                // TODO: ADMINKA

            }else {
                // Запрошеный адрес разбитый на массив без начального слеша '/'
                $url = explode('/', substr($address_str, strlen(PATH)));

                $hrUrl = $this->routes['user']['hrUrl'];

                // Контроллер для обработки пользовательской части
                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            $this->createRoute($route, $url);

            exit();
        }else {
            try {
                throw new \Exception('Не корректная директория сайта!');
            }catch (\Exception $e) {
                exit($e->getMessage());
            }
        }
    }

    private function createRoute($routeStr, $url)
    {
        $route = [];

        if(!empty($url[0])) {
            if(isset($this->routes[$routeStr]['routes'][$url[0]])) {
                //Разбиваю путь указаный в Settings по слешу '/'
                $route = explode('/', $this->routes[$routeStr]['routes'][$url[0]]);

                // Если есть элиас то формируем из него контроллер
                $this->controller .= ucfirst($route[0].'Controller');
            }else {
                // Если запрашивам путь не равен элиасу из массива, берем контроллер из строки запроса
                $this->controller .= ucfirst($url[0].'Controller');
            }
        }else {
            // Если нет строки запроса(мы на главной) то берем дефолтный контроллер IndexController
            $this->controller = $this->routes['default']['controller'];
        }

        // Если в $route нет индекса 1 то подключаем дефолтный метод для ввода
        // Если в $route нет индекса 2 то подключаем дефолтный метод для вывода
        $this->inputMethod  = $route[0] ? $route[0] : $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ? $route[2] : $this->routes['default']['outputMethod'];
    }
}