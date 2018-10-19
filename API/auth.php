<?php

require 'database.php';

class auth extends database {
	public function __construct($url, $get, $post) {
		parent::__construct();
		
		if (count($post) > 0) {
			// POST
			$post['login'];
			$post['password'];
			
			// Authorization...
			
			$status_code = 200;
			$status_text = 'Successful authorization';
			$body = array(
				('status') => true,
				('token') => 'Something'
			);
			
			$this->result = json_encode(array(
				('status code') => $status_code,
				('status text') => $status_text,
				('body') => $body
			));
			
		} elseif (count($get) > count($url)) {
			// GET
		}
	}
	
	public $result;
}
