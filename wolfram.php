<?php
/**
 * wolfram|alpha binding wrapper
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */

class adb_wolfram {
	private $engine = null;
	private $response = null;
	public $active = false;

	public function __construct() {
		$appID = get_option('adb_wolfram_id');
		if ($appID) {
			require_once 'wa_wrapper/WolframAlphaEngine.php';
			$this->engine = new WolframAlphaEngine($appID);
			$this->active = true;
		}
	}

	public function push ($request) {
		$this->response = $this->engine->getResults($request);
		return !($this->response->isError);
	}

	public function getAssumptions() {
		$data = $this->response->getAssumptions();
		if (!empty($data)) {
			foreach ($data as $type => $assumptions) {
				echo $type;

				foreach ($assumptions as $assumption) {
					echo $assumption->name;
					echo $assumption->description;
					$assumption->input;
				}
			}
		}
	}

	public function getPods() {
		$data = $this->response->getPods();
		if (!empty($data)) {
			foreach ($data as $pod) {
				if (strtolower($pod->attributes['title']) != 'input') {
					foreach ($pod->getSubpods() as $subpod) {
						echo $subpod->image->attributes['src'];
					}
				}
			}
		}
	}

	public function getState ($name) {
		$query = $name . ' state';
	}

	public function getIndex ($name) {
		$query = $name . ' cost of living index';
	}

	public function getMedian ($name) {
		$query = $name . ' median household income';
	}

	public function getCities ($name) {
		$query = $name . ' metro cities';
	}

	public function getZips ($name) {
		$query = $name . ' metro ZIP codes';
	}

	public function metroCity ($name) {
		$query = $name . ' metro area';
	}

	public function metroZip ($name) {
		$query = $name . ' ZIP code city';
	}
}
