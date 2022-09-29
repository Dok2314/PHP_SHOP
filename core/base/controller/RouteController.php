<?php

namespace core\base\controller;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

class RouteController extends BaseController
{
    use Singleton;

    protected array $routes;

    private function __construct()
    {
        $address_str = $_SERVER['REQUEST_URI'];

        if(strrpos($address_str, '/') === strlen($address_str) - 1 && strrpos($address_str, '/') !== 0) {
             $this->redirect(rtrim($address_str, '/'), 301);
        }

        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        if($path === PATH) {
            $this->routes = Settings::getPropertyByName('routes');

            if(!$this->routes) throw new RouteException('Отсутствуют маршруты в базовых настройках!', 1);

            $url = explode('/', substr($address_str, strlen(PATH)));

            if(!empty($url[0]) && $url[0] === $this->routes['admin']['alias']) {

                array_shift($url);

                if(!empty($url[0]) && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $url[0])) {
                    // PLUGIN
                    $plugin = array_shift($url);

                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin.'Settings');

                    if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')) {
                        $pluginSettings = str_replace('/','\\', $pluginSettings);

                        $this->routes = $pluginSettings::getPropertyByName('routes');
                    }

                    // $dir - если захотим хранить контроллеры плагинов в отдельной папке, а не в папке с названием = названию плагина
                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
                    $dir = str_replace('//', '/', $dir);

                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;

                    $hrUrl = $this->routes['plugins']['hrUrl'];

                    $route = 'plugins';
                }else{
                    // ADMIN PANEL
                    $this->controller = $this->routes['admin']['path'];

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';
                }
            }else{
                // USER
                $this->controller = $this->routes['user']['path'];

                $hrUrl = $this->routes['user']['hrUrl'];

                $route = 'user';
            }

            $this->createRoute($route, $url);

            // Parameters
            if(isset($url[1])) {
                $count = count($url);
                $key = '';

                if(!$hrUrl) {
                    // WITHOUT HUMAN READABLE URL
                    $i = 1;
                }else{
                    // FOR HUMAN READABLE URL
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
            }
        }else{
            throw new RouteException('Не корректная директория сайта!', 1);
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