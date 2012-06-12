<?php
/**
 * campus class file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
Wolfram Alpha (to complete campus information: term type, profit, public)
	[university name]

wp_ntt_campus_meta
	term	| Term Type: semester, quarter 					(auto if empty)
	public	| Public or Private Institution (true = public) (auto if empty)
	profit	| (Non-)Profit status (true = for-profit)		(auto if empty)
*/

/**
 * Campus Class
 *
 * Class to handle campuses and their metadata
 */
class adb_campus extends adb_parent {
	/**
	 * @var string Base table name
	 */
	protected $_name = 'campus';


	// Object Methods


	/**
	 * Get Parent
	 *
	 * Load and return parent campus
	 * @param int|null $id ID of campus to get parent
	 * @return adb_campus Campus object
	 */
	public function &getParent ($id=null) {
		if ($id) {
			$class = adb_campus::get($id);
			$parent = $class->_data['parent'];
		}
		else {
			$parent = $this->_data['parent'];
		}

		return ($parent) ? adb_campus::get($parent) : false;
	}

	public function getContracts() {}

	public function getBenefits() {}

	/**
	 * Build New Campus
	 *
	 * Build a campus (main data only), querying Wolfram if metro missing
	 * @param string $name Campus name
	 * @param int|bool $metro Foreign key to metro area (false will try to find via Wolfram)
	 * @param int $parent Main campus (0 means this is the main campus)
	 */
	public function build ($name, $metro=false, $parent=0, $metadata=array()) {
		$wolfram = adb_system::getWolfram();

		if (!$metro) {
			$city = $wolfram->campusLocation($name);
			$metro = adb_metro::getBy('city', $city);
			$metro = $metro->id;
		}

		$this->_data['name'] = $name;
		$this->_data['metro'] = $metro;
		$this->_data['parent'] = $parent;

		// attempt to populate metadata
		$this->_metadata = $metadata;
		$this->_metadata['term'] = (isset($this->_metadata['term'])) ? $this->_metadata['term'] : $wolfram->campusTerm($name);
		$this->_metadata['public'] = (isset($this->_metadata['public'])) ? $this->_metadata['public'] : $wolfram->campusPublic($name);
		$this->_metadata['profit'] = (isset($this->_metadata['profit'])) ? $this->_metadata['profit'] : $wolfram->campusProfit($name);

		$this->save();
	}


	// Database Methods


	/**
	 * Database Schema
	 *
	 * Format database table to current schema
	 */
	public function schema() {
		$sql = sprintf('CREATE TABLE %s (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		parent mediumint(9) NOT NULL,
		metro mediumint(9) NOT NULL,
		UNIQUE KEY name (name)
		);

		CREATE TABLE %s (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		campus_id mediumint(9) NOT NULL,
		name tinytext NOT NULL,
		value text NOT NULL
		);', $this->_tbl_name, $this->_tbl_meta);

		dbDelta($sql);
	}
}
