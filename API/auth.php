<?php
require_once 'base_class.php';

class auth extends base_class {
	public function __construct() {
		parent::__construct();
	}
	
	public function auth() {
		$login = $_POST['login'];
		$password = $_POST['password'];
		
		$user = $this->db->query("SELECT `id` FROM `user` WHERE `login` = '$login' AND `password` = '$password'");
		
		// Existing check
		if ($user->num_rows === 0) {
			$this->response('401', 'Invalid authorization data', false, array('message' => 'Invalid authorization data'));
			return;
		}
		
		// If there is no errors do that needs
		$token = sha1(time() . $login . $password);
		$user = $user->fetch_assoc();
		$user_id = $user['id'];
		$this->db->query("INSERT INTO `session` (`token`, `user_id`) VALUES ('$token', '$user_id')");

		$this->response('200', 'Successful authorization', true, array('token' => $token ));
	}
}
