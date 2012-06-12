<?php
/**
 * adjunct benefit class file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
wp_ntt_benefits
	status		| Is benefit restricted? (list: no, after X courses, after X terms, after X years, 50% P/T, 75% P/T, FT, other)
	type		| health, dental, vision, retirement, other
	value		| discount, minimal, basic, full (stock-options, state-pension, private-pension, 401k, 403b, IRA for retirement)
*/

class adb_benefit extends adb_parent {
	protected $_name = 'benefit';
	protected $key = 'campus';

	// Magic Methods


	/**
	 * Constructor
	 *
	 * Establish table names and load an object if an ID is given
	 * @param int $id Object ID/Key to load
	 */
	public function __construct ($id=null) {
		parent::__construct($id, false);
	}


	public function schema() {
		$sql = sprintf('CREATE TABLE %s (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		campus mediumint(9) NOT NULL,
		status tinytext NOT NULL,
		type tinytext NOT NULL,
		value text NOT NULL,
		);', $this->_tbl_name);

		dbDelta($sql);
	}
}
