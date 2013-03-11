<?php

class Model_Response extends Init_Model{
	public $app = FALSE;
	private $response = array();

	public function __construct($data = array(), $error = 0) {
		parent::__construct();
		$this->setResponse($data, $error);
	}

	public function send($response_expire_seconds = FALSE) {
		if ($response_expire_seconds) {
			$seconds = intval($response_expire_seconds);
			$expires = date('D, d M Y H:i:s \G\M\T', (time()+$seconds));
			/*header('Expires: ' . $expires);
		 	header('Cache-Control: max-age='.$seconds);
		 	header("Pragma: public");*/
		 	header("Cache-Control: max-age={$seconds}, must-revalidate");
			header('Pragma: public');
			header('Expires: '.$expires);
			header('Last-Modified:Fri, 27 Jan 2012 16:04:49 GMT');
			
		}else {
		 	header("Cache-Control: no-cache, must-revalidate");
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header("Pragma: no-cache");
		}

		if(_gf('response', FALSE, 'json') == 'xml') {
			header('Content-type: text/xml; charset=utf-8');
			$response = $this->responseXML($this->response);
		} 
		else {
			header('Content-type: application/json; charset=utf-8');
			$response = $this->responseJSON($this->response);
		}

		//header("Content-length: ".strlen($response)); // tells file size
		//Facebook's xhprof (scripts debugger)
		if (DEBUG && extension_loaded('xhprof')) { // Mostrar debugger
			$xhprof_data = xhprof_disable();
			$xhprof_runs = new XHProfRuns_Default();
			$run_id = $xhprof_runs->save_run($xhprof_data, "api_seriesly");
			$xhprof_link = "http://xhprof.animet.org/index.php?run={$run_id}&source=api_seriesly";
			header("X-Xhprof-Debugger: {$xhprof_link}");
		}
		echo $response;
		die();
	}
	public function setResponse($data = array(), $error = 0) {
		if ($error !== 0) 
			$data = array('errorMessage' => $data);
		elseif (!is_array($data)) 
			$data = array('data' => $data);

		Init_Timer::logAllTimers();
		$this->response = array_merge($this->response, $data);

		// Variables de la peticion
		$this->response['error'] = $error;
	}

	protected function responseJSON($response) {
		return json_encode($response);
	}
	protected function responseXML($response) {
		return Init_ArrayToXML::toXml($response);	
	}
}