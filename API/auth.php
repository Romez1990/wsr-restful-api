<?php
require_once 'base_class.php';

class auth extends base_class {
	public function __construct($url) {
		parent::__construct();
		
		$response = null;
		
		if ($this->method === 'POST') {
			$login = $_POST['login'];
			$password = $_POST['password'];
			
			$user = $this->db->query("SELECT `id` FROM `user` WHERE `login` = '$login' AND `password` = '$password'");
			
			if ($user->num_rows === 0) {
				$response['status code'] = 401;
				$response['status text'] = 'Invalid authorization data';
				$response['body']['status'] = false;
				$response['body']['message'] = 'Invalid authorization data';
			}
			
			// If there is no errors
			if (!isset($response['status code'])) {
				$token = sha1(time() . $login . $password);
				$user_id = $user->fetch_assoc()['id'];
				$this->db->query("INSERT INTO `session` (`token`, `user_id`) VALUES ('$token', '$user_id')");

				$response['status code'] = 200;
				$response['status text'] = 'Successful authorization';
				$response['body']['status'] = true;
				$response['body']['token'] = $token;
			}
		}
		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	}
}
