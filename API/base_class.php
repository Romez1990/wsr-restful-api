<?php

require_once 'config.php';

class base_class {
	protected $db;
	public $method;
	
	public function __construct() {
		$this->db = new mysqli(config::$db['host'], config::$db['username'], config::$db['password'], config::$db['dbname']);
		$this->method = $_SERVER['REQUEST_METHOD'];
	}
	
	protected function response($status_code, $status_text, $status, $body) {
		$response['status code'] = $status_code;
		$response['status text'] = $status_text;
		if ($body !== null) {
			$response['body'] = $body;
		}
		if ($status !== null) {
			$response['body']['status'] = $status;
		}
		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		$this->db->close();
	}
	
	protected function check_authorized($print_response = true) {
		$token = getallheaders()['Authorization'];
		if ($this->db->query("SELECT COUNT(*) AS `count` FROM `session` WHERE `token` = '$token'")->fetch_assoc()['count'] === '0') {
			if ($print_response)
				$this->response(401, 'Unauthorized', null, array('message' => 'Unauthorized'));
			return false;
		} else {
			return true;
		}
	}
	
	protected function get_datetime() {
		return date('Y-m-d H:i:s', time() + (config::$server_timezone - config::$timezone) * 60 * 60);
	}
}
