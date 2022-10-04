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
                if(isset($set['order_direction'][$direct_count])){
                    $order_direction = $set['order_direction'][$direct_count];
                    $direct_count++;
                }else{
                    $order_direction = $set['order_direction'][$direct_count - 1];
                }

                $orderBy .= $table . $order . ' ' . $order_direction . ', ';
            }

            $orderBy = rtrim($orderBy, ', ');
        }

        return $orderBy;
    }
}