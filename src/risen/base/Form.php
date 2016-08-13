<?php
namespace risen\base;

use risen\base\Request;

#trace
use risen\Trace;
#endtrace


class Form implements \ArrayAccess
{
    protected $name = '';
    private $_values = array();
    private $_errors = array();

    /**
     * 指定验证器类名
     * @var string
     */
    protected $validatorClass = 'risen\base\Validator';

    /**
     * 提供验证数据,格式为:
     * array('email' => array('requred' => 'email 不能空')
     * array(
     *  'email' => array
     *      'requred' => '邮件地址不能空',
     *      'email' => 'email格式不正确',
     *      'minLen' => array(10, '邮件地址长度不能小于10'),
     *      'mblenRange' => array(10, 30, '长度需要在10 - 30 之间')
     * )
     * @var array
     */
    protected $validation = array();


    function __construct() {
        if (empty($this->name)) {
            Trace::appendError(array(
                'errstr' => "in ". get_called_class() . " name is empty"
            ));
            return;
        }
        $this->_values = Request::getArray($this->name);
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
//            echo $template;
            return 'sdlfkj';
        }
    }
    
    function validate()
    {
        $result = true;
        if (!class_exists($this->validatorClass)) {
#trace
            Trace::appendError(array(
                'errstr' => "validator class {$this->validatorClass} not exists;"
            ));
#endtrace
            return false;
        }

        foreach ($this->validation as $field => $validators)
        {
            if (!isset($this->_values[$field])) {
                continue;
            }
            foreach ($validators as $functionName => $params)
            {
#trace
                if (!method_exists($this->validatorClass, $functionName)) {
                    Trace::appendError(array(
                        'errstr' => "validator: {$this->validatorClass}::{$functionName} not exists in form:" . get_called_class()
                    ));
                    continue;
                }
#endtrace
                $value = $this->_values[$field];
                if (is_array($params)) {
                    $lastParam = sizeof($params) - 1;
                    $errorMessage = $params[$lastParam];
                    $params[$lastParam] = $value;
                    if (!call_user_func_array("{$this->validatorClass}::{$functionName}", $params)) {
                        $this->_errors[$field] = $errorMessage;
#trace
                        Trace::appendError(array(
                            'errstr' => "{$field} error: {$errorMessage}"
                        ));
#endtrace
                        $result = false;
                        break;
                    }
                }
                else {
                    if (!call_user_func("{$this->validatorClass}::{$functionName}", $value)) {
                        $this->_errors[$field] = $params;
#trace
                        Trace::appendError(array(
                            'errstr' => "{$field} error: {$params}"
                        ));
#endtrace
                        $result = false;
                        break;
                    }
                }
                
            }
        }

        return $result;
    }
    
    
    function getValues() {
        return $this->_values;
    }

    function getErrors()
    {
        return $this->_errors;
    }

	private function getAttrStr($key, array $attrs)
	{
        $attrsStr = "id=\"{$this->name}_{$key}\" name=\"{$this->name}[$key]\"";
		if (!empty($attrs)) {
			foreach($attrs as $attr => $value) {
				$attrStr .= " $attr=\"$value\"";
			}
		}
		return attrsStr;
	}

	protected function textEl($key, array $attrs=[])
	{
		$attrStr = $this->getAttrStr($key, $attrs);
		return "<input type=\"text\" {$attrStr}/>";
	}

	protected function passwordEl($key, array $attrs=[])
	{
		$attrStr = $this->getAttrStr($key, $attrs);
		return "<input type=\"text\" {$attrStr}/>";
	}

	protected function hiddenEl($key)
	{
        return "<input type=\"hidden\" id=\"{$this->name}_{$key}\" name=\"{$this->name}[$key]\"/>";
	}

	protected function selectEl($key, $data, $attrs=[])
	{
		$attrStr = $this->getAttrStr($key, $attrs);

		$options = '';
		if (!empty($data)) {
			foreach($data as $value=>$text) {
				$options .= "<option value=\"{$value}\">{$text}</option>";
			}
		}
		$html = "<select {$attrStr}>{$options}</select>";
	}

    
    protected function attrs($key)
    {
        $attrs = "id=\"{$this->name}_{$key}\" name=\"{$this->name}[$key]\"";
        if (isset($this->_values[$key])) {
            $attrs .= " value=\"{$this->_values[$key]}\"";
        }
        return $attrs;
    }
    
    protected function error($key)
    {
        if (isset($this->_errors[$key])) {
            return $this->_errors[$key];
        }
        return '';
    }

    protected function hasError($key)
    {
        return isset($this->_errors[$key]);
    }

    function offsetGet($offset) {
        if (isset($this->_values[$offset]))
            return $this->_values[$offset];
    }
    
    function offsetSet($offset, $value)
    {
        $this->_values[$offset] = $value;
    }
    
    function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_values);
    }
    
    function offsetUnset($offset)
    {
        unset ($this->_values[$offset]);
    }
}
