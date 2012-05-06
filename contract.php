<?php
/**
 * adjunct contract class file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
wp_ntt_contract_meta
	credit_rate	| Per credit-hour pay rate (calculated)
	adj_cred_rate| Per credit-hour pay rate adjusted for cost of living (calculated)
	cont_rate	| Per contact-hour pay rate (calculated)
					* 1 semester hour = 15 contact hours, 1 quarter hour = 10 contact hours
	adj_cont_rate| Per contact-hour pay rate adjusted for cost of living (calculated)
	service		| (array) dept-vote, faculty-senate, faculty-union, committee, dept-meetings
	union		| Associated union (list: AFT, united-academics, AAUP, adjunct-association)
	other		| Additional notes
*/

class adb_contract extends adb_parent {
	protected $_name = 'contract';

	public function schema() {
		$sql = sprintf('CREATE TABLE %s (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		campus mediumint(9) NOT NULL,
		dept tinytext NOT NULL,
		pay decimal(7,2) NOT NULL,
		grade tinytext NOT NULL,
		degree tinyint NOT NULL,
		online boolean NOT NULL,
		hours tinyint NOT NULL,
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

	public function insert() {}

	public function update() {}

	public function delete() {}

	// Calculator to determine term rate from hourly rate
	private function calculateRates() {}
}
