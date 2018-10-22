<?php
require_once 'database.php';

class product extends database {
	public function __construct($url) {
		parent::__construct();
		
		switch (count($url)) {
			case 1:
				if (count($_POST) > 0) {
					// POST
					// Creating
					$title = $_POST['title'];
					$manufacturer = $_POST['manufacturer'];
					$text = $_POST['text'];
					$tags = $_POST['tags'];
					$image = $_FILES['image'];
					
					// Already exists check
					if ($this->db->query("SELECT COUNT(`title`) AS `count` FROM `product` WHERE `title` = '$title'")->fetch_assoc()['count'] > 0) {
						// Already exists
						$response['status code'] = 400;
						$response['status text'] = 'Creating error';
						$response['body']['status'] = false;
						$response['body']['message']['title'] = 'already exists';
					}
					
					// Invalid file format check
					$valid_size = 2 * pow(2, 20); // 2 MB
					if ($image['size'] > $valid_size || $image['type'] !== 'image/png' && $image['type'] !== 'image/jpeg') {
						$response['status code'] = 400;
						$response['status text'] = 'Creating error';
						$response['body']['status'] = false;
						$response['body']['message']['image'] = 'invalid file format';
					}
					
					// If there is no errors
					if (!$response['status code']) {
						// Upload image
						$upload_dir = 'W:\domains\1_Module_RESTful_API\API\product_images';
						$upload_path = $upload_dir . '\\' . $image['name'];
						$result_file = move_uploaded_file($image['tmp_name'], $upload_path);
						
						// Upload data
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
					// View
					
				}
				break;
			
			case 2:
				if (is_numeric($url[1])) {
					if (count($_POST) > 0) {
						// POST
						// Editing
						$title = $_POST['title'];
						$manufacturer = $_POST['manufacturer'];
						$text = $_POST['text'];
						$tags = $_POST['tags'];
						$image = $_FILES['image'];
						
						if ($this->db->query("SELECT COUNT(`id`) AS `count` FROM `product` WHERE `id` = '$url[1]'")->fetch_assoc()['count'] === 0) {
							// Product not found
							$response['status code'] = 404;
							$response['status text'] = 'Product not found';
							$response['body']['message'] = 'Product not found';
						} else {
							// Already exists check
							if ($this->db->query("SELECT COUNT(`title`) AS `count` FROM `product` WHERE `title` = '$title'")->fetch_assoc()['count'] > 0) {
								$response['status code'] = 400;
								$response['status text'] = 'Creating error';
								$response['body']['status'] = false;
								$response['body']['message']['title'] = 'This title already exists';
							}
							
							// Invalid file format check
							$valid_size = 2 * pow(2, 20); // 2 MB
							if ($image['size'] > $valid_size || $image['type'] !== 'image/png' && $image['type'] !== 'image/jpeg') {
								$response['status code'] = 400;
								$response['status text'] = 'Creating error';
								$response['body']['status'] = false;
								$response['body']['message']['image'] = 'invalid file format';
							}
							
							// Updating
							// Upload image
							$r = $this->db->query("SELECT `image` FROM `product` WHERE `id` = '$url[1]'");
							$d = $r->fetch_assoc()['image'];
							var_dump($d);
							unlink($d);
							$upload_dir = 'W:\domains\1_Module_RESTful_API\API\product_images';
							$upload_path = $upload_dir . '\\' . $image['name'];
							$result_file = move_uploaded_file($image['tmp_name'], $upload_path);
							
							// Upload data
							$now = date('H:i d.m.Y');
							$result_db = $this->db->query("UPDATE `product` SET `title` = '$title', `manufacturer` = '$manufacturer', `text` = '$text', `tags` = '$tags', `image` = '$upload_path' WHERE `id` = '$url[1]'");
							
							if ($result_file && $result_db) {
								$response['status code'] = 201;
								$response['status text'] = 'Successful editing';
								$response['body']['status'] = true;
								$response['body']['post']['title'] = $title;
								$response['body']['post']['datetime'] = $this->db->query("SELECT `data_of_creation` FROM `product` WHERE `id` = '$url[1]'")->fetch_assoc()['date_of_creation'];
								$response['body']['post']['■	manufacturer'] = $manufacturer;
								$response['body']['post']['text'] = $text;
								$response['body']['post']['tags'] = $tags;
								$response['body']['post']['image'] = $result_file;
							}
						}
						
					}
				}
				break;
			
			case 3:
				
				break;
			
		}
		
		echo json_encode($response);
	}
}
