<?php
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/5/13
 * Time: 上午9:42
 */

namespace risen\base\dbi;
#trace
use risen\base\Trace;
#endtrace

abstract class AdapterBase
{
    function query($sql, array $params = array(), $indexKey = "")
    {
        $this->__connect();
        $result = array();
        $stmt = null;

#trace
        $sql_real = $sql;
        $sql = str_ireplace("SELECT", "SELECT SQL_NO_CACHE", $sql);
        $time_begin = microtime(true);
#endtrace
        if (empty($params)) {
            $stmt = $this->__pdo->query($sql);
        }
        else {
            $stmt = $this->__pdo->prepare($sql);
            tr($params);
            $stmt->execute($params);
        }
#trace
        $time = microtime(true) - $time_begin;
       // explain
        $explain_sql = "EXPLAIN $sql_real";
        $stmt_expl = null;
        $result_expl = array();
        if (empty($params)) {
            $stmt_expl = $this->__pdo->query($explain_sql);
        }
        else {
            $stmt_expl = $this->__pdo->prepare($explain_sql);
            $stmt_expl->execute($params);
        }
        if ($stmt_expl !== false) {
            while($row = $stmt_expl->fetchObject()) {
               $result_expl[] = $row;
            }
            Trace::appendSql($sql_real, $time, $result_expl);
        }
#endtrace
        if ($stmt === false) {
#trace
            $err = $this->__pdo->errorInfo();
            if (isset($err[2]))
                Trace::appendError(array(
                    'errstr' => "{$sql_real};\n{$err[2]}",
                    'backtrace' => debug_backtrace()
                ));
#endtrace
            return array();
        }
        if ($indexKey == "") {
            while($row = $stmt->fetchObject()) {
                $result[] = $row;
            }
        }
        else {
            while($row = $stmt->fetchObject()) {
                $result[$row->$indexKey] = $row;
            }
        }

        return $result;

    }


    function exec($sql, array $params = array())
    {
        $this->__connect();
#trace
        $beginTime = microtime(true);
#endtrace
        if (empty($params)) {
            $result = $this->__pdo->exec($sql);
#trace
            if ($result === false) {
                $err = $this->__pdo->errorInfo();
                Trace::appendError(array(
                    'errno' => $err[0],
                    'errstr' => "{$sql};\n{$err[2]}",
                    'errfile' => '',
                    'errline' => '',
                    'errcontext' => '',
                    'backtrace' => debug_backtrace()
                ));
            }
#endtrace
        }
        else {
            $stmt = $this->__pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->rowCount();
        }
#trace
        $time = microtime(true) - $beginTime;
        Trace::appendSql($sql, $time, null);
#endtrace

        return $result;
    }

    private function makeSqlValues(array $row)
    {
        $valuePart = "";
        foreach($row as $field => $value)
        {
            if ($value === null) {
                $valuePart .= ",\n`$field`=NULL";
                continue;
            }
            $valuePart .= (is_numeric($value) || $value[0] == ':') ?
                ",\n`$field`=$value":
                ",\n`$field`='$value'";
        }
        $valuePart[0] = " ";
        return "\nSET$valuePart";
    }

    function update($tableRef, array $values, $where, $params)
    {
        $sql = "UPDATE ". $tableRef . $this->makeSqlValues($values) . "\nWHERE\n$where";
        return $this->exec($sql, $params);
    }

    function delete($tableRef, $where, $tables="", $params = array())
    {
        $sql = "DELETE $tables FROM " . $tableRef . "\n WHERE $where";
        return $this->exec($sql, $params);
    }

    function select($table /*, $fields = '*', $where = "", $order="", $limit = "10", $params = array() */)
    {
        if ($table == '') {
            return array();
        }

        $keys = array('WHERE', 'ORDER BY', 'LIMIT');
        $args = func_get_args();

        $sql = 'SELECT ';
        $params = array();
        if (isset($args[1])) {
            $sql .= "$args[1]\nFROM " . $table;

            for($i=2, $cnt=count($args); $i<$cnt; $i++) {
                if (is_array($args[$i])) {
                    $params = $args[$i];
                    break;
                }
                if ($args[$i] == '') {
                    continue;
                }
                $sql .= "\n". $keys[$i-2] . " $args[$i]";
            }
        }
        else {
            $sql .= "* \nFROM \n" . $table;
        }

        return $this->query($sql, $params);
    }

    function insert($table, array $values)
    {
        $sql = "INSERT INTO " . $table . $this->makeSqlValues($values);
        $this->exec($sql);
        return $this->__pdo->lastInsertId();
    }

    private function __connect()
    {
        if ($this->__pdo === null) {
            $this->__pdo = new \PDO("mysql:host=$this->_host;dbname=$this->_dbName",
                $this->_user,
                $this->_passwd,
                array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_EMULATE_PREPARES => false,
//                    \PDO::ATTR_PERSISTENT => true
                ));
        }
    }

    /**
     * @var \PDO
     */
    private $__pdo = null;
    protected $_host = '127.0.0.1';
    protected $_user = 'root';
    protected $_passwd = '';
    protected $_dbName = '';
    protected $_charset = 'UTF-8';

}
