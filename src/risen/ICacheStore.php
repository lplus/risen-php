<?php #release
namespace risen;

interface ICacheStore
{
	function get($key);
	function set($key, $value);
	// todo: sdf
}
