<?php

class base_class {
	protected $db;
	protected $headers;
	public $method;
	
	public function __construct() {
		$this->db = new mysqli('127.0.0.1', 'root', '', 'module1');
		$this->headers = getallheaders();
		$this->method = $_SERVER['REQUEST_METHOD'];
	}
	
	protected $response;
	
	protected function set_response($status_code, $status_text, $status, $body) {
		$this->response['status code'] = $status_code;
		$this->response['status text'] = $status_text;
		if ($body !== null) {
			$this->response['body'] = $body;
		}
		if ($status !== null) {
			$this->response['body']['status'] = $status;
		}
	}
	
	protected function print_response() {
		echo json_encode($this->response, JSON_UNESCAPED_UNICODE);
		$this->db->close();
	}
	
	protected function check_authorized() {
		$token = $this->headers['Authorization'];
		if ($this->db->query("SELECT COUNT(*) AS `count` FROM `session` WHERE `token` = '$token'")->fetch_assoc()['count'] === '0') {
			$this->set_response(401, 'Unauthorized', null, array('message' => 'Unauthorized'));
			return false;
		} else {
			return true;
		}
	}
	
}
