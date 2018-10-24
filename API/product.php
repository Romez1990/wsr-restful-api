<?php
require_once 'base_class.php';

class product extends base_class {
	public function __construct($url) {
		parent::__construct();
		
		$response = null;
		
		switch (count($url)) {
			case 1:
				if ($this->method === 'POST') {
					// Creating product
					
					$title = $_POST['title'];
					$manufacturer = $_POST['manufacturer'];
					$text = $_POST['text'];
					$image = $_FILES['image'];
					$tags = explode(',', $_POST['tags']);
					foreach ($tags as $i => $tag) $tags[$i] = trim($tag);
					
					// Check Already exists
					if ($this->db->query("SELECT COUNT(*) AS `count` FROM `product` WHERE `title` = '$title'")->fetch_assoc()['count'] != 0) {
						$response['status code'] = 400;
						$response['status text'] = 'Creating error';
						$response['body']['status'] = false;
						$response['body']['message']['title'] = 'Already exists';
					}
					
					// Check empty text
					if (empty($text)) {
						$response['status code'] = 400;
						$response['status text'] = 'Creating error';
						$response['body']['status'] = false;
						$response['body']['message']['manufacturer'] = 'Empty text';
					}
					
					// Check empty manufacturer
					if (empty($manufacturer)) {
						$response['status code'] = 400;
						$response['status text'] = 'Creating error';
						$response['body']['status'] = false;
						$response['body']['message']['manufacturer'] = 'Empty manufacturer';
					}
					
					// Check invalid file format
					$valid_size = 2 * pow(2, 20); // 2 MB
					if ($image['size'] > $valid_size || $image['type'] !== 'image/png' && $image['type'] !== 'image/jpeg') {
						$response['status code'] = 400;
						$response['status text'] = 'Creating error';
						$response['body']['status'] = false;
						$response['body']['message']['image'] = 'Invalid file format';
					}
					
					// If there is no errors
					if (!isset($response['status code'])) {
						// Upload image
						$upload_dir = 'product_images';
						$upload_path = $upload_dir . '\\' . $image['name'];
						$result_file = move_uploaded_file($image['tmp_name'], $upload_path);
						
						// Upload data
						$now = date('H:i d.m.Y');
						$upload_path_patched = $this->db->real_escape_string($upload_path);
						$result_db = $this->db->query("INSERT INTO `product` (`title`, `manufacturer`, `text`, `image`, `date_of_creation`) VALUES ('$title', '$manufacturer', '$text', '$upload_path_patched', '$now')");
						
						$product_id = $this->db->insert_id;
						foreach ($tags as $i => $tag) {
							//$this->db->query("INSERT INTO `tag` (`product_id`, `tag`) VALUES ('$elem','$product_id')");
						}
						
						if ($result_file && $result_db) {
							// Successful
							$response['status code'] = 201;
							$response['status text'] = 'Successful creation';
							$response['body']['status'] = true;
							$response['body']['post_id'] = $id_product;
						}
					}
					
				} elseif ($this->method === 'GET') {
					// View product
					
					$list = $this->db->query("SELECT `title`, `manufacturer`, `text`, `tags`, `image`, `date_of_creation` FROM `product`");
					
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
				if (!is_numeric($url[1])) return;
				
				if ($this->method === 'POST') {
					// Editing product
					
					$product = $this->db->query("SELECT * FROM `product` WHERE `id` = '$url[1]'");
					
					if ($product->num_rows == 0) {
						// Product not found
						$response['status code'] = 404;
						$response['status text'] = 'Product not found';
						$response['body']['message'] = 'Product not found';
					} else {
						$product = $product->fetch_assoc();
						
						$title = empty($_POST['title']) ? $product['title'] : $_POST['title'];
						$manufacturer = $_POST['manufacturer'];
						$text = empty($_POST['text']) ? $product['text'] : $_POST['text'];
						$tags = empty($_POST['tags']) ? $product['tags'] : $_POST['tags'];
						$image = $_FILES['image'];
						
						// Already exists check
						if ($this->db->query("SELECT COUNT(*) AS `count` FROM `product` WHERE `title` = '$title' AND `id` != '$url[1]'")->fetch_assoc()['count'] != 0) {
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
							unlink($product['image']);
							$upload_dir = 'product_images';
							$upload_path = $upload_dir . '\\' . $image['name'];
							$result_file = move_uploaded_file($image['tmp_name'], $upload_path);
							
							// Upload data
							$upload_path_patched = $this->db->real_escape_string($upload_path);
							$result_db = $this->db->query("UPDATE `product` SET `title` = '$title', `manufacturer` = '$manufacturer', `text` = '$text', `tags` = '$tags', `image` = '$upload_path_patched' WHERE `id` = '$url[1]'");
							
							if ($result_file && $result_db) {
								$response['status code'] = 201;
								$response['status text'] = 'Successful editing';
								$response['body']['status'] = true;
								$response['body']['post']['title'] = $title;
								$response['body']['post']['datetime'] = $product['date_of_creation'];
								$response['body']['post']['manufacturer'] = $manufacturer;
								$response['body']['post']['text'] = $text;
								$response['body']['post']['tags'] = $tags;
								$response['body']['post']['image'] = $result_file;
							}
						}
					}
					
				} elseif ($this->method === 'GET') {
					// View one product
					
					$product = $this->db->query("SELECT `title`, `date_of_creation`, `manufacturer`, `text`, `tags`, `image` FROM `product` WHERE `id` = '$url[1]'");
					
					// If there is no such product
					if ($product->num_rows == 0) {
						$response['status code'] = 404;
						$response['status text'] = 'Product not found';
						$response['body']['message'] = 'Product not found';
					} else {
						$product = $product->fetch_assoc();
						
						$response['status code'] = 200;
						$response['status text'] = 'List products';
						$response['product'] = $product;
					}
				} elseif ($this->method === "DELETE") {
					// Delete product
					
					$product = $this->db->query("SELECT `image` FROM `product` WHERE `id` = '$url[1]'");
					
					if ($product->num_rows == 0) {
						$response['status code'] = 404;
						$response['status text'] = 'Product not found';
						$response['body']['message'] = 'Product not found';
					} else {
						$product = $product->fetch_assoc();
						unlink($product['image']);
						$this->db->query("DELETE FROM `product` WHERE `id` = '$url[1]'");
						
						$response['status code'] = 201;
						$response['status text'] = 'Successful delete';
						$response['body']['status'] = true;
					}
				}
				break;
			
			case 3:
				if (!is_numeric($url[1]) && $url[2] !== 'comment') return;
				
				if ($this->method === 'POST') {
					// Add comment
					
					if ($this->db->query("SELECT COUNT(*) AS `count` FROM `product` WHERE `id` = '$url[1]'")->fetch_assoc()['count'] == 0) {
						$response['status code'] = 404;
						$response['status text'] = 'Product not found';
						$response['body']['message'] = 'Product not found';
					} else {
						$author = $_POST['author'];
						$text = $_POST['text'];
						
						if (empty($author)) {
							$response['status code'] = 400;
							$response['status text'] = 'Creating error';
							$response['body']['status'] = false;
							$response['body']['message']['Author'] = 'Author is empty';
						}
						
						if (empty($text)) {
							$response['status code'] = 400;
							$response['status text'] = 'Creating error';
							$response['body']['status'] = false;
							$response['body']['message']['Text'] = 'Text is empty';
						}
						
						if (!isset($response['status code'])) {
							$now = date('H:i d.m.Y');
							$this->db->query("INSERT INTO `comment` (`product_id`, `author`, `text`, `date_of_creation`) VALUES ('$url[1]', '$author', '$text', '$now')");
							
							$response['status code'] = 201;
							$response['status text'] = 'Successful creation';
							$response['body']['status'] = true;
						}
					}
				}
				break;
			
			case 4:
				if (!is_numeric($url[1]) && $url[2] !== 'comment' && !is_numeric($url[3])) return;
				
				if ($this->method === 'DELETE') {
					// Delete comment
					if ($this->db->query("SELECT COUNT(*) AS `count` FROM `product` WHERE `id` = '$url[1]'")->fetch_assoc()['count'] == 0) {
						$response['status code'] = 404;
						$response['status text'] = 'Product not found';
						$response['body']['message'] = 'Product not found';
					} else {
						if ($this->db->query("SELECT COUNT(*) AS `count` FROM `comment` WHERE `id` = '$url[3]' AND `product_id` = '$url[1]'")->fetch_assoc()['count'] == 0) {
							$response['status code'] = 404;
							$response['status text'] = 'Comment not found';
							$response['body']['message'] = 'Comment not found';
						} else {
							$this->db->query("DELETE FROM `comment` WHERE `id` = '$url[3]'");
							
							$response['status code'] = 201;
							$response['status text'] = 'Successful delete';
							$response['body']['status'] = true;
						}
					}
				}
				break;
		}
		
		echo json_encode($response);
	}
}
