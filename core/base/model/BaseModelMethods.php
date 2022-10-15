<?php

namespace core\base\model;

use function Sodium\add;

abstract class BaseModelMethods
{
    protected function createFields($set, $table = false)
    {
        $set['fields'] = (isset($set['fields']) && is_array($set['fields'])) ? $set['fields'] : ['*'];

        $table = $table . '.' ?? '';

        $fields = '';

        foreach ($set['fields'] as $field) {
            $fields .= $table . $field . ',';
        }

        return $fields;
    }

    protected function createOrder($set, $table = false)
    {
        $table = $table . '.' ?? '';

        $orderBy = '';

        if(isset($set['order']) && is_array($set['order'])) {
            $set['order_direction'] = (isset($set['order_direction']) && is_array($set['order_direction']))
                ? $set['order_direction'] : ['ASC'];

            $orderBy = 'ORDER BY ';

            $direct_count = 0;

            foreach ($set['order'] as $order) {
                if(isset($set['order_direction'][$direct_count])) {
                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;
                }else{
                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]);
                }

                if(is_int($order)) $orderBy .= $order . ' ' . $order_direction . ', ';
                else $orderBy .= $table . $order . ' ' . $order_direction . ', ';
            }

            $orderBy = rtrim($orderBy, ', ');
        }

        return $orderBy;
    }

    protected function createWhere($set, $table = false, $instruction = 'WHERE')
    {
        $table = $table . '.' ?? '';

        $where = '';

        if(isset($set['where']) && is_array($set['where'])) {
            $set['operand']   = (isset($set['operand']) && is_array($set['operand'])) ? $set['operand'] : ['='];
            $set['condition'] = (isset($set['condition']) && is_array($set['condition'])) ? $set['condition'] : ['AND'];

            $where = $instruction;

            $operand_count   = 0;
            $condition_count = 0;

            foreach ($set['where'] as $key => $value) {
                $where .= ' ';

                if(isset($set['operand'][$operand_count])) {
                    $operand = $set['operand'][$operand_count];
                    $operand_count++;
                }else{
                    $operand = $set['operand'][$operand_count - 1];
                }

                if(isset($set['condition'][$condition_count])) {
                    $condition = $set['condition'][$condition_count];
                    $condition_count++;
                }else{
                    $condition = $set['condition'][$condition_count - 1];
                }

                if($operand === 'IN' || $operand === 'NOT IN') {
                    //Если в $value находится SELECT, то оборачиваем его в "(SELECT ...)" и формируем $where
                    if(is_string($value) && strpos($value, 'SELECT') === 0) {
                        $in_str = $value;
                    }else{
                        // В любом случае делаю массив $temp_value
                        if(is_array($value)) $temp_value = $value;
                        else $temp_value = explode(',', $value);

                        $in_str = '';

                        // Оборачиваем значения в "'$v'"
                        foreach ($temp_value as $v) {
                            $in_str .= "'" . addslashes(trim($v)) . "',";
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . ' (' . trim($in_str, ',') . ') ' . $condition;
                }elseif(strpos($operand, 'LIKE') !== false) {
                    $like_template = explode('%', $operand);

                    foreach ($like_template as $lt_key => $lt_value) {
                        //Если нет $lt_value - в нём пустая строка и был '%',
                        // проверяем ключ,если его нет, он = 0, нужно приклеить '%' в начало строки
                        // если ключ есть - нужно приклеить в конец строки '%'
                        if(!$lt_value) {
                            if(!$lt_key) {
                                $value = '%' . $value;
                            }else{
                                $value .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . ' LIKE ' . "'" . addslashes($value) . "' $condition";
                }else{
                    // Проверяем если SELECT стоит в начале строки
                    // оборачиваем его в скобки (SELECT...)
                    if(strpos($value, 'SELECT') === 0) {
                        $where .= $table . $key . $operand . '(' . $value . ") $condition";
                    }else{
                        $where .= $table . $key . $operand . "'" . addslashes($value) . "' $condition";
                    }
                }
            }

            $where = substr($where, 0, strrpos($where, $condition));
        }

        return $where;
    }

    protected function createJoin($set, $table, $newWhere = false)
    {
        $fields = '';
        $join   = '';
        $where  = '';

        if(isset($set['join'])) {
            $join_table = $table;

            foreach ($set['join'] as $key => $value) {
                if(is_int($key)) {
                    // Делаю ключ равным названию таблицы, или же выхожу из текущей итерации
                    if(!$value['table']) continue;
                    else $key = $value['table'];
                }

                if($join) $join .= ' ';

                if(isset($value['on'])) {
                    $join_fields = [];

                    switch (2) {
                        case count($value['on']['fields'] ?? []):

                            $join_fields = $value['on']['fields'];

                            break;

                        case count($value['on']):

                            $join_fields = $value['on'];

                            break;

                        default:

                            // Выйти из второго уровня цикла то есть из foreach
                            continue 2;

                            break;
                    }

                    // Тип присоединения, если не указан - по дефолту LEFT JOIN
                    if(!$value['type']) $join .= 'LEFT JOIN ';
                    else $join .= trim(strtoupper($value['type'])) . ' JOIN ';

                    // Конкатенирую таблицу и признак ON
                    $join .= $key . ' ON ';

                    // Таблица с которой присоединяемся, если есть берём из массива
                    // если нет берём ту которая пришла в $join_table из $table
                    if(isset($value['on']['table'])) $join .= $value['on']['table'];
                    else $join .= $join_table;

                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[1];

                    $join_table = $key;

                    if($newWhere) {
                        if($value['where']) {
                            $newWhere = false;
                        }

                        $groupCondition = 'WHERE';
                    }else{
                        $groupCondition = isset($value['group_condition']) ? strtoupper($value['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($value, $key);
                    $where  .= $this->createWhere($value, $key, $groupCondition);
                }
            }
        }

        return compact('fields', 'join', 'where');
    }

    protected function createInsert($fields, $files, $except)
    {
        $insertArr = [];

        $insertArr['fields'] ??= '';
        $insertArr['values'] ??= '';

        if($fields) {
            $sqlFunctions = ['NOW()'];

            foreach ($fields as $row => $value) {
                // Есть исключение из филдов - выхожу из текущей итерации
                if($except && in_array($row, $except)) {
                    continue;
                }

                $insertArr['fields'] .= $row . ',';

                if(in_array($value, $sqlFunctions)) {
                    $insertArr['values'] .= $value . ',';
                }else {
                    $insertArr['values'] .= "'" . addslashes($value) . "',";
                }
            }
        }

        if($files) {
            foreach ($files as $fileKey => $fileValue) {
                $insertArr['fields'] .= $fileKey . ',';

                if(is_array($fileValue)) {
                    $insertArr['values'] .= "'" . addslashes(json_encode($fileValue)) . "',";
                }else{
                    $insertArr['values'] .= "'" . addslashes($fileValue) . "',";
                }
            }
        }

        // Меняю исходный массив, убираю запятую в конце
        foreach ($insertArr as $key => $arr) {
            $insertArr[$key] = rtrim($arr, ',');
        }

        return $insertArr;
    }

    protected function createUpdate($fields, $files, $except)
    {

    }
}