<?php
namespace Chama\DB;
use MysqliDb;

/**
 * DbManager
 * Manages DB connection. Only.
 *
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class DbManager {
	/**
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	private static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton The *Singleton* instance.
	 */
	public static function connect() {
		if (null === static::$instance) {
			//Open a new connection to the MySQL server
			//			static::$instance = new MysqliDb('localhost', 'makaoyan_chama', '_XLCZ%eUhEg(', 'makaoyan_chama');
			static::$instance = new MysqliDb('localhost', 'root', 'jim', 'chamaapp');
			static::$instance->pageLimit = 60;
		}

		return static::$instance;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}

}
