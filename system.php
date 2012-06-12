<?php
/**
 * adjunct static workhorse file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

class adb_system {
	public static function isInstalled() {
		$version = get_option('adb_version');
		return version_compare($version, ADB_VERSION, 'eq');
	}

	public static function search() {}

	public static function &getMetro ($id=null) {
		if (!class_exists('adb_metro')) {
			include('./metro.php');
		}
		return adb_metro::get($id);

	}

	public static function &getCampus ($id=null) {
		if (!class_exists('adb_campus')) {
			include('./campus.php');
		}
		return adb_campus::get($id);

	}

	public static function &getContract ($id=null) {
		if (!class_exists('adb_contract')) {
			include('./contract.php');
		}
		return adb_contract::get($id);

	}

	public static function &getBenefit ($id=null) {
		if (!class_exists('adb_benefit')) {
			include('./benefit.php');
		}
		return adb_benefit::get($id);
	}

	public static function &getWolfram () {
		static $instance = null;

		if (!is_null($instance)) {
			include('./wolfram.php');
			$instance = new adb_wolfram();
		}

		return $instance;
	}
}
