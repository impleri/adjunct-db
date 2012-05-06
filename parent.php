<?php
/**
 * adjunct main class file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

class adb_parent {
	private $_prefix = 'ntt_';
	protected $_name = '';
	protected $_tbl_name = '';
	protected $_tbl_meta = '';

	protected $key = 'id';

	protected $db = null;

	public function __construct ($id=null, $meta=false) {
		global $wpdb;

		$this->db = $wpdb;

		if (!empty($this->table)) {
			$this->_tbl_name = $this->db->prefix . $this->_prefix . $this->_name;
		}

		if ($meta) {
			$this->_tbl_meta = $this->_tbl_name . '_meta';
		}

		if ($id) {
			$this->load($id);
		}
	}

	static public function get ($id) {}

	static public function getBy ($field, $value) {
		$query = $this->db->prepare(sprintf('SELECT `%s` FROM `%s` WHERE `zip` = %d', $this->key, $this->_tbl_name, $field, $value));
		if ($result = $this->db->get_var($query)) {
			return $this->get($result);
		}

		return false;
	}

	public function load ($id) {
		$query = $this->db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = %d', $this->_table, $this->key, $id));
		if (($results = $this->db->get_row($query, ARRAY_A))) {
			foreach ($results as $key => $val) {
				$this->$key = $val;
			}
		}

		$this->fillData();
		return;
	}

	public function fillData() {
		if (!empty($this->$key) && !empty($this->_table_meta)) {
			$meta = adb_get_meta_by_key();
		}
	}

	public function insert() {}

	public function update() {}

	public function delete() {}
}
