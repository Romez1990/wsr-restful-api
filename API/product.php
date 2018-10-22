<?php
require_once 'database.php';

class product extends database {
	public function __construct($url) {
		parent::__construct();
		
		if (count($_POST) > 0) {
			// POST
			$title = $_POST['title'];
			$manufacturer = $_POST['manufacturer'];
			$text = $_POST['text'];
			$tags = $_POST['tags'];
			$image = $_FILES['image'];
			
			// Already exists check
			$res = $this->db->query("SELECT COUNT(`title`) AS `count` FROM `product` WHERE `title` = '$title'");
			if ($res->fetch_assoc()['count'] > 0) {
				// Already exists
				$response['status code'] = 400;
				$response['status text'] = 'Creating error';
				$response['body']['status'] = false;
				$response['body']['message']['title'] = 'already exists';
			}
			
			// Invalid file format check
			if ($image['size'] > pow(2, 20) || $image['type'] !== 'image/png' && $image['type'] !== 'image/jpeg') {
				if ($response['status code'] !== 400) {
					$response['status code'] = 400;
					$response['status text'] = 'Creating error';
					$response['body']['status'] = false;
				}
				$response['body']['message']['image'] = 'invalid file format';
			}
			
			// If there is no errors
			if (!$response['status code']) {
				// Upload image
				$upload_dir = 'W:\domains\1_Module_RESTful_API\API\product_images';
				$upload_path = $upload_dir . '\\' . $image['name'];
				$result_file = move_uploaded_file($image['tmp_name'], $upload_path);
				
				$now = date('H:i d.m.Y');
				$result_db = $this->db->query("INSERT INTO `product` (`title`, `manufacturer`, `text`, `tags`, `image`, `date_of_creation`) VALUES ('$title', '$manufacturer', '$text', '$tags', '$upload_path', '$now')");
				
				if ($result_file && $result_db) {
					// Successful
					$response['status code'] = 201;
					$response['status text'] = 'Successful creation';
					$response['body']['status'] = true;
					$response['body']['post_id'] = $this->db->query("SELECT `id` FROM `product` WHERE `title` = '$title'")->fetch_assoc()['id'];
				}
			}
			
		} elseif (count($_GET) > count($url)) {
			// GET
			
		}
		
		echo json_encode($response);
	}
}
