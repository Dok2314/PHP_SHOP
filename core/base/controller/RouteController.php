<?php

namespace core\base\controller;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $instance;

    protected array $routes;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected array $parameters;

    private function __clone()
    {
    }

    static public function instance(): RouteController
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    public function route()
    {
    }

    private function __construct()
    {
        $address_str = $_SERVER['REQUEST_URI'];

        // Если в запросе слеш последний / и это не корень сайта / то редирект на страницу без этого слеша для СЕО
        if(strrpos($address_str, '/') === strlen($address_str) - 1 && strrpos($address_str, '/') !== 0) {
            // $this->redirect(rtrim($address_str, '/'), 301);
        }

        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'],'index.php'));

        // Если путь в конфиге равен тому за который отвечает скрипт то можем делать что-то если нет то у нас не настроен конфиг
        if($path === PATH) {
            $this->routes = Settings::getPropertyByName('routes');

            if(!$this->routes) throw new RouteException("Сайт находится на техническом обслуживании!");

            if(strrpos($address_str, $this->routes['admin']['alias']) === strlen(PATH)) {
                // Admin Panel
            }else{
                $url = explode('/', substr($address_str, strlen(PATH)));

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            $this->createRoute($route, $url);
            exit();
        }else{
            try {
                throw new \Exception("Неверная директория сайта!");
            }catch (\Exception $e) {
                exit($e->getMessage());
            }
        }
    }

    private function createRoute(string $routeName, array $url)
    {
        $route = [];

        //Если нулевой элемент не пуст то это не корень
        // проверяем сузествует ли такой элиас,
        // если да то из него берем первую чать - это котроллер
        if(!empty($url[0])) {
            if(isset($this->routes[$routeName]['routes'][$url[0]])) {
                $route = explode('/', $this->routes[$routeName]['routes'][$url[0]]);

                $this->controller .= ucfirst($route[0].'Controller');
            }else{
                // если не существует то берем из строки запроса
                $this->controller .= ucfirst($url[0].'Controller');
            }
        }else{
            // если нет вообще строки запроса берём дефолтный
            $this->controller .= $this->routes['default']['controller'];
        }

        // если нет методов берём из дефолта
        $this->inputMethod  = $route[1] ?? $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ?? $this->routes['default']['outputMethod'];
    }
}