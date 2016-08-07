<?php
namespace risen\base\dbi;

#trace
use risen\base\Trace;
#endtrace

class JoinTable
{
    private $tableRef = "";
    private $adapter = null;
    private $fks = array();
#trace
    private $tables = array();
#endtrace

    function __construct($adapter, $tableClass, $alias = '')
    {
        $this->adapter = $adapter;
//         $this->tables = $tableClass::$name;
        $this->tableRef = empty($alias)? $tableClass::$name : $tableClass::$name . " as $alias";
        $this->appendFks($tableClass, $alias);
    }

    private function appendFks($tableClass, $tableAlias = '')
    {
    	
   		$this->tables[] = empty($tableAlias)? $tableClass::$name: $tableAlias;
    	if (!isset($tableClass::$fks)) {
    		return;
    	}
    	if (!is_array($tableClass::$fks)) {
#trace
			Trace::appendError(array(
				'errstr' => "fks not an array in table class $tableClass",
				'backtrace' => debug_backtrace()
			));
#endtrace
    		return;
    	}
    	
    	$joinTable = $tableClass::$name;
    	$deleteTable = $tableAlias;
    	if ($tableAlias != $joinTable) {
    		$deleteTable = $joinTable;
    		foreach (array_keys($tableClass::$fks) as $table)
    		{
    			if (!is_array($tableClass::$fks[$table])) {
#trace
					Trace::appendError(array(
							'errstr' => "$tableClass::\$fks[$table] mast be an array with alias key",
							'backtrace' => debug_backtrace()
					));
#endtrace
    				continue;
    			}
    			foreach ($tableClass::$fks[$table] as $alias => &$condition)
    			{
#trace
    				if (isset($this->fks[$table]) && isset($this->fks[$table][$alias])) {
//     					echo $this->fks[$table][$alias];
    					Trace::appendError(array(
    							'errstr' => "conflict fks with $tableClass $table => $alias",
    							'backtrace' => debug_backtrace()
    					));
    					continue;
    				}
#endtrace
    				$condition = str_replace($joinTable, $tableAlias, $condition);
    			}
    		}
    	}
    	
    	$this->fks = array_merge_recursive($this->fks, $tableClass::$fks);

    }

    function join($tableClass, $alias, $joinCondition, $type="LEFT") {
        $table = $tableClass::$name;
        
        if (empty($alias)) {
        	if (!isset($this->fks[$table])) {
        		$this->fks[$table] = $joinCondition;
        		$this->tableRef .= "\n$type JOIN $table ON(\n$joinCondition\n)";
        	}
        	else {
        		// conflict
        	}
        }
        else {
        	if (isset($this->fks[$table])) {
        		if (!isset($this->fks[$table][$alias])) {
        			$this->fks[$table][$alias] = $joinCondition;
        		}
        		else {
        			// conflict
        		}
        	}
        	else {
        		$this->fks[$table] = array($alias => $joinCondition);
        	}
        	$this->tableRef .= "\n$type JOIN $table AS $alias ON(\n$joinCondition\n)";
        }
        
        $this->appendFks($tableClass, $alias);
        return $this;
    }


    function fkJoin($tableClass, $alias, $type="LEFT") {
        $table = $tableClass::$name;
        $this->tableRef .= "\n$type JOIN $table ";
        if (isset($this->fks[$table])) {
        	if (empty($alias)) {
        		$this->tableRef .= "ON(\n". $this->tableRef[$table] . "\n)";
        	}
        	else {
        		if (isset($this->fks[$table][$alias])) {
        			$this->tableRef .= "AS $alias ON(\n" . $this->fks[$table][$alias] . "\n)";
        		}
        		else {
        			// no fk
        		}
        	}
        	
            
            $this->appendFks($tableClass, $alias);
            return $this;
        }
#trace
		Trace::appendError(array(
			"errstr" => "fk $table => $alias not found",
			"backtrace" => debug_backtrace()
		));
#endtrace
        return null;
    }

    function select($fields = '*'/* $where = "", $order="", $limit = "10", $params = array() */)
    {
        $args = func_get_args();
        array_unshift($args, $this->tableRef);
        return call_user_func_array(array($this->adapter, 'select'), $args);
    }
	
	function selectx($where=""/*, $order="", $limit = "10", $params = array()*/)
	{
		$args = [];
		array_unshift($args, '*');
		array_unshift($args, $this->tableRef);
		return call_user_func_array([$this->adapter, 'select'], $args);
	}

    function delete($tables, $where, $params = array())
    {
        return $this->adapter->delete($this->tableRef, $where, $tables, $params);
    }

    function update(array $valueSet, $where, $params = array())
    {
        return $this->adapter->update($this->tableRef, $valueSet, $where, $params);
    }
}
