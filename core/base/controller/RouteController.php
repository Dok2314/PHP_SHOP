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
        $address_str = $_SERVER['REQUEST_URI'];

        // Если последнее вхождение '/' в строку равно длинне строки - 1 и последнее вхождение не равно корню сайта (0)
        // то редирект на страницу без этого слеша '/' со статусом 301, это нужно для СЕО, потому что строка /test/ и /test -
        // это две разные строки, соответственно это две разные страницы
        if(strrpos($address_str, '/') === strlen($address_str) - 1 && strrpos($address_str, '/') !== 0) {
        //  $this->redirect(rtrim($address_str, '/'), 301);
        }

        // $_SERVER['PHP_SELF'] - имя файла скрипта, который сейчас выполняется, относительно корня документов,
        // так как единая точка входа это index.php, $_SERVER['PHP_SELF'] = /index.php
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        if($path == PATH) {
            $this->routes = Settings::get('routes');

            if(!$this->routes) throw new RouteException('Сайт находится на техническом обслуживании!');

            // Если в адресе элиас админа('admin') идёт сразу после слеша, значит мы стучимся в админку
            if(strpos($address_str, $this->routes['admin']['alias']) === strlen(PATH)) {
                // Обрезаю '/admin/' и все что после него разбиваю по слешу '/' и кладу в $url
                $url = explode('/', substr($address_str, strlen(PATH.$this->routes['admin']['alias']) + 1));

                // Если после '/admin/' идёт директория и она лежит в папке плагинов то мы работаем с плагином
                if($url[0] && is_dir($_SERVER['DOCUMENT_ROOT'].PATH.$this->routes['plugins']['path'].$url[0])) {
                    $plugin = array_shift($url);

                    // Путь к настройкам плагина
                    $pluginSettings = $this->routes['settings']['path'].ucfirst($plugin.'Settings');

                    // Проверяю существует ли класс настроек плагина
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].PATH.$pluginSettings.'.php')) {
                        //Переопределяю свойство routes из настроек
                        $pluginSettings = str_replace('/', '\\', $pluginSettings);
                        $this->routes = $pluginSettings::get('routes');
                    }

                    $dir = $this->routes['plugins']['dir'] ? '/'.$this->routes['plugins']['dir'].'/' : '/';

                    // Ищем 2 слеша и заменяем на один чтобы всегда директория имела вид '/dir/' если она прописана
                    $dir = str_replace('//','/', $dir);

                    $this->controller = $this->routes['plugins']['path'].$plugin.$dir;

                    $hrUrl = $this->routes['plugins']['hrUrl'];

                    $route = 'plugins';
                }else{
                    $this->controller = $this->routes['admin']['path'];

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';
                }
            }else {
                // Запрошеный адрес разбитый на массив без начального слеша '/'
                $url = explode('/', substr($address_str, strlen(PATH)));

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            $this->createRoute($route, $url);

            if($url[1]) {
                $count = count($url);
                $key   = '';

                if(!$hrUrl) {
                    $i = 1;
                }else{
                    $this->parameters['alias'] = $url[1];
                    $i = 2;
                }

                for(;$i < $count; $i++) {
                    if(!$key) {
                        $key = $url[$i];
                        $this->parameters[$key] = '';
                    }else{
                        $this->parameters[$key] = $url[$i];
                        $key = '';
                    }
                }
            }

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
                $route = explode('/', $this->routes[$routeStr]['routes'][$url[0]]);

                $this->controller .= ucfirst($route[0].'Controller');
            }else {
                $this->controller .= ucfirst($url[0].'Controller');
            }
        }else {
            $this->controller = $this->routes['default']['controller'];
        }

        $this->inputMethod  = $route[1] ?? $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ?? $this->routes['default']['outputMethod'];
    }
}