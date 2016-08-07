<?php #release
/**
 * 向trace 窗口中输出调试信息的函数
 */


/**
 * 使用 var_dump 输出所有的参数
 * @param $var... 可变参数
 */
function td($var)
{
    $args=func_get_args();
    ob_start();
    foreach($args as $arg)
    {
        var_dump($arg);
    }
    risen\Trace::appendInfo(ob_get_clean());
}

/**
 * 使用 print_r 输出所有参数
 * @param $arr ... 所有数组
 */
function tr($arr)
{
    $args=func_get_args();
    ob_start();
    foreach($args as $arg)
    {
        print_r($arg);
    }
    risen\Trace::appendInfo(ob_get_clean());
}

/**
 * 使用 echo 输出全部参数
 * @param $str ... 所有参数
 */
function ts($str)
{
    $args=func_get_args();
    ob_start();
    foreach($args as $arg)
    {
        echo $arg;
        echo "\n";
    }
    risen\Trace::appendInfo(ob_get_clean());
}
