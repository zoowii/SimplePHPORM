<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-28
 * Time: 上午1:18
 * To change this template use File | Settings | File Templates.
 */
abstract class Model
{
    // 如果对象有没人值，可以给具体的model添加有默认值得属性，比如public $has_read=false
    protected static $tableName;
    protected $isNewRecord = true;
    protected static $pkColumn;
    protected static $columnTypes;
    protected static $relations = array(); // 外键，eg. array('name' => array('column' => '?', 'model' => 'model_name', 'key' => 'mapped_column_name'))
    protected $data = array();
    protected static $onlyReadColumns = array(); // TODO: 只从数据库中读取，但不写入数据库的字段，比如id, 自动时间等

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        $class = get_called_class();
        if (in_array($name, array_keys($class::$relations))) {
            $value = $this->data[$name];
            if (!$value) {
                $columnName = $class::$relations[$name]['column'];
                $key_value = $this->$columnName;
                $joinClass = $class::$relations[$name]['model'];
                $joinKey = $class::$relations[$name]['key'];
                $model = $joinClass::findOneByAttributes(array($joinKey => $key_value));
                $this->data[$name] = $model;
                return $model;
            } else {
                return $value;
            }
        } else {
            return $this->data[$name];
        }
    }

    // 字段在sql中时字段值对应的sql字符串，即是否加单引号
    private static function columnSqling($columnType, $columnValue)
    {
        if (in_array($columnType, array('string', 'datetime', 'date', 'time', 'timestamp', 'enum'))) {
            return "'$columnValue'";
        } else {
            if ($columnValue === null) {
                return 'null';
            } else if (is_bool($columnValue)) {
                return $columnValue ? 'true' : 'false';
            }
            return $columnValue;
        }
    }

    /**
     * load record from db to update the model object
     */
    public function refresh()
    {
        if ($this->isNewRecord) {
            return false;
        } else {
            $pk = $this::$pkColumn;
            $obj = self::findByPk(get_class($this), $this->$pk);
            foreach ($this::$columnTypes as $column => $value) {
                $this->$column = $obj->$column;
            }
            foreach ($this::$relations as $column => $value) {
                $this->$column = null;
            }
            return true;
        }
    }

    public function save()
    {
        if ($this->isNewRecord) {
            $sql = "insert into " . TABLE_PREFIX . $this::$tableName;
            $bindParams = array();
            $column_string = ' (';
            $value_string = '(';
            $columns = $this::$columnTypes;
            $i = 0;
            foreach ($columns as $column => $value) {
                if ($column == $this::$pkColumn) {
                    continue;
                }
                if ($i > 0) {
                    $column_string .= ',';
                    $value_string .= ',';
                }
                $column_string .= "`$column`";
                $value_string .= '?';
                $bindParams[] = $this->$column;
                $i++;
            }
            $column_string .= ')';
            $value_string .= ')';
            $sql .= $column_string . ' values ' . $value_string;
            $stmt = Simu::db()->prepare($sql);
            $result = $stmt->execute($bindParams);
            $this->isNewRecord = false;
            $pk = $this::$pkColumn;
            $this->$pk = Simu::db()->lastInsertId();
            return $result;
        } else {
            return $this->update();
        }
    }

    public function update()
    {
        if ($this->isNewRecord) {
            return false; // only record in db can be update
        }
        $sql = "update " . TABLE_PREFIX . $this::$tableName . ' t';
        $bindParams = array();
        $set_string = ' set';
        $class = get_class($this);
        $columns = $class::$columnTypes;
        $i = 0;
        foreach ($columns as $column => $value) {
            if ($i > 0) {
                $set_string .= ",";
            }
            $set_string .= " `$column`=?";
            $bindParams[] = $this->$column;
            $i++;
        }
        $pk = $class::$pkColumn;
        $where_string = " where `$pk`=" . self::columnSqling($class::$columnTypes[$pk], $this->$pk);
        $sql .= $set_string . $where_string;
        $stmt = Simu::db()->prepare($sql);
        return $stmt->execute($bindParams);
    }

    public function delete()
    {
        $class = get_class($this);
        $pk = $this::$pkColumn;
        $sql = "delete from " . TABLE_PREFIX . $this::$tableName . " t where `$pk`=?";
        $bindParams = array($this->$pk);
        $stmt = Simu::db()->prepare($sql);
        return $stmt->execute($bindParams);
    }

    protected static function getRealTableName()
    {
        $cls = get_called_class();
        return TABLE_PREFIX . $cls::$tableName;
    }

    /**
     * @param $attr
     * @param array $with: 要在查询数据库时一起查询的relation的列表，即靠外键连接的对象
     * 使用方式比如：ModelA::findAllByAttributes(array('has_read'='false'), array('user'))
     * @return array
     * @throws Exception
     */
    public static function findAllByAttributes($attr, $order = '', $with = array(), $limit = null, $offset = null)
    {
        $model_name = get_called_class();
        $conn = Simu::db();
        if (!is_array($attr) || count($attr) <= 0) {
            $where_string = '';
        } else {
            $where_string = ' where';
            $i = 0;
            foreach ($attr as $col => $val) {
                if ($i > 0) {
                    $where_string .= ' and';
                }
                $where_string .= " t.`$col`=" . self::columnSqling($model_name::$columnTypes[$col], $val);
                $i++;
            }
        }
        $from_string = ' from ' . $model_name::getRealTableName() . ' t';
        if (!is_array($with) || count($with) <= 0) {
            $select_string = ' *';
        } else {
            // 使用 字段名 和 relation名（比如user_id对应的user)$字段名的方式区分不同字段，
            // 前者表示本张表的字段，后者表示外键对应的表的字段
            $select_string = ' ';
            $i = 0;
            foreach ($model_name::$columnTypes as $col => $type) {
                if ($i > 0) {
                    $select_string .= ',';
                }
                $select_string .= "t.`$col`";
                $i++;
            }
            $i = 0;
            foreach ($with as $name) {
                $select_string .= ',';
                $rel = $model_name::$relations[$name];
                if ($where_string != '') {
                    $where_string .= ' and';
                } else {
                    $where_string .= ' where';
                }
                $where_string .= ' t.`' . $rel['column'] . "`=t$i.`" . $rel['key'] . "`";
                $from_string .= ',' . $rel['model']::getRealTableName() . " t$i";
                $j = 0;
                foreach ($rel['model']::$columnTypes as $col => $type) {
                    if ($j > 0) {
                        $select_string .= ',';
                    }
                    $select_string .= "t$i.`$col` as `$name$$col`";
                    $j++;
                }
                $i++;
            }
        }
        $order_string = $order === '' ? '' : " order by $order";
        $sql = "select" . $select_string . $from_string . $where_string . $order_string;
        if (is_numeric($offset)) {
            $sql .= " offset $offset";
        }
        if (is_numeric($limit)) {
            $sql .= " limit $limit";
        }
        $rows = $conn->query($sql);
        if ($rows == false) {
            var_dump($conn->errorInfo());
            throw new Exception('DB error');
        }
        $objects = array();
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                if (!is_numeric($key)) {
                    $data[$key] = $value;
                }
            }
            $model = self::loadFromArray($model_name, $data);
            $objects[] = $model;
        }
        return $objects;
    }

    /**
     * @param $model_name
     * @param $data
     * @return Model
     */
    private static function loadFromArray($model_name, $data)
    {
        if (!is_array($data) || !is_string($model_name) || $model_name === '') {
            return null;
        } else {
            $model = self::createFromDb($model_name);
            $rels = array(); // related objects
            foreach ($data as $name => $value) {
                $idx = strpos($name, '$');
                if ($idx === false) { // 没有$符号，表示不是外键对象的属性
                    $model->$name = $value;
                } else {
                    $rels[substr($name, 0, $idx)][substr($name, $idx + 1)] = $value;
                }
            }
            foreach ($rels as $name => $rel) {
                $m = $model_name::$relations[$name]['model'];
                $model->$name = self::loadFromArray($m, $rel);
            }
            return $model;
        }
    }

    /**
     * @param $id
     * @param string $order
     * @param array $with
     * @param null $offset
     * @return Model
     */
    public static function findByPk($id, $order = '', $with = array(), $offset = null)
    {
        $model_name = get_called_class();
        $pk = $model_name::$pkColumn;
        return $model_name::findOneByAttributes(array($pk => $id), $order, $with, $offset);
    }

    /**
     * @param $attr
     * @param string $order
     * @param array $with
     * @param null $offset
     * @return Model
     */
    public static function findOneByAttributes($attr, $order = '', $with = array(), $offset = null)
    {
        $cls = get_called_class();
        $objs = $cls::findAllByAttributes($attr, $order, $with, 1, $offset);
        if (count($objs) <= 0) {
            return null;
        } else {
            return $objs[0];
        }
    }

    /**
     * @param $condition sql条件信息
     * @param array $with 要通过级联查询的relations中的字段，也就是外键对应的表
     * @return array
     * @throws Exception
     */
    public static function findAll($condition, $order = '', $with = array(), $limit = null, $offset = null)
    {
        $model_name = get_called_class();
        $conn = Simu::db();
        $where_string = (is_null($condition) || $condition === '') ? '' : " where $condition";
        $from_string = ' from ' . $model_name::getRealTableName() . ' t';
        if (!is_array($with) || count($with) <= 0) {
            $select_string = ' *';
        } else {
            // 使用 字段名 和 relation名（比如user_id对应的user)$字段名的方式区分不同字段，
            // 前者表示本张表的字段，后者表示外键对应的表的字段
            $select_string = ' ';
            $i = 0;
            foreach ($model_name::$columnTypes as $col => $type) {
                if ($i > 0) {
                    $select_string .= ',';
                }
                $select_string .= "t.`$col`";
                $i++;
            }
            $i = 0;
            foreach ($with as $name) {
                if ($where_string != '') {
                    $where_string .= ' and';
                } else {
                    $where_string .= ' where';
                }
                $select_string .= ',';
                $rel = $model_name::$relations[$name];
                $where_string .= ' t.`' . $rel['column'] . "`=t$i.`" . $rel['key'] . "`";
                $from_string .= ',' . $rel['model']::getRealTableName() . " t$i";
                $j = 0;
                foreach ($rel['model']::$columnTypes as $col => $type) {
                    if ($j > 0) {
                        $select_string .= ',';
                    }
                    $select_string .= "t$i.`$col` as `$name$$col`";
                    $j++;
                }
                $i++;
            }
        }
        $order_string = $order === '' ? '' : " order by $order";
        $sql = "select" . $select_string . $from_string . $where_string . $order_string;
        if (is_numeric($offset)) {
            $sql .= " offset $offset";
        }
        if (is_numeric($limit)) {
            $sql .= " limit $limit";
        }
        $rows = $conn->query($sql);
        if ($rows == false) {
            var_dump($conn->errorInfo());
            throw new Exception('DB error');
        }
        $objects = array();
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                if (!is_numeric($key)) {
                    $data[$key] = $value;
                }
            }
            $model = self::loadFromArray($model_name, $data);
            $objects[] = $model;
        }
        return $objects;
    }

    /**
     * @param $condition
     * @param string $order
     * @param array $with
     * @param null $offset
     * @return Model
     */
    public static function findOne($condition, $order = '', $with = array(), $offset = null)
    {
        $cls = get_called_class();
        $objs = $cls::findAll($condition, $order, $with, 1, $offset);
        if (count($objs) <= 0) {
            return null;
        } else {
            return $objs[0];
        }
    }

    private static function createFromDb($model_name)
    {
        $model = new $model_name();
        $model->isNewRecord = false;
        return $model;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $res = array();
        foreach ($this::$columnTypes as $column => $type) {
            $res[$column] = $this->$column;
        }
        foreach ($this->data as $column => $value) {
            if (instance_of($value, self)) {
                $res[$column] = $value->toArray();
            } else {
                $res[$column] = $value;
            }
        }
        return $res;
    }

}
