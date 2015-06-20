<?php
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/5/13
 * Time: 上午10:37
 */

namespace risen\dbi;

class DbView
{
    /**
     * before php5.5
     * @return string
     */
    static function getClass()
    {
        return get_called_class();
    }

    static $name = '';
    protected $query = null;

    static function select($fields = '*'/* $where = "", $order="", $limit = "10", $params = array() */)
    {
        $args = func_get_args();
        array_unshift($args, static::$name);
        return call_user_func_array(array(static::getAdapter(), 'select'), $args);
    }

    static function selectByPk($pk)
    {
        $users = self::select('*', static::$_pk . '=' . $pk);
        if (empty($users)) {
            return null;
        }
        return $users[0];
    }

    static function delete($where, $tables="", $params = array())
    {
        return static::getAdapter()->delete(static::$name, $where, $tables, $params);
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
}
