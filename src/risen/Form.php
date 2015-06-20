<?php #release
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/6/3
 * Time: 下午5:03
 */

namespace risen;
#namespace risen\trace;

use risen\Request;

class Form implements \ArrayAccess
{
	function __construct() {
		if (empty($this->name)) {
			Trace::appendError(array(
				'errstr' => "in ". get_called_class() . " name is empty"
			));
			return;
		}
		$this->values = Request::getArray($this->name);
	}
	
	function __toString()
	{
		$template = get_called_class();
		$template = str_replace("\\", "/", $template);

		$template .= ".phtml";
		
		if (is_file($template)) {
			ob_start();
			include $template;
			return ob_get_clean();
		}
		else {
//			echo $template;
			return 'sdlfkj';
		}
	}
	
	protected $validation = array();
	
	private $validateClass = '';
	
	function validate()
	{
		$result = true;
		foreach ($this->validation as $field => $functions) {
			foreach ($functions as $function => $param) {
				$validate = '';
				if (method_exists('risen\Validate', $function)) {
					$validate = 'risen\Validate';
				}
				elseif($this->validateClass != '' && method_exists($this->validateClass, $function)) {
					$validate = $this->validateClass;
				}
				elseif (method_exists($this, $function)) {
					$validate = $this;
				}
#trace
				else {
					$searchClass = 'risen\Validate, ' . get_called_class();
					if ($this->validateClass != '') {
						$searchClass .= ",{$this->validateClass}";
					} 
					Trace::appendError(array(
						'errstr' => "field $field validate function: $function not exists; search class $searchClass"
					));
				}
#endtrace
				if ($validate != '') {
					if (is_string($param)) {
						if (!call_user_func(array($validate, $function), $this->values[$field])) {
							$this->errors[$field] = $this->validation[$field][$function];
							$result = false;
							break;
						}
					}
					elseif (is_array($param)) {
						$params = $this->validation[$field][$function];
						$msgPos = count($params) -1;
						$params[$msgPos] = $this->values[$field];
						if (!call_user_func_array(array($validate, $function), $params)) {
							$this->errors[$field] = $this->validation[$field][$function][$msgPos];
							$result = false;
							break;
						}
					}
				}
			}
		}
		return $result;
	}
	
	
	
	function getValues() {
		return $this->values;
	}
	
	protected $name = 'usf';
	
	protected $values = array();
	protected $errors = array();
	
    protected function htmlOptions($key)
    {
    	$option = "id=\"{$key}\" name=\"{$this->name}[$key]\"";
    	if (isset($this->values[$key])) {
    		$option .= " value=\"{$this->values[$key]}\"";
    	}
    	return $option;
    }
    
    protected function error($key)
    {
    	if (isset($this->errors[$key])) {
    		return $this->errors[$key];
    	}
    	return '';
    }
    
    function setValidateClass($validateClass)
    {
    	$this->validateClass = $validateClass;
    }
    
    function __get($var)
    {
        if (isset($this->values[$var])) {
        	return $this->values[$var];
        }
        return '';
    }
    
    function offsetGet($offset) {
    	if (isset($this->values[$offset]))
    		return $this->values[$offset];
    }
    
    function offsetSet($offset, $value)
    {
    	$this->values[$offset] = $value;
    }
    
    function offsetExists($offset)
    {
    	return array_key_exists($offset, $this->values);
    }
    
    function offsetUnset($offset)
    {
    	unset ($this->values[$offset]);
    }
}