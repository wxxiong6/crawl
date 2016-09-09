<?php
namespace library\db;

use PDO;
use Exception;
use PDOException;

/**
 * PDO 数据库类
 *
 * @author
 *
 */
class MysqlPDO
{

    /**
     * 数据表前缀
     */
    protected $tablePrefix = '';

    /*
     * 最后执行的一条SQL语句
     */
    public $lastSql;

    /**
     * 受影响行数
     */
    public $num_rows;

    /**
     * 执行SQL语句
     */
    private $_arrSql;

    /**
     * 数据库配置
     */
    private $_config;

    /**
     * 表链接
     */
    private static $conn = NULL;

    /**
     * 构造函数
     */
    public function __construct(array $config)
    {
        if (! class_exists("PDO"))
            throw new PDOException('PHP环境未安装PDO函数库！');

        $this->_config = $config;
        if (! is_array($config)) {
            throw new PDOException('Adapter parameters must be in an array !');
        }
    }

    /**
     * 数据库连接
     */
    private function  conn()
    {
        if (! isset($this->_config['host'])) {
            throw new PDOException("HOTS不能为空");
        }
        if (! isset($this->_config['user'])) {
            throw new PDOException("用户名不能为空");
        }
        if (! isset($this->_config['password'])) {
            throw new PDOException("密码不能为空");
        }
        if (! isset($this->_config['tablePrefix'])) {
            throw new PDOException("tablePrefix 表前缀不存在");
        }
        if (! isset($this->_config['charset'])) {
            $this->_config['charset'] = 'utf8';
        }
        $this->tablePrefix = $this->_config['tablePrefix'];
        try {
            self::$conn = new PDO(
                $this->_config['host'],
                $this->_config['user'],
                $this->_config['password'],
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->_config['charset']}"
                )
            );
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * 从数据表中查找一条记录
     *
     * @param
     *            $table
     * @param
     *            conditions 查找条件，数组array("字段名"=>"查找值")或字符串，
     *            请注意在使用字符串时将需要开发者自行使用escape来对输入值进行过滤
     * @param
     *            sort 排序，等同于“ORDER BY ”
     * @param
     *            fields 返回的字段范围，默认为返回全部字段的值
     */
    public function find($table, $conditions = null, $sort = null, $fields = null)
    {
        if ($record = $this->findAll($table, $conditions, $sort, $fields, 1)) {
            return array_pop($record);
        } else {
            return FALSE;
        }
    }

    /**
     * 从数据表中查找记录
     *
     * @param
     *            $table
     * @param
     *            conditions 查找条件，数组array("字段名"=>"查找值")或字符串，
     *            请注意在使用字符串时将需要开发者自行使用escape来对输入值进行过滤
     * @param
     *            sort 排序，等同于“ORDER BY ”
     * @param
     *            fields 返回的字段范围，默认为返回全部字段的值
     * @param
     *
     *            如果limit值只有一个数字，则是指代从0条记录开始。
     *  @param    offset
     */
    public function findAll($table, $conditions = null, $sort = null, $fields = null, $limit = null , $offset = null)
    {
        $where = "";
        $fields = empty($fields) ? "*" : $fields;
        if (is_array($conditions)) {
            $join = array();
            foreach ($conditions as $key => $condition) {
                $condition = $this->escape($condition);
                $join[] = "`{$key}` = {$condition}";
            }
            $where = "WHERE " . join(" AND ", $join);
        } else {
            if (null != $conditions)
                $where = "WHERE " . $conditions;
        }
        if (null != $sort) {
            $sort = "ORDER BY `{$sort}`";
        }
        $table = $this->getTableNmae($table);
        $sql = "SELECT {$fields} FROM {$table} {$where} {$sort}";
        if (null != $limit){
            $sql = $this->setlimit($sql, $limit, $offset);
        }
        return $this->getArray($sql);
    }

    /**
     * 过滤转义字符
     *
     * @param
     *            value 需要进行过滤的值
     */
    public function escape($value)
    {
        return $this->__val_escape($value);
    }

    /**
     * 在数据表中新增一行数据
     *
     * @param
     *            row 数组形式，数组的键是数据表中的字段名，键对应的值是需要新增的数据。
     */
    public function insert($table, $row)
    {
        if (! is_array($row))
            return FALSE;
        $row = $this->__prepera_format($table, $row);
        if (empty($row))
            return FALSE;
        foreach ($row as $key => $value) {
            $cols[] = $key;
            $vals[] = $this->escape($value);
        }
        $col = '(`' . implode('`,`', $cols) . '`)';
        $val = implode(',', $vals);
        $table = $this->getTableNmae($table);
        $sql = "INSERT INTO $table {$col} VALUES ({$val})";
        if (FALSE != $this->exec($sql)) { // 获取当前新增的ID
            if ($newinserid = $this->lastInsertId()) {
                return $newinserid;
            }
        }
        return FALSE;
    }

