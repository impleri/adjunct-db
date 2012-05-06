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
			require_once 'wa_wrapper_0.1/WolframAlphaEngine.php';
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
}
