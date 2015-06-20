<?php
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/5/9
 * Time: 下午7:32
 */

namespace risen;

class Request
{
    static function getParam($key)
    {
        return $_REQUEST[$key];
    }

    static function getNumber($key)
    {
        if (is_numeric($_REQUEST[$key])) {
            return $_REQUEST[$key];
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
            return $_REQUEST[$key];
        }
        return null;
    }
}
