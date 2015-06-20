<?php #release

function td()
{
	$args=func_get_args();
	ob_start();
	foreach($args as $arg)
	{
		var_dump($arg);
	}
	risen\Trace::appendInfo(ob_get_clean());
}

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
