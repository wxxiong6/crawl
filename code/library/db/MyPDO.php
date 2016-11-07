<?php
/**
 * PDO mysql类
 * @author xwx
 * @mtime 2016-11-5 修改记录日志，偶尔内存溢出问题
 * 
 */
class MyPDO
{

    /**
     * debug 模式
     * @var string
     */
    public $debug = false;

    /**
     * 是否记录构思日志
     * @var string
     */
    public $log = false;

    /**
     * 最大内存写入日志 单位（Mb）
     * @var string
     */
    public $maxMemort = 2;
    /**
     * 日志目录
     * @var string
     */
    public $logDestination = 'runsql.txt';

    /**
     * 数据表前缀
     */
    protected $tablePrefix = '';

    /**
     * 执行SQL语句
     */
    private static $_arrSql = [];

    /**
     * 数据库配置
     * @var $_config
     */
    private $_config;

    /**
     * 数据库link
     * @var $con
     */
    private static $conn = NULL;

    /**
     * 构造函数
     * @param array $config
     * @throws PDOException
     */
    public function __construct(array $config)
    {
        if (! class_exists("PDO"))
            throw new PDOException('PHP环境未安装PDO函数库！');
        $this->_config = $config;
        if (! is_array($config)) {
            throw new PDOException('Adapter parameters must be in an array !');
        }
        set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * 异常处理
     * @param unknown $exception
     */
    public function exceptionHandler($exception) {
        $trace = $exception->getTraceAsString();
        $log   = "Uncaught exception: " . $exception->getMessage() . PHP_EOL
        . "trace: " . $trace . PHP_EOL;

        if ($this->debug){
             echo $log,PHP_EOL;
        }
        if ($this->log){
            $this->_log($log, $this->logDestination);
        }
    }

    /**
     * 数据库连接
     * @throws PDOException
     */
    protected  function  _setConn()
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
     * etConn 获取link对象
     * @return NULL|PDO
     */
    public function getConn()
    {
        if(is_null(self::$conn)){
            $this->_setConn();
        }
        return self::$conn;
    }

    /**
     *  从数据表中查找一条记录
     * @param string $table
     * @param array|string  $conditions conditions 查找条件，数组array("字段名"=>"查找值")或字符串，
     *            请注意在使用字符串时将需要开发者自行使用escape来对输入值进行过滤
     * @param string $sort sort 排序，等同于“ORDER BY ”
     * @param string $fields 返回的字段范围，默认为返回全部字段的值
     * @return mixed|boolean
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
     * @param string $table
     * @param array|string   $conditions 查找条件，数组array("字段名"=>"查找值")或字符串，
     *            请注意在使用字符串时将需要开发者自行使用escape来对输入值进行过滤
     * @param string $sort sort 排序，等同于“ORDER BY ”
     * @param string $fields
     * @param string $limit 如果limit值只有一个数字，则是指代从0条记录开始。
     * @param string $offset offset
     * @throws PDOException
     */
    public function findAll($table, $conditions = null, $sort = null, $fields = null, $limit = null , $offset = null)
    {
        $where = "";
        $bindParams = [];
        $fields = empty($fields) ? "*" : $fields;
        if (is_array($conditions)) {
            $join = [];
            $conditionsFileArr = $this->_preperaFormat($table, $conditions);
            if(!$conditionsFileArr)
            {
                 throw new PDOException("Unknown column field in '{$table}'");
            }
            foreach ($conditions as $key => $condition) {
                $join[] = "`{$key}` = :{$key}";
                $bindParams[":{$key}"] = $condition;
            }
            $where = "WHERE " . join(" AND ", $join);
        } else {
            if (null != $conditions)
                $where = "WHERE " . $conditions;
        }
        if (null != $sort) {
            $sort = "ORDER BY {$sort}";
        }
        $table = $this->getTableNmae($table);
        $sql = "SELECT {$fields} FROM {$table} {$where} {$sort}";
        if (null != $limit){
            $sql = $this->setlimit($sql, $limit, $offset);
        }
        return $this->getArray($sql, $bindParams);
    }

    /**
     * 按字段值查找一条记录
     * @param string $table
     * @param string $field 对应数据表中的字段名
     * @param string $value 对应的值
     * @return boolean|mixed
     */
    public function findBy($table, $field, $value)
    {
        return $this->find($table, array(
            $field => $value
        ));
    }

    /**
     * 计算符合条件的记录数量
     * @param string $table
     * @param array|string   $conditions  查找条件，数组array("字段名"=>"查找值")或字符串，
     *               请注意在使用字符串时将需要开发者自行使用escape来对输入值进行过滤
     * @return mixed
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
     * @param string $table
     * @param array|string  $conditions 数组形式，查找条件，
     *                          此参数的格式用法与find/findAll的查找条件参数是相同的。
     * @param array $row 数组形式，修改的数据
     * @return boolean|number|boolean
     */
    public function update($table, $conditions, array $row)
    {
        $where = "";
        $row = $this->_preperaFormat($table, $row);
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
            $sql = "UPDATE  {$table} SET {$values} {$where}";
            return $this->exec($sql);
    }

    /**
     * 按字段值修改一条记录
     * @param string $table
     * @param array|string $conditions 数组形式，查找条件，
     *                        此参数的格式用法与find/findAll的查找条件参数是相同的。
     * @param string $field
     * @param string $value
     * @return boolean|number
     */
    public function updateField($table, $conditions, $field, $value)
    {
        return $this->update($table, $conditions, array(
            $field => $value
        ));
    }

    /**
     * 按给定的数据表的主键id删除记录
     * @param string $table
     * @param string $pk  pk 字符串或数字，数据表主键的值。
     * @return number|boolean
     */
    public function deleteById($table, $pk)
    {
        $table = $this->getTableNmae($table);
        return $this->delete($table, array(
            'id' => $pk
        ));
    }

    /**
     * 按条件删除记录
     * @param string $table
     * @param  array|string $conditions  数组形式，查找条件，此参数的格式用法与find/findAll的查找条件参数是相同的。
     * @return number|boolean
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
     * 在数据表中新增多条或多记录
     * @param string $table 表名
     * @param array $rows 数组形式，一条一维数组，多条是二维数组
     * @return boolean|string
     */
    public function insert($table, array $rows)
    {
        if (! is_array($rows))
            return FALSE;
            if(!empty($rows[1]) && is_array($rows[1])){
                $row =  $rows[0];
            } else
            {
                $row =  $rows;
            }
            $data = $this->_preperaFormat($table, $row);
            foreach ($rows as $key => $value) {
                if(is_array($value))
                {
                    foreach($value as $k => $v)
                    {
                        $bindParams[$key][':'.$k] = $v;
                    }
                } else
                {
                    $bindParams[':'.$key] = $value;
                }
            }
            $cols = array_keys($row);
            $col = '(`' . implode('`,`', $cols) . '`)';
            $val = ':'.implode(',:', $cols);
            $table = $this->getTableNmae($table);
            $sql = "INSERT INTO $table {$col} VALUES ({$val});";

            $sth = $this->execute($sql, $bindParams);
            if ($sth->rowCount()) {
                $newinserid = $this->lastInsertId();
                return $newinserid;
            }
            return false;
    }

    /**
     * 过滤转义字符
     * @param string $value 需要进行过滤的值
     * @return string|number
     */
    public function escape($value)
    {
        return $this->_valEscape($value);
    }

    /**
     * 创建批量插入sql语句
     * @param string $table
     * @param array $data
     * @return string
     */
    public function createInsert($table, array $data)
    {
        $table = $this->getTableNmae($table);
        $sql = 'INSERT INTO ' . $table;
        $flag = false; // 是否是二维数组
        foreach ($data as $key => $val) {
            if (is_array($val)) { // 二维数组
                $flag = true;
                if(empty($fields))
                {
                    $fields = array_keys(array_shift($data));
                }
                $values[] = "('" . implode("','", array_map('addslashes', $val)) . "') \n";
            } else { // 一维数组
                $values[] = $this->escape($val);
                $fields[] = $key;
            }
        }
        $sql .= ' (`' . implode('`,`', $fields) . '`) VALUES  '."\n";
        if ($flag) { // 二维数组
            $sql .= implode(',', $values) . ';';
        } else { // 一维数组
            $sql .= "(" . implode(",", $values) . ");";
        }

        return $sql;
    }


    /**
     * 返回最后执行的SQL语句供分析
     */
    public function getSqlList()
    {
        return self::$_arrSql;
    }

    /**
     * 返回最后执行的SQL语句供分析
     */
    public function getLastSql()
    {
        return array_pop(self::$_arrSql);
    }

    /**
     *  按SQL语句获取记录结果，返回数组
     * @param string $sql  需要执行的SQL语句
     * @param array $bindParams
     */
    public function getArray($sql, array $bindParams = [])
    {
        $sth = $this->execute($sql, $bindParams);
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 返回当前插入记录的主键ID
     */
    public function lastInsertId($name = '')
    {
        return $this->getConn()->lastInsertId($name = '');
    }

    /**
     * 格式化带limit的SQL语句
     * @param string $sql
     * @param int $limit
     * @param number $offset
     * @throws PDOException
     * @return string
     */
    public function setlimit($sql, $limit, $offset = 0)
    {
        $limit = intval($limit);
        if ($limit <= 0) {
            throw new PDOException("LIMIT argument limit=$limit is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new PDOException("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $limit";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }
        return $sql;
    }

    /**
     * 执行一个SQL语句
     * @param string $sql 需要执行的SQL语句
     * @throws PDOException
     * @return number|boolean
     */
    public function exec($sql)
    {
        self::$_arrSql[] = $sql.PHP_EOL;
        $result = $this->getConn()->exec($sql);
  
        if (FALSE !== $result) {
            $this->num_rows = $result;
            
            $memortMB = round(memory_get_usage()/1024/1024); 
            if($memortMB > $this->maxMemort && self::$_arrSql){
                //是否写入日志
                if ($this->log){ 
                    $log = join(self::$_arrSql);
                    $this->_log($log);
                    unset($log);
               }
               self::$_arrSql = [];
            }
                        
            return $result;
        } else {
            $poderror = $this->getConn()->errorInfo();
            if (!empty($poderror)){
                throw new PDOException("Execution error: " . $poderror[2]."{$sql}");
            }
        }
        return false;
    }

    /**
     * 执行一条预处理语句
     * @param string $sql 需要执行的SQL语句
     * @param array $bindParam
     * @throws PDOException
     * @return PDOStatement
     */
    public function execute($sql, array $bindParam = [])
    {
        self::$_arrSql[] = $sql.PHP_EOL.'bindParams'.var_export($bindParam, true).PHP_EOL;
        if (! $sth = $this->getConn()->prepare($sql)) {
            $poderror = $this->getConn()->errorInfo();
            throw new PDOException("[execution error]: " . $poderror[2] ."{$sql}");
        }
        if(!empty($bindParam[1]) && is_array($bindParam[1])){
            foreach($bindParam as $key => $value ){
                $sth->execute($value);
            }
        } else
        {
            $sth->execute($bindParam);
        }
        
       $memortMB = round(memory_get_usage()/1024/1024); 
       if($memortMB > $this->maxMemort && self::$_arrSql){
        //是否写入日志
        if ($this->log){ 
            $log = join(self::$_arrSql);
            $this->_log($log);
            unset($log);
         }
         self::$_arrSql = [];
        }    
            
        return $sth;
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
     * 获取数据表结构
     * @param unknown $table
     * @throws PDOException
     */
    private function _getTableInfo($table)
    {
        $table = $this->getTableNmae($table);
        $tableInfo = $this->getArray("DESCRIBE {$table}");
        if (empty($tableInfo))
            throw new PDOException('The' . $table . 'not exists');
        return $tableInfo;
    }

    /**
     * 对特殊字符进行过滤
     * @param string $value
     * @return string|number
     */
    private function _valEscape($value)
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
     * 按表字段调整适合的字段
     * @param string $table
     * @param string $rows 表字段
     */
    private function _preperaFormat($table, $rows)
    {
        $columns = $this->_getTableInfo($table);
        $newcol = array();
        foreach ($columns as $col) {
            $newcol[$col['Field']] = $col['Field'];
        }
        return array_intersect_key($rows, $newcol);
    }

    /**
     * 日志
     * @param string $message
     * @param string $logDestination 日志路径
     * @return boolean
     */
    private function _log($message, $logDestination = ''){
        if (empty($logDestination)) $logDestination = $this->logDestination;
        return error_log($message.PHP_EOL, 3 ,$logDestination);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->log && self::$_arrSql){
            $sql = join(self::$_arrSql);
            $this->_log($sql);
        }
        self::$_arrSql = [];
        self::$conn = null;
    }
}
