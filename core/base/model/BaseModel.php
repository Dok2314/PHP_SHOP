<?php

namespace core\base\model;

use core\base\controller\Singleton;
use core\base\exceptions\DbException;

class BaseModel
{
    use Singleton;

    protected $db;

    private function __construct()
    {
        $this->db = @new \mysqli(HOST,USER,PASSWORD,DB_NAME);

        if($this->db->connect_error) {
            throw new DbException('Ошибка подключения к базе данных: '
                .$this->db->connect_errno . ' ' . $this->db->connect_error);
        }

        $this->db->query("SET NAMES UTF8");
    }

    final public function query($query, $crud = 'r', $return_id = false)
    {
        $result = $this->db->query($query);

        //affected_rows - число строк, затронутых предыдущей операцией MySQL, "-1" - обозначает ошибку
        if($this->db->affected_rows === -1) {
            throw new DbException('Ошибка в SQL запросе: '
                . $query . ' - ' . $this->db->errno . ' ' . $this->db->error
            );
        }

        switch ($crud) {
            case 'r':

                if($result->num_rows) {
                    $res = [];

                    for($i = 0; $i < $result->num_rows; $i++) {
                        $res[] = $result->fetch_assoc();
                    }

                    return $res;
                }

                return false;

                break;

            case 'c':

                if($return_id) return $this->db->insert_id;

                return true;

                break;

            default:

                return true;

                break;
        }
    }

    /**
     * @param string $table - Таблица базы данных
     * @param array $set
     * 'fields'           => ['id', 'name'],
     * 'where'            => ['fio' => 'Smirnov', 'name' => 'Oleg', 'surname' => 'Sergeevich'],
     * 'operand'          => ['=', '<>'],
     * 'condition'        => ['AND'],
     * 'order'            => ['fio', 'name'],
     * 'order_direction'  => ['ASC', 'DESC'],
     * 'limit'            => '1'
     * 'join' => [
     *      'table'             => 'teachers',
     *      'fields'            => ['id as j_id', 'name as j_name'],
     *      'type'              => 'left',
     *      'where'             => ['name' => 'Sasha'],
     *      'operand'           => ['='],
     *      'condition'         => ['OR'],
     *      'on'                => ['id', 'parent_id'],
     *      'group_condition'   => 'AND'
     *      ]
     *  ],
     *  'join_table1' => [
     *      'table'     => 'join_table2',
     *      'fields'    => ['id as j_id', 'name as j_name'],
     *      'type'      => 'left',
     *      'where'     => ['name' => 'Sasha'],
     *      'operand'   => ['='],
     *      'condition' => ['OR'],
     *      'on'        => [
     *      'table'  => 'teachers',
     *      'fields' => ['id', 'parent_id']
     *      ]
     *  ]
     * @return void
     */
    final public function get(string $table, array $set = [])
    {
        $fields = $this->createFields($set, $table);
        $order  = $this->createOrder($set, $table);
        $where  = $this->createWhere($set, $table);

        if(!$where) $newWhere = true;
            else $newWhere = false;

        $join_arr = $this->createJoin($set, $table, $newWhere);

        $fields .= $join_arr['fields'];
        $join    = $join_arr['join'];
        $where  .= $join_arr['where'];

        $fields = rtrim($fields,',');

        if(isset($set['limit'])) $limit = 'LIMIT ' . $set['limit'];
            else $limit = '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

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
                if($set['order_direction'][$direct_count]) {
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
                        //Если нет $lt_value - значит в нём пустая строка и был '%',
                        // проверяем ключ,если его нет, значит он = 0, значит нужно приклеить '%' в начало сторки
                        // если ключ есть значит нужно приклеить в конец строки '%'
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
                    if(!$value['table']) continue;
                        else $key = $value['table'];
                }

                if($join) $join .= ' ';

                if($value['on']) {
                    $join_fields = [];

                    switch (2) {
                        case count($value['on']['fields']):

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

                    if(!$value['type']) $join .= 'LEFT JOIN ';
                        else $join .= trim(strtoupper($value['type'])) . ' JOIN ';

                    $join .= $key . ' ON ';

                    if($value['on']['table']) $join .= $value['on']['table'];
                        else $join .= $join_table;

                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[1];

                    $join_table = $key;

                    if($newWhere) {

                        if($value['where']) {
                            $newWhere       = false;
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
}