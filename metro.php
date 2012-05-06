<?php
/**
 * adjunct main class file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
CSV file
	Metro Area
	Population
	COL Index
	% Workforce in creative labour
	Median Household Income
	% Income Growth

Wolfram Alpha (to populate zip_metro_map)
	[name] metro zip codes

Wolfram Alpha (to guess metro area from city, state)
	[city], [state] metro area
*/

class adb_metro extends adb_parent {
	protected $_name = 'metro';
	private $_tbl_zip = '';
	private $_tbl_city = '';

	public function __construct ($id=null) {
		parent::__construct($id);
		$this->_tbl_zip = $this->_tbl_name . '_zip_map';
		$this->_tbl_city = $this->_tbl_name . '_city_map';
	}

	public function schema() {
		$sql = sprintf('CREATE TABLE %s (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name text NOT NULL,
		wa_name text NOT NULL,
		index decimal(5,2) NOT NULL,
		median decimal(8,2) NOT NULL,
		UNIQUE KEY name (name)
		);

		CREATE TABLE %s (
		city tinytext NOT NULL,
		state varchar(2) NOT NULL,
		metro mediumint(9) NOT NULL,
		UNIQUE KEY city_state (city, state)
		);

		CREATE TABLE %s (
		zip mediumint(5) NOT NULL,
		metro mediumint(9) NOT NULL,
		UNIQUE KEY zip (zip)
		);', $this->_tbl_name, $this->_tbl_city, $this->_tbl_zip);

		dbDelta($sql);
	}

	static public function get ($id) {}

	static public function getBy ($field, $value) {
		$query = '';
		switch ($field) {
			case 'zip':
				$query = sprintf('SELECT `metro` FROM `%s` WHERE `zip` = %d', $this->_tbl_zip, intval($value));
				break;

			case 'city':
				$query = sprintf('SELECT `metro` FROM `%s` WHERE `city` = "%s" AND `state` = "%s"', $this->_tbl_city, $this->db->escape($value['city']), $this->db->escape($value['state']));
				break;
		}

		if (empty($query)) {
			return false;
		}

		$result = $this->db->get_var($query);

		if ($result) {
			return adb_system::getMetro($result);
		}

		return false;
	}

	public function insert() {}

	public function update() {}

	public function delete() {}
}
