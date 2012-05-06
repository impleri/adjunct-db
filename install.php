<?php
/**
 * adjunct static workhorse file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

class adb_install {
	public static function process() {
		$version = get_option('adb_version');

		// Install
		if (false === $version) {
			self::install();
			return; // no need to check for upgrade
		}

		self::update();
	}

	public static function update() {
		$version = get_option('adb_version');

		// already up to date
		if (version_compare($version, ADB_VERSION, 'eq')) {
			return;
		}
	}

	public static function install() {
		add_option('adb_version', ADB_VERSION);
	}

	private static function schema() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$classes = array('metro', 'campus', 'contract', 'benefit');

		foreach ($classes as $class) {
			$class = 'adb_' . $class;
			$object = new $class();
			$object->schema();
		}
	}
}
