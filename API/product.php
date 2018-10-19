<?php
require_once 'database.php';

class product extends database {
	public function __construct($url, $get, $post) {
		parent::__construct();
		
		if (count($post) > 0) {
			// POST
			$title =          $post['title'];
			$manufacturer =   $post['manufacturer'];
			$text =           $post['text'];
			$tags =           $post['tags'];
			$image =          $post['image'];
			
			$title = 'Crap10';
			$manufacturer = 'Me';
			$text = 'its a good product';
			$tags = 'good';
			$image = 'API/product_images';
			
			$res = $this->db->query("SELECT * FROM `product` WHERE `title` = '$title'");
			
			if ($res->num_rows > 0) {
				$result['status code'] = 400;
				$result['status text'] = 'Creating error';
				$result['body'] = array(
					('status') => false,
					('message') => array(
						('title') => 'already exists'
					)
				);
			} else {
				$date = date('H:i d.m.Y');
				$res = $this->db->query("INSERT INTO `product` (`title`, `manufacturer`, `text`, `tags`, `image`, `date_create`) VALUES ('$title', '$manufacturer', '$text', '$tags', '$image', '$date')");
				
				$result['status code'] = 201;
				$result['status text'] = 'Successful creation';
				$result['body'] = array(
					('status') => true,
					('post_id') => 0
				);
			}
			
		} elseif (count($get) > count($url)) {
			// GET
			
		}
		
		$this->response = json_encode($result);
	}
	
	public $response;
}
