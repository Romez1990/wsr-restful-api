<?php
require_once 'database.php';

class auth extends database {
	public function __construct($url, $get, $post) {
		parent::__construct();
		
		if (count($post) > 0) {
			// POST
			$login = $_POST['login'];
			$password = $_POST['password'];
			
			// Authorization...
			
			$token = sha1(time());
			
			
			
			$response['status_code'] = 200;
			$response['status_text'] = 'Successful authorization';
			$response['body']['status'] = true;
			$response['body']['token'] = $token;
			
			echo json_encode($response);
			
		} elseif (count($get) > count($url)) {
			// GET
			
		}
	}
	
	public $response;
}
