<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseAdmin extends BaseController
{
    protected $model;

    protected $table;
    protected $columns;
    protected $data;

    protected $menu;
    protected $title;

    protected function inputData()
    {
        $this->init(true);

        $this->title = 'DOK';

        if(!$this->model) {
            $this->model = Model::instance();
        }

        if(!$this->menu) {
            $this->menu = Settings::getPropertyByName('projectTables');
        }

        $this->sendNoCacheHeaders();
    }

    protected function outputData()
    {
    }

    protected function sendNoCacheHeaders()
    {
        header("Last-Modified: " . gmdate("D, d m Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0,pre-check=0");
    }

    protected function execBase()
    {
        self::inputData();
    }

    protected function createTableData()
    {
        if(!$this->table) {
            if($this->parameters) {
                $this->table = array_keys($this->parameters)[0];
            }else{
                $this->table = Settings::getPropertyByName('defaultTable');
            }
        }

        $this->columns = $this->model->showColumns($this->table);

        if(!$this->columns) {
            throw new RouteException('Не найдены поля в таблице - ' . $this->table, 2);
        }
    }

    protected function createData(array $arr = [], bool $add = true)
    {
        $fields         = [];
        $order          = [];
        $orderDirection = [];

        if($add) {
            if(!isset($this->columns['id_row'])) {
                return $this->data = [];
            }

            $fields[] = $this->columns['id_row'] . ' as id';

            if(isset($this->columns['name'])) {
                $fields['name'] = 'name';
            }

            if(isset($this->columns['img'])) {
                $fields['img'] = 'img';
            }

            if(count($fields) < 3) {
                foreach ($this->columns as $columnKey => $columnValue) {
                    if(!isset($fields['name']) && strpos($columnKey, 'name') !== false) {
                        $fields['name'] = $columnKey . ' as name';
                    }

                    // одиночное изображение должно начинатся с img...
                    if(!isset($fields['img']) && strpos($columnKey, 'img') === 0) {
                        $fields['img'] = $columnKey . ' as img';
                    }
                }
            }

            if(isset($arr['fields'])) {
                if(is_array($arr['fields'])) {
                    $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
                }else{
                    $fields[] = $arr['fields'];
                }
            }

            if(isset($this->columns['parent_id'])) {
                if(!in_array('parent_id', $fields)) {
                    $fields[] = 'parent_id';
                }

                $order[]  = 'parent_id';
            }

            if(isset($this->columns['menu_position'])) {
                $order[] = 'menu_position';
            }elseif (isset($this->columns['date'])) {
                if($order) {
                    $orderDirection = ['ASC', 'DESC'];
                }else{
                    $orderDirection[] = ['DESC'];
                }

                $order[] = 'date';
            }

            if(isset($arr['order'])) {
                if(is_array($arr['order'])) {
                    $order = Settings::instance()->arrayMergeRecursive($order, $arr['order']);
                }else {
                    $order[] = $arr['order'];
                }
            }

            if(isset($arr['order_direction'])) {
                if(is_array($arr['order_direction'])) {
                    $orderDirection = Settings::instance()->arrayMergeRecursive($orderDirection, $arr['order_direction']);
                }else{
                    $orderDirection[] = $arr['order_direction'];
                }
            }
        }else {
            if(!$arr) {
                return $this->data = [];
            }

            $fields         = $arr['fields'];
            $order          = $arr['order'];
            $orderDirection = $arr['orderDirection'];
        }

        // ORDER parent_id - более приоритетно чем menu_position
        $this->data = $this->model->get($this->table, [
            'fields'          => $fields,
            'order'           => $order,
            'order_direction' => ['ASC', 'DESC']
        ]);
    }

    protected function expansion(array $args = [])
    {
        $filename  = explode('_', $this->table);
        $className = '';

        foreach ($filename as $item) {
            $className .= ucfirst($item);
        }

        $class = Settings::getPropertyByName('expansion') . $className . 'Expansion';

        if(is_readable($_SERVER['DOCUMENT_ROOT'] . PATH . $class . '.php')) {
            $class = str_replace('/', '\\', $class);

            $exp = $class::instance();

            $res = $exp->expansion($args);
        }
    }
}