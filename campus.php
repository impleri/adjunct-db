<?php
/**
 * adjunct main class file
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

class adb_campus extends adb_parent {
	protected $_name = 'campus';

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

	static public function get ($id) {}

	static public function getBy ($field, $value) {}

	public function getParent() {}

	public function getContracts() {}

	public function getBenefits() {}

	public function insert() {}

	public function update() {}

	public function delete() {}
}
