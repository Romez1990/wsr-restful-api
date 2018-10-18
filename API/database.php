<?php

class database
{
	public function __construct()
	{
		$this->db = new mysqli('127.0.0.1', 'mysql', 'mysql'. '1_module_restful_api');
		echo($this->db);
	}
	
	public $db;
	
}
