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
     * @return void
     */
    final public function get(string $table, array $set = [])
    {
        $fields   = $this->createFields($table, $set);
        $where    = $this->createWhere($table, $set);
        $join_arr = $this->createJoin($table, $set);

        $fields .= $join_arr['fields'];
        $join    = $join_arr['join'];
        $where  .= $join_arr['where'];

        $fields = rtrim($fields,',');

        $order = $this->createOrder($table, $set);

        $limit = $set['limit'] ?? '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

    protected function createFields($table = false, $set)
    {
        $set['fields'] = (isset($set['fields']) && is_array($set['fields'])) ? $set['fields'] : ['*'];

        $table = $table . '.' ?? '';

        $fields = '';

        foreach ($set['fields'] as $field) {
            $fields .= $table . $field . ',';
        }

        return $fields;
    }

    protected function createOrder($table = false, $set)
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

                $orderBy .= $table . $order . ' ' . $order_direction . ', ';
            }

            $orderBy = rtrim($orderBy, ', ');
        }

        return $orderBy;
    }

    protected function createWhere($table = false, $set, $instraction = 'WHERE')
    {
        $table = $table . '.' ?? '';

        $where = '';

        if(isset($set['where']) && is_array($set['where'])) {
            $set['operand']   = (isset($set['operand']) && is_array($set['operand'])) ? $set['operand'] : ['='];
            $set['condition'] = (isset($set['condition']) && is_array($set['condition'])) ? $set['condition'] : ['AND'];

            $where = $instraction;

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
                    if(is_string($value) && strpos($value, 'SELECT')) {
                        $in_str = $value;
                    }else{
                        if(is_array($value)) $temp_value = $value;
                            else $temp_value = explode(',', $value);

                        $in_str = '';

                        foreach ($temp_value as $v) {
                            $in_str .= "'" . trim($v) . "',";
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . ' (' . trim($in_str, ',') . ') ' . $condition;
                }elseif(strpos($operand, 'LIKE') !== false) {
                    $like_template = explode('%', $operand);

                    foreach ($like_template as $lt_key => $lt_value) {
                        if(!$lt_value) {
                            if(!$lt_key) {
                                $value = '%' . $value;
                            }else{
                                $value .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . ' LIKE ' . "'" . $value . "' $condition";
                }else{
                    if(strpos($value, 'SELECT') === 0) {
                        $where .= $table . $key . $operand . '(' . $value . ") $condition";
                    }else{
                        $where .= $table . $key . $operand . "'" . $value . "' $condition";
                    }
                }
            }

            $where = substr($where, 0, strrpos($where, $condition));
        }

        return $where;
    }
}