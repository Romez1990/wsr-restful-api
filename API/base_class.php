<?php

class base_class {
	public function __construct() {
		$this->db = new mysqli('127.0.0.1', 'mysql', 'mysql', '1_module_restful_api');
		
		// Check connection
		if ($this->db->connect_error) {
			die("Connection failed: " . $this->db->connect_error);
		}
		
		$this->headers = getallheaders();
		$this->method = $_SERVER['REQUEST_METHOD'];
	}
	
	public $db;
	public $headers;
	public $method;
	
}
