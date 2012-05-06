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

	public static function getMetro() {}

	public static function getCampus() {}

	public static function getContract() {}

	public static function getBenefit() {}
}