    /**
     * 在数据表中新增多条记录
     *
     * @param
     *            rows 数组形式，每项均为create的$row的一个数组
     */
    public function insertAll($table, $rows)
    {
        $sql = $this->createInsert($table, $rows);
        return $this->exec($sql);
    }

    /**
     * 根据数组（支持一维二维）
     * 生成insert SQL语句
     */
    public function createInsert($table, array $data)
    {
        $table = $this->getTableNmae($table);
        $sql = 'INSERT INTO ' . $table;
        $flag = false; // 是否是二维数组
        foreach ($data as $key => $val) {
            if (is_array($val)) { // 二维数组
                $flag = true;
                $fields = array_keys($val);
                $values[] = "('" . implode("','", array_map('addslashes', $val)) . "')";
            } else { // 一维数组
                $values[] = $this->escape($val);
                $fields[] = $key;
            }
        }

        $sql .= ' (`' . implode('`,`', $fields) . '`) VALUES ';

        if ($flag) { // 二维数组
            $sql .= implode(',', $values) . ';';
        } else { // 一维数组
            $sql .= "('" . implode("','", $values) . "');";
        }
        return $sql;
    }

    /**
     * 按条件删除记录
     *
     * @param
     *            conditions 数组形式，查找条件，此参数的格式用法与find/findAll的查找条件参数是相同的。
     */
    public function delete($table, $conditions)
    {
        $where = "";
        if (is_array($conditions)) {
            $join = array();
            foreach ($conditions as $key => $condition) {
                $condition = $this->escape($condition);
                $join[] = "`{$key}` = {$condition}";
            }
            $where = "WHERE ( " . join(" AND ", $join) . ")";
        } else {
            if (null != $conditions)
                $where = "WHERE ( " . $conditions . ")";
        }
        $table = $this->getTableNmae($table);
        $sql = "DELETE FROM {$table} {$where}";
        return $this->exec($sql);
    }

    /**
     * 按字段值查找一条记录
     *
     * @param
     *            field 字符串，对应数据表中的字段名
     * @param
     *            value 字符串，对应的值
     */
    public function findBy($table, $field, $value)
    {
        return $this->find($table, array(
            $field => $value
        ));
    }

    /**
     * 返回最后执行的SQL语句供分析
     */
    public function getSqlList()
    {
        return $this->_arrSql;
    }

    /**
     * 返回最后执行的SQL语句供分析
     */
    public function getLastSql()
    {
        return array_pop($this->_arrSql);
    }

    /**
     * 返回上次执行update,create,delete,exec的影响行数
     */
    public function affectedRows()
    {
        return $this->num_rows;
    }

    /**
     * 计算符合条件的记录数量
     *
     * @param
     *            conditions 查找条件，数组array("字段名"=>"查找值")或字符串，
     *            请注意在使用字符串时将需要开发者自行使用escape来对输入值进行过滤
     */
    public function count($table, $conditions = null)
    {
        $where = "";
        if (is_array($conditions)) {
            $join = array();
            foreach ($conditions as $key => $condition) {
                $condition = $this->escape($condition);
                $join[] = "`{$key}` = {$condition}";
            }
            $where = "WHERE " . join(" AND ", $join);
        } else {
            if (null != $conditions)
                $where = "WHERE " . $conditions;
        }
        $table = $this->getTableNmae($table);
        $sql = "SELECT COUNT(*) AS SP_COUNTER FROM {$table} {$where}";
        $result = $this->getArray($sql);
        return $result[0]['SP_COUNTER'];
    }

