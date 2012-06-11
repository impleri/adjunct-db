<?php
/**
 * adjunct static workhorse file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * Installer Class
 *
 */
class adb_install {
	/**
	 * @var string URL to Kiplinger's Best Cities Data
	 *
	 * CSV File has the following columns:
	 * * Metro Area
	 * * Population
	 * * COL Index
	 * * % Workforce in creative labour
	 * * Median Household Income
	 * * % Income Growth
	 */
	private $cities_url = 'http://www.kiplinger.com/tools/bestcities_sort/best_cities.csv';

	/**
	 * Activation Handler
	 *
	 * Handles checking for installation and upgrade when plugin activated.
	 */
	public static function activate() {
		$version = get_option('adb_version');
		return (false === $version) ? self::install() : self::update();
	}

	/**
	 * Installer
	 *
	 * Installs basic data schema
	 */
	public static function install() {
		add_option('adb_version', ADB_VERSION);
		self::schema();
	}

	/**
	 * Updater
	 *
	 * updates data schema
	 */
	public static function update() {
		$version = get_option('adb_version');

		// already up to date
		if (version_compare($version, ADB_VERSION, 'eq')) {
			return;
		}
	}

	/**
	 * Add Data
	 *
	 * fills tables with metro areas, metro-zip map, and metro-city map
	 */
	public static function addData() {
		self::importCities();
	}

	/**
	 * Manage DB Schema
	 *
	 * install/update the database schema
	 */
	private static function schema() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$classes = array('metro', 'campus', 'contract', 'benefit');

		foreach ($classes as $class) {
			$class = 'adb_' . $class;
			$object = new $class();
			$object->schema();
		}
	}

	/**
	 * Read CSV File
	 *
	 * loads a csv file and parses it into an array
	 * @param string $file CSV file to import
	 * @param int $start Row to start reading
	 * @param int $end Row to end reading
	 * @return array Parsed CSV data
	 */
	private static function readCSV ($file, $start=0, $end=0) {
		$return = array();
		$row = 0;
		if (($handle = fopen($file, 'r')) !== false) {
			while (($data = fgetcsv($handle, 1000, ',')) !== false) {
				if ($row >= $start) {
					$return[$row] = $data;
				}

				$row++;
				if ($end != 0 && $row == $end) {
					break;
				}
			}
			fclose($handle);
		}

		return $return;
	}

	/**
	 * Import City Data
	 *
	 * fills tables with metro areas from Kiplinger's 400 best cities
	 */
	private static function importCities() {
		// save Kiplinger file to ./

		$data = self::readCSV();
		foreach ($data as $row) {
			$metro = new adb_metro();
			$metro->build($row[0], $row[2], $row[4]);
		}
	}

	/**
	 * Map City Data
	 *
	 * Maps metro areas to included cities/towns and ZIP codes
	 */
	private static function mapCities() {
		$metros = adb_system::getCities();

		foreach ($metros as $metro) {
			$metro->mapCity();
			$metro->mapZip();
		}
	}

	/**
	 * Import City Data
	 *
	 * fills tables with metro areas from Kiplinger's 400 best cities
	 */
	private static function importContracts() {

		$data = self::readCSV();
		foreach ($data as $row) {
			$metro = new adb_metro();
			$metro->name = $row[0];
			$metro->index = $row[2];
			$metro->median = $row[4];
			$metro->save();
		}
	}

}
