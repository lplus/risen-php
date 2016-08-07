<?php
namespace risen\base;


/**
 * Class Request
 * @package risen\base
 */
class Request
{
    static function getParam($key)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }

    static function getNumber($key)
    {
		if (isset($_REQUEST[$key])) {
			if (is_numeric($_REQUEST[$key])) {
				return $_REQUEST[$key];
			}
		}
        return null;
    }

    static function getArray($key)
    {
        if (isset($_REQUEST[$key]) && is_array($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
        return null;
    }

    static function getString($key)
    {
        if (is_string($_REQUEST[$key])) {
            return trim($_REQUEST[$key]);
        }
        return null;
    }
}