    /**
     * 修改数据，该函数将根据参数中设置的条件而更新表中数据
     *
     * @param
     *            conditions 数组形式，查找条件，此参数的格式用法与find/findAll的查找条件参数是相同的。
     * @param
     *            row 数组形式，修改的数据，
     *            此参数的格式用法与create的$row是相同的。在符合条件的记录中，将对$row设置的字段的数据进行修改。
     */
    public function update($table, $conditions, $row)
    {
        $where = "";
        $row = $this->__prepera_format($table, $row);
        if (empty($row))
            return FALSE;
        if (is_array($conditions)) {
            $join = array();
            foreach ($conditions as $key => $condition) {
                $condition = $this->escape($condition);
                $join[] = "`{$key}` = {$condition}";
            }
            $where = "WHERE " . join(" AND ", $join);
        } else {
            if (null != $conditions)
                $where = "WHERE " . $conditions;
        }
        foreach ($row as $key => $value) {
            $value = $this->escape($value);
            $vals[] = "`{$key}` = {$value}";
        }
        $values = join(", ", $vals);
        $table = $this->getTableNmae($table);
        $sql = "UPDATE  SET {$values} {$where}";
        return $this->exec($sql);
    }

    /**
     * 按字段值修改一条记录
     *
     * @param
     *            conditions 数组形式，查找条件，此参数的格式用法与find/findAll的查找条件参数是相同的。
     * @param
     *            field 字符串，对应数据表中的需要修改的字段名
     * @param
     *            value 字符串，新值
     */
    public function updateField($table, $conditions, $field, $value)
    {
        return $this->update($table, $conditions, array(
            $field => $value
        ));
    }

    /**
     * 按给定的数据表的主键删除记录
     *
     * @param
     *            pk 字符串或数字，数据表主键的值。
     */
    public function deleteByPk($table, $pk)
    {
        $table = $this->getTableNmae($table);
        return $this->delete($table, array(
            'id' => $pk
        ));
    }

    /**
     * 按表字段调整适合的字段
     *
     * @param
     *            rows 输入的表字段
     */
    private function __prepera_format($table, $rows)
    {
        $columns = $this->getTableInfo($table);
        $newcol = array();
        foreach ($columns as $col) {
            $newcol[$col['Field']] = $col['Field'];
        }
        return array_intersect_key($rows, $newcol);
    }

    /**
     * 按SQL语句获取记录结果，返回数组
     *
     * @param
     *            sql 执行的SQL语句
     */
    public function getArray($sql)
    {
        $this->_arrSql[] = $sql;
        if (! $rows = $this->getConn()->prepare($sql)) {
            $poderror = $this->getConn()->errorInfo();
            throw new Exception("{$sql} 执行错误: " . $poderror[2]);
        }
        $rows->execute();
        return $rows->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 返回当前插入记录的主键ID
     */
    public function lastInsertId()
    {
        return $this->getConn()->lastInsertId();
    }

    /**
     * 格式化带limit的SQL语句
     * @param string $sql
     * @param int $limit
     * @param number $offset
     * @throws Exception
     * @return string
     */
    public function setlimit($sql, $limit, $offset = 0)
    {
        $limit = intval($limit);
        if ($limit <= 0) {
            throw new Exception("LIMIT argument limit=$limit is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $limit";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }
        return $sql;
    }

    /**
     * 执行一个SQL语句
     *
     * @param
     *            sql 需要执行的SQL语句
     */
    public function exec($sql)
    {
        $this->_arrSql[] = $sql;
        $result = $this->getConn()->exec($sql);
        if (FALSE !== $result) {
            $this->num_rows = $result;
            return $result;
        } else {
            $poderror = $this->getConn()->errorInfo();
            throw new Exception("{$sql} Execution error: " . $poderror[2]);
        }
        return false;
    }

    /**
     * 获取数据表结构
     * @param
     *            tbl_name 表名称
     */
    public function getTableInfo($table)
    {
        $table = $this->getTableNmae($table);
        $tableInfo = $this->getArray("DESCRIBE {$table}");
        if (empty($tableInfo))
            throw new PDOException('The' . $table . 'not exists');
        return $tableInfo;
    }

   /**
    * 获取表名
    * @param string $tbl_name
    * @return string
    */
    public function getTableNmae($table){
        return "`{$this->tablePrefix}{$table}`";
    }

    /**
     * 对特殊字符进行过滤
     *
     * @param
     *            value 值
     */
    private function __val_escape($value)
    {
        if (is_null($value))
            return 'NULL';
        if (is_bool($value))
            return $value ? 1 : 0;
        if (is_int($value))
            return (int) $value;
        if (is_float($value))
            return (float) $value;
        if (@get_magic_quotes_gpc())
            $value = stripslashes($value);
        return $this->getConn()->quote($value);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        self::$conn = null;
    }

    /**
     * getConn 取得PDO对象
     */
    public function getConn()
    {
        if(is_null(self::$conn)){
            $this->conn();
        }
        return self::$conn;
    }
}