<?php

class base_class {
	protected $db;
	public $method;
	
	public function __construct() {
		$this->db = new mysqli('127.0.0.1', 'root', '', 'module1');
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
	
	protected function check_authorized() {
		$token = getallheaders()['Authorization'];
		if ($this->db->query("SELECT COUNT(*) AS `count` FROM `session` WHERE `token` = '$token'")->fetch_assoc()['count'] === '0') {
			$this->response(401, 'Unauthorized', null, array('message' => 'Unauthorized'));
			return false;
		} else {
			return true;
		}
	}
	
}
