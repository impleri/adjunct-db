<?php
/**
 * adjunct main class file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * Parent Class
 *
 * Generic table object parent class
 */
abstract class adb_parent {
	/**
	 * @var string ADB table prefix
	 */
	private $_prefix = 'ntt_';

	/**
	 * @var string Base table name
	 */
	protected $_name = '';

	/**
	 * @var string Metadata type name
	 */
	protected $_meta_name = '';

	/**
	 * @var string Constructed table name
	 */
	protected $_tbl_name = '';

	/**
	 * @var string Constructed metadata table name
	 */
	protected $_tbl_meta = '';

	/**
	 * @var string Table key field
	 */
	protected $_key = 'id';

	/**
	 * @var array Table data
	 */
	protected $_data = null;

	/**
	 * @var array Associated metadata
	 */
	protected $_metadata = null;

	/**
	 * @var object WordPress DB object
	 */
	protected $_db = null;


	// Magic Methods


	/**
	 * Constructor
	 *
	 * Establish table names and load an object if an ID is given
	 * @param int $id Object ID/Key to load
	 * @param bool $meta Setup metadata (true = yes)
	 */
	protected function __construct ($id=null, $meta=false) {
		global $wpdb;

		// provide a shortcut to $wpdb
		$this->_db = $wpdb;

		// construct table name
		if (!empty($this->_name)) {
			$this->_tbl_name = $this->_db->prefix . $this->_prefix . $this->_name;
		}

		// construct meta table name
		if ($meta) {
			$this->_meta_name = $this->_prefix . $this->_name;
			$this->_tbl_meta = $this->_tbl_name . '_meta';
		}

		// load data if an ID is given
		if ($id) {
			$this->load($id);
		}
	}

	/**
	 * Magic Getter
	 *
	 * Get $field from data/metadata when possible
	 * @param string $field Field to get
	 * @return mixed Field value
	 */
	public final function __get ($field) {
		$return = null;

		if (array_key_exists($field, $this->_data)) {
			$return = $this->_data[$field];
		}
		elseif (array_key_exists($field, $this->_metadata)) {
			$return = $this->_metadata[$field];
		}

		return $return;
	}

	/**
	 * Magic Setter
	 *
	 * Set $field in data/metadata when possible
	 * @param string $field Field to set
	 * @param mixed $value Field value
	 */
	public final function __set ($field, $value) {
		if (array_key_exists($field, $this->_data)) {
			$this->_data[$field] = $value;
		}
		else {
			$this->_metadata[$field] = $value;
		}
	}


	// Static Methods


	/**
	 * Get Object
	 *
	 * Load (and cache) an object
	 * @param int $id Object ID/Key to load
	 * @return object|boolean Loaded object if successful, false if not
	 */
	public final static function &get ($id) {
		static $objects = array();

		if (empty($objects)) {
			$that = get_called_class();
			$objects[0] = new $that();
		}

		if (!$objects[$id]) {
			$that = get_called_class();
			$object = new $that($id);
			if ($object->id > 0) {
				$objects[$id] = $object;
			}
		}

		return ($objects[$id]) ? $objects[$id] : false;
	}

	/**
	 * Get Object By Field
	 *
	 * Load (and cache) an object
	 * @param string $field Field name
	 * @param mixed $value Field value
	 * @return object|boolean Loaded object if successful, false if not
	 */
	public final static function getBy ($field, $value) {
		$object = self::get(0);
		$id = $object->getIdByField($field, $value);
		if ($id) {
			return self::get($id);
		}

		return false;
	}


	// Object Methods


	/**
	 * Load Data
	 *
	 * Load object data
	 * @param int $id Object ID/Key to load
	 */
	public function load ($id) {
		$key = $this->_key;

		$query = $this->_db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = %d', $this->_tbl_name, $this->_key, intval($id)));
		if (($results = $this->db->get_row($query, ARRAY_A))) {
			$this->_data = $results;
		}

		if (!empty($this->$key) && !empty($this->_tbl_meta)) {
			$this->_metadata = get_metadata($this->_meta_name, $this->$key);
		}

		return;
	}

	/**
	 * Get ID By Field
	 *
	 * Get table ID by a (unique) field
	 * @param string $field Field name
	 * @param mixed $value Field value
	 * @return int|bool ID if found, false otherwise
	 */
	public function getIdByField ($field, $value) {
		$query = $this->_db->prepare(sprintf('SELECT `%s` FROM `%s` WHERE `%s` = "%s"', $this->_key, $this->_tbl_name, $this->_db->escape($field), $this->_db->escape($value)));
		if ($result = $this->_db->get_var($query)) {
			if (intval($result) > 0) {
				return $result;
			}
		}

		return false;
	}

	/**
	 * Save Object
	 *
	 * Save object (automatically deal with insert/update)
	 * @return bool True on success, false otherwise
	 */
	public function save() {
		$key = $this->_key;

		$action = ($this->$key > 0) ? 'insert' : 'replace';
		$result = $this->_db->$action($this->_tbl_name, $this->_data);

		foreach ($this->_metadata as $mKey => $mVal) {
			update_metadata($this->_meta_name, $this->$key, $mKey, $mVal);
		}

		return $result;
	}

	/**
	 * Delete Object
	 *
	 * Delete an object and all related metadata
	 * @return bool True on success, false otherwise
	 */
	public function delete() {
		$key = $this->_key;
		$query = $this->_db->prepare(sprintf('DELETE FROM `%s` WHERE `%s` = %d', $this->_tbl_name, $key, intval($this->$key)));
		$results = $this->_db->query($query);

		if ($this->_meta_name) {
			foreach ($this->_metadata as $mKey => $mVal) {
				delete_metadata($this->_meta_name, $this->$key, $mKey);
			}
		}

		return $results;
	}

}
