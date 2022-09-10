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

        if(strrpos($address_str, '/') === strlen($address_str) - 1 && strrpos($address_str, '/') !== 0) {
            // $this->redirect(rtrim($address_str, '/'), 301);
        }

        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'],'index.php'));

        if($path === PATH) {
            $this->routes = Settings::getPropertyByName('routes');

            if(!$this->routes) throw new RouteException("Сайт находится на техническом обслуживании!");

            if(strpos($address_str, $this->routes['admin']['alias']) === strlen(PATH)) {
                //ADMIN PANEL

                $url = explode('/', substr($address_str, strlen(PATH.$this->routes['admin']['alias']) +1));

                if(isset($url[0]) && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'].$url[0])) {
                    // Plugins

                    $plugin = array_shift($url);

                    $pluginSettings = $this->routes['settings']['path'].ucfirst($plugin.'Settings');

                    if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')) {
                        $pluginSettings = str_replace('/', '\\', $pluginSettings);

                        $this->routes = $pluginSettings::getPropertyByName('routes');

                        $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
                        $dir = str_replace('//','/', $dir);

                        $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;

                        $hrUrl = $this->routes['plugins']['hrUrl'];

                        $route = 'plugins';
                    }
                }else{
                    $this->controller = $this->routes['admin']['path'];

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';
                }
            }else{
                // User
                $url = explode('/', substr($address_str, strlen(PATH)));

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            $this->createRoute($route, $url);

            // Parameters
            if(isset($url[1])) {
                $count = count($url);

                $key = '';

                if(!$hrUrl) {
                    $i = 1;
                }else{
                    $this->parameters['alias'] = $url[1];
                    $i = 2;
                }

                for (; $i < $count; $i++) {
                    if(!$key) {
                        $key = $url[$i];
                        $this->parameters[$key] = '';
                    }else{
                        $this->parameters[$key] = $url[$i];
                        $key = '';
                    }
                }
                var_dump($this->parameters);
            }
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

        if(!empty($url[0])) {
            if(isset($this->routes[$routeName]['routes'][$url[0]])) {
                $route = explode('/', $this->routes[$routeName]['routes'][$url[0]]);

                $this->controller .= ucfirst($route[0].'Controller');
            }else{
                $this->controller .= ucfirst($url[0].'Controller');
            }
        }else{
            $this->controller .= $this->routes['default']['controller'];
        }

        $this->inputMethod  = $route[1] ?? $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ?? $this->routes['default']['outputMethod'];
    }
}