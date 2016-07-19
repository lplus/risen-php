<?php
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/5/24
 * Time: 下午6:44
 */

namespace risen\base\dbi;
#trace
use risen\Trace;
#endtrace

class DbTable
{
    static $name = '';
    static $pk = '';
    
	static function className()
    {
        return get_called_class();
    }

    /**
     * 
     * @param string $fields
     * @param string $where
     * @param string $order
     * @param string $limit
     * @param array $params
     * @return mixed
     */
    static function select($fields = '*'/* $where = "", $order="", $limit = "10", $params = array() */)
    {
        $args = func_get_args();
#trace
        if (empty(static::$name)) {
            Trace::appendError(array(
                'errstr' => get_called_class() . "::\$name for table name is empty",
                'backtrace' => debug_backtrace()
            ));
        }
#endtrace
        array_unshift($args, static::$name);
        return call_user_func_array(array(static::getAdapter(), 'select'), $args);
    }
    
    static function selectOne($fields = '*'/* $where = "", $order="", $limit = "10", $params = array() */)
    {
        $result = call_user_func_array(get_called_class() . "::select", func_get_args());
        if (empty($result)) {
            return null;
        }
        return $result[0];
    }

    static function selectByPk($pk)
    {
        $users = self::select('*', static::$pk . '=' . $pk);
        if (empty($users)) {
            return null;
        }
        return $users[0];
    }

    /**
     * 
     * @param unknown $where
     * @param string $tables
     * @param unknown $params
     */
    static function delete($where, $params = array())
    {
        return static::getAdapter()->delete(static::$name, $where, "", $params);
    }

    static function deleteByPk($pk)
    {
        return static::getAdapter()->delete(static::$name, static::$pk . '=' . $pk);
    }

    static function update(array $valueSet, $where, $params = array())
    {
        return static::getAdapter()->update(static::$name, $valueSet, $where, $params);
    }

    static function join($tableClass, $alias, $joinCondition, $type="LEFT")
    {
    	$joinTable = new JoinTable(static::getAdapter(), get_called_class());
    	return $joinTable->join($tableClass, $alias, $joinCondition, $type);
    }
    
    static function fkJoin($tableClass, $alias, $type="LEFT")
    {
    	$joinTable = new JoinTable(static::getAdapter(), get_called_class());
    	return $joinTable->fkJoin($tableClass, $alias, $type);
    }

    protected static function getAdapter()
    {
        static $adapter = null;
        if ($adapter == null) {
            $tableClass = get_called_class();
            $namespace = substr($tableClass, 0, strrpos($tableClass, '\\'));
            $adapterClass = $namespace . '\\Adapter';
            $adapter = new $adapterClass;
        }
        return $adapter;
    }

    static function insert(array $row)
    {
        return static::getAdapter()->insert(static::$name, $row);
    }
}
