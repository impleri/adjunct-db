<?php
/**
 * adjunct main class file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * Metro Area Class
 *
 * Class to handle metro areas and the cities and ZIP codes within metro areas
 */
class adb_metro extends adb_parent {
	/**
	 * @var string Base table name
	 */
	protected $_name = 'metro';

	/**
	 * @var string Constructed ZIP Code table name
	 */
	private $_tbl_zip = '';

	/**
	 * @var string Constructed City table name
	 */
	private $_tbl_city = '';


	// Magic Methods


	/**
	 * Constructor
	 *
	 * Establish table names and load an object if an ID is given
	 * @param int $id Object ID/Key to load
	 */
	public function __construct ($id=null) {
		parent::__construct($id);
		$this->_tbl_zip = $this->_tbl_name . '_zip_map';
		$this->_tbl_city = $this->_tbl_name . '_city_map';
	}


	// Object Methods


	/**
	 * Get ID By Field
	 *
	 * Get table ID by a (unique) field
	 * @param string $field Field name
	 * @param mixed $value Field value
	 * @return int|bool ID if found, false otherwise
	 */
	public function getIdByField ($field, $value) {
		$query = '';
		switch ($field) {
			case 'zip':
				$query = sprintf('SELECT `metro` FROM `%s` WHERE `zip` = "%d"', $this->_tbl_zip, intval($value));
				break;

			case 'city':
				$query = sprintf('SELECT `metro` FROM `%s` WHERE `city` = "%s" AND `state` = "%s"', $this->_tbl_city, $this->_db->escape($value['city']), $this->_db->escape($value['state']));
				break;

			case 'wa_name_like':
				 $query = sprintf('SELECT `%s` FROM `%s` WHERE `%s` LIKE "%%%s%%"', $this->_key, $this->_tbl_name, $this->_db->escape('wa_name'), $this->_db->escape($value));
				 break;

			default:
				 $query = sprintf('SELECT `%s` FROM `%s` WHERE `%s` = "%s"', $this->_key, $this->_tbl_name, $this->_db->escape($field), $this->_db->escape($value));
				 break;
		}

		$query = $this->_db->prepare($query);

		if ($result = $this->_db->get_var($query)) {
			$result = (intval($result) > 0) ? $result : false;
		}

		if (!$result && in_array($field, array('zip', 'city'))) {
			return $this->guessBy($field, $value);
		}

		return $result;
	}

	/**
	 * Guess ID By Field
	 *
	 * Guess table ID by (ZIP or city name) through Wolfram
	 * Will create a new metro area if needed
	 * @param string $field Field name
	 * @param mixed $value Field value
	 * @return int|bool ID if found, false otherwise
	 */
	private function guessBy ($field, $value) {
		$wolfram = adb_system::getWolfram();
		$function = ($field == 'zip') ? 'metroZip' : 'metroCity';

		if ($name = $wolfram->$function($value)) {
			$metro = adb_metro::getBy('wa_name', $name);

			// add metro if it does not exists
			if (!$metro->_data['id']) {
				$metro->_data['wa_name'] = $name;
				$metro->build($name);
			}

			// map ZIP/city to metro
			if ($field == 'zip') {
				$data = array('metro' => $metro->_data['id'], 'zip' => $value);
				$table = '_tbl_zip';
			}
			elseif ($field == 'city') {
				$comma = strrpos($name, ',');
				if ($comma) {
					$city = trim(substr($name, 0, $comma));
					$state = trim(substr($name, $comma, strlen($name)-$comma));
				}
				else {
					$city = $value;
					$state = $wolfram->getState($value);
				}
				$data = array('metro' => $metro->_data['id'], 'city' => $city, 'state' => $state);
				$table = '_tbl_city';
			}
			$this->_db->insert($this->$table, $data);

			return $metro->_data['id'];
		}

		return false;
	}

	/**
	 * Build New Metro
	 *
	 * Build metro, querying Wolfram if COL index or MFI missing
	 * @param string $name Metro area name (will be trimmed to first comma/dash)
	 * @param int|bool $index COL index (false will try to find via Wolfram)
	 * @param int|bool $median Median Family Income (false will try to find via Wolfram)
	 */
	public function build ($name, $index=false, $median=false) {
		// strips area name to first (largest) city name
		$dash = strpos($name, '-');
		$comma = strpos($name, ',');
		if (!$dash && $comma) {
			$break = $comma-1;
		}
		elseif (!$comma && $dash) {
			$break = $dash-1;
		}
		elseif (!$comma && !$dash) {
			$break = strlen($row[0]);
		}
		else {
			$break = min(strpos($row[0], '-'), strpos($row[0], ','))-1;
		}

		$this->_data['name'] = substr($row[0], 0, $break);

		if (!$index) {
			$wolfram = adb_system::getWolfram();
			$index = $wolfram->getIndex($this->_data['name']);
		}
		$this->_data['index'] = $index;

		if (!$median) {
			$wolfram = adb_system::getWolfram();
			$median = $wolfram->getMedian($this->_data['name']);
		}
		$this->_data['median'] = $median;

		$this->save();
		$this->mapCity();
		$this->mapZip();
	}


	// Database Methods


	/**
	 * Database Schema
	 *
	 * Format database table to current schema
	 */
	public function schema() {
		$sql = sprintf('CREATE TABLE `%s` (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`name` text NOT NULL,
		`wa_name` text NOT NULL,
		`index` decimal(5,2) NOT NULL,
		`median` decimal(8,2) NOT NULL,
		UNIQUE KEY `name` (`name`)
		);

		CREATE TABLE `%s` (
		`city` tinytext NOT NULL,
		`state` varchar(2) NOT NULL,
		`metro` mediumint(9) NOT NULL,
		UNIQUE KEY `city_state` (`city`, `state`)
		);

		CREATE TABLE `%s` (
		`zip` mediumint(5) NOT NULL,
		`metro` mediumint(9) NOT NULL,
		UNIQUE KEY `zip` (`zip`)
		);', $this->_tbl_name, $this->_tbl_city, $this->_tbl_zip);

		dbDelta($sql);
	}

	/**
	 * Wolfram Connector
	 *
	 * Get cities or zip codes for metro area
	 * @param string $type Object to get (cities or ZIPs)
	 * @return array Array of object values (city-state names or ZIP codes)
	 */
	private function _map ($type='cities') {
		$wolfram = adb_system::getWolfram();
		$function = 'get' . ucfirst($type);
		$results = $wolfram->$function($this->name);

		if (empty($this->wa_name)) {
			$this->wa_name = $wolfram->getQueryName();
		}

		return $results;
	}

	/**
	 * Map Cities
	 *
	 * Map metro area to area cities
	 */
	public function mapCity() {
		$cities = $this->_map('cities');

		foreach ($cities as $name) {
			$comma = strrpos($name, ',');
			$city = trim(substr($name, 0, $comma));
			$state = trim(substr($name, $comma, strlen($name)-$comma));
			$data = array('metro' => $this->_data['id'], 'city' => $city, 'state' => $state);
			if (!($result = $this->_db->insert($this->_tbl_city, $data))) {
				// throw $this->_db->last_error;
			}
		}
	}

	/**
	 * Map ZIP
	 *
	 * Map metro area to area ZIP codes
	 */
	public function mapZip() {
		$zips = $this->_map('zips');

		foreach ($zips as $zip) {
			$data = array('metro' => $this->_data['id'], 'zip' => $zip);
			if (!($result = $this->_db->insert($this->_tbl_zip, $data))) {
				// throw $this->_db->last_error;
			}
		}
	}

}
