<?php #release
namespace risen\base;

class Session
{
	private static $sessionStarted = false;

	private static function start() {
		if (self::$sessionStarted) {
			return false;
		}
		self::$sessionStarted = true;
		session_start();
	}

	static function get($key) {
		self::start();
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		return "";
	}

	static function set($key, $value) {
		self::start();
		$_SESSION[$key] = $value;
	}

	static function destroy() {
		self::start();
//		$_SESSION = [];
		session_destroy();
	}
}
