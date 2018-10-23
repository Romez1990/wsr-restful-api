<?php
require_once 'base_class.php';

class product extends base_class {
	public function __construct($url) {
		parent::__construct();
		
		switch (count($url)) {
			case 1:
				if ($this->method == 'POST') {
					// Creating
					$title = $_POST['title'];
					$manufacturer = $_POST['manufacturer'];
					$text = $_POST['text'];
					$tags = $_POST['tags'];
					$image = $_FILES['image'];
					
					// Already exists check
					if ($this->db->query("SELECT * FROM `product` WHERE `title` = '$title'")->num_rows !== 0) {
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
					if (!isset($response['status code'])) {
						// Upload image
						$upload_dir = 'product_images';
						$upload_path = $upload_dir . '\\' . $image['name'];
						$result_file = move_uploaded_file($image['tmp_name'], $upload_path);
						
						// Upload data
						$now = date('H:i d.m.Y');
						$p = $this->db->real_escape_string($upload_path);
						$result_db = $this->db->query("INSERT INTO `product` (`title`, `manufacturer`, `text`, `tags`, `image`, `date_of_creation`) VALUES ('$title', '$manufacturer', '$text', '$tags', '$p', '$now')");
						
						if ($result_file && $result_db) {
							// Successful
							$response['status code'] = 201;
							$response['status text'] = 'Successful creation';
							$response['body']['status'] = true;
							$response['body']['post_id'] = $this->db->query("SELECT `id` FROM `product` WHERE `title` = '$title'")->fetch_assoc()['id'];
						}
					}
					
				} else {
					// GET
					// View
					
					$list = $this->db->query("SELECT `title`, `date_of_creation`, `manufacturer`, `text`, `tags`, `image` FROM `product`");
					
					$products = null;
					
					while ($product = $list->fetch_assoc()) {
						$products[] = $product;
					}
					
					$response['status code'] = 200;
					$response['status text'] = 'List products';
					$response['list_products'] = $products;
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
								var_dump($image);
								$response['status code'] = 400;
								$response['status text'] = 'Creating error';
								$response['body']['status'] = false;
								$response['body']['message']['image'] = 'invalid file format';
							}
							
							// Updating
							if (!$response['status code']) {
								// Upload image
								$r = $this->db->query("SELECT `image` FROM `product` WHERE `id` = '$url[1]'");
								$d = $r->fetch_assoc()['image'];
								unlink($d);
								$upload_dir = 'product_images';
								$upload_path = $upload_dir . '\\' . $image['name'];
								$result_file = move_uploaded_file($image['tmp_name'], $upload_path);
								
								// Upload data
								$now = date('H:i d.m.Y');
								$p = $this->db->real_escape_string($upload_path);
								$result_db = $this->db->query("UPDATE `product` SET `title` = '$title', `manufacturer` = '$manufacturer', `text` = '$text', `tags` = '$tags', `image` = '$p' WHERE `id` = '$url[1]'");
								
								if ($result_file && $result_db) {
									$response['status code'] = 201;
									$response['status text'] = 'Successful editing';
									$response['body']['status'] = true;
									$response['body']['post']['title'] = $title;
									$response['body']['post']['datetime'] = $this->db->query("SELECT `date_of_creation` FROM `product` WHERE `id` = '$url[1]'")->fetch_assoc()['date_of_creation'];
									$response['body']['post']['manufacturer'] = $manufacturer;
									$response['body']['post']['text'] = $text;
									$response['body']['post']['tags'] = $tags;
									$response['body']['post']['image'] = $result_file;
								}
							}
						}
						
					} else {
						// GET
						// View one
						
						$product = $this->db->query("SELECT `title`, `date_of_creation`, `manufacturer`, `text`, `tags`, `image` FROM `product` WHERE `id` = '$url[1]'")->fetch_assoc();
						
						$response['status code'] = 200;
						$response['status text'] = 'List products';
						$response['product'] = $product;
					}
				}
				break;
			
			case 3:
				if (is_numeric($url[1]) && $url[2] == 'comment') {
					$author = $_POST['author'];
					$text = $_POST['text'];
					
					if ($this->db->query("SELECT COUNT(`title`) AS `count` FROM `product` WHERE `id` = '$url[1]'")->fetch_assoc()['count'] === 0) {
						$response['status code'] = 400;
						$response['status text'] = 'Creating error';
						$response['body']['status'] = false;
						$response['body']['message']['product'] = 'Product not found';
					}
					
					if (empty($response['status code'])) {
						$now = date('H:i d.m.Y');
						$result = $this->db->query("INSERT INTO `comment` (`id_product`, `author`, `text`, `date_of_creation`) VALUES ('$url[1]', '$author', '$text', '$now')");
						
						if ($result) {
							$response['status code'] = 201;
							$response['status text'] = 'Successful creation';
							$response['body']['status'] = true;
						}
					}
				}
				break;
			
			case 4:
				if (is_numeric($url[1]) && $url[2] == 'comment' && is_numeric($url[3])) {
					// DELETE
					
				}
				break;
		}
		
		echo json_encode($response);
	}
}
