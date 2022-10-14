<?php

namespace core\base\model;

use core\base\controller\Singleton;
use core\base\exceptions\DbException;

class BaseModel extends BaseModelMethods
{
    use Singleton;

    protected $db;

    private function __construct()
    {
        $this->db = @new \mysqli(HOST,USER,PASSWORD,DB_NAME);

        if($this->db->connect_error) {
            throw new DbException('<h1 style="color: black;">' . 'Ошибка подключения к базе данных: ' . '</h1>' .
                '<h3 style="color: red;">' .$this->db->connect_errno . ' ' . $this->db->connect_error . '</h3>');
        }

        $this->db->query("SET NAMES UTF8");
    }

    /**
     * @param $query
     * @param string $crud = 'r' - SELECT/ 'c' - INSERT/ 'u' - UPDATE/ 'd' - DELETE
     * @param bool $return_id
     * @return array|bool
     * @throws DbException
     */
    final public function query($query, string $crud = 'r', $return_id = false)
    {
        $result = $this->db->query($query);

        //affected_rows - число строк, затронутых предыдущей операцией MySQL, "-1" - обозначает ошибку
        if($this->db->affected_rows === -1) {
            throw new DbException('<h1 style="color: black;">'.'Ошибка в SQL запросе: ' . '</h1>' . '<h3 style="color: red;">'
                . $query . ' - ' . $this->db->errno . ' ' . $this->db->error . '</h3>'
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

        // Первый раз или нет делаем $where
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

    /**
     * @param $table - таблица для вставки данных;
     * @param array $set - массив параметров;
     * fields => [поле => значение]; если не указан, то обрабатывается $_POST[поле => значение]
     * разрешена передача например NOW() в качестве MySQL функции обычной строкой
     * files = [поле => значение]; можно подать массив вида [поле => [массив значений]]
     * except => ['исключение 1', 'исключение 2'] - исключает данные элементы массива из добавления в запрос
     * return_id => true/false - возвращать или нет идентификатор вставленой записи
     * @return mixed
     */
    final public function add($table, $set)
    {
        $set['fields']    = (isset($set['fields']) && is_array($set['fields'])) ? $set['fields'] : false;
        $set['files']     = (isset($set['files']) && is_array($set['files'])) ? $set['files'] : false;

        // (bool)$set['return_id'] - Существует $set['return_id'] вернется true, не существует - вернется false
        $set['return_id'] = isset($set['return_id']);
        $set['except']    = (isset($set['except']) && is_array($set['except'])) ? $set['except'] : false;

        $insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);

        if(isset($insert_arr)) {
            $query = "INSERT INTO $table ({$insert_arr['fields']}) VALUES ({$insert_arr['values']})";

            return $this->query($query, 'c', $set['return_id']);
        }

        return false;
    }
}