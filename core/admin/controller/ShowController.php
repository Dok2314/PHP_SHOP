<?php

namespace core\admin\controller;

use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class ShowController extends BaseAdmin
{
    protected function inputData()
    {
        $this->execBase();

        $this->createTableData();

        $this->createData([
            'fields' => 'content'
        ]);

        return $this->expansion(get_defined_vars());
    }

    protected function outputData()
    {
    }

    protected function createData(array $arr = [])
    {
        $fields         = [];
        $order          = [];
        $orderDirection = [];

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
                $orderDirection[] = 'DESC';
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

        // ORDER parent_id - более приоритетно чем menu_position
        $this->data = $this->model->get($this->table, [
            'fields'          => $fields,
            'order'           => $order,
            'order_direction' => ['ASC', 'DESC']
        ]);
    }
}