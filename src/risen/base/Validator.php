<?php
namespace risen\base;

class Validator
{
    static function email($value)
    {
        
    }
    
    static function required($value) 
    {
        return !empty($value);
    }
    
    static function match($pattern, $value)
    {
#trace
        if ($r = preg_match($pattern, $value)) {
            return true;
        }
        else {
            if ($r === false) {
                $errCode = preg_last_error();
                $errstr = '';
                if ($errCode == PREG_NO_ERROR) {
                    $errstr =  'There is no error.';
                }
                else if ($errCode == PREG_INTERNAL_ERROR) {
                    $errstr =  'There is an internal error!';
                }
                else if ($errCode == PREG_BACKTRACK_LIMIT_ERROR) {
                    $errstr =  'Backtrack limit was exhausted!';
                }
                else if ($errCode == PREG_RECURSION_LIMIT_ERROR) {
                    $errstr =  'Recursion limit was exhausted!';
                }
                else if ($errCode == PREG_BAD_UTF8_ERROR) {
                    $errstr =  'Bad UTF8 error!';
                }
                else if ($errCode == PREG_BAD_UTF8_ERROR) {
                    $errstr =  'Bad UTF8 offset error!';
                }
                Trace::appendError(array(
                    'errstr' => $errstr
                ));
            }
            return false;
        }
#endtrace
        return (bool)preg_match($pattern, $value);
    }
    
    static function integer($value)
    {
        return ctype_digit($value);
    }
    
    static function min($minValue, $value)
    {
        tr($minValue, $value);
        if (is_numeric($value) && $value > $minValue) {
            return true;
        }
        return false;
    }
    
    static function max($maxValue, $value)
    {
        tr($maxValue, $value);
        if (is_numeric($value) && $value < $maxValue) {
            return true;
        }
        return false;
    }
    
    static function range($minValue, $maxValue, $value)
    {
        if (is_numeric($value) && is_numeric($minValue) && is_numeric($maxValue)) {
            if ($value > $minValue && $value < $maxValue) {
                return true;
            }
        }

        return false;
    }
    
    static function lenMin($minValue, $value)
    {
        tr($value);
        if (is_string($value) && strlen($value) > $minValue) {
            return true;
        }
        return false;
    }
    
    static function lenMax($maxValue, $value)
    {
        if (is_string($value) && strlen($value) < $maxValue) {
            return true;
        }
        return false;
    }

    static function lenRange($minValue, $maxValue, $value)
    {
        if (is_string($value)) {
            $len = strlen($value);
            if ($len > $minValue && $len < $maxValue) {
                return true;
            }
        }
        return false;
    }
    
    static function mblenMin($minValue, $value)
    {
        
    }
    
    static function mblenMax($maxValue, $value)
    {
        
    }
    
    static function mblenRange($minValue, $maxValue, $value)
    {
        
    }
    
    static function url($value) {
        
    }
    
    static function unicode($value) {
        
    }
    
    static function ipv4($value) {
        
    }
    
    static function ipv6($value) {
        
    }
    
    static function date($value) {
        
    }
    
    static function datetime($value) {
        
    }
}
