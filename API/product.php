<?php

require_once 'base_class.php';

class product extends base_class {
	public function __construct() {
		parent::__construct();
	}
	
	public function create() {
		// Authorization check
//		if (!$this->check_authorized()) return;
		
		$title = $_POST['title'];
		$manufacturer = $_POST['manufacturer'];
		$text = $_POST['text'];
		$tags = $_POST['tags'];
		$image = $_FILES['image'];
		
		// Existing check
		if ($this->db->query("SELECT COUNT(*) AS 'count' FROM `product` WHERE `title` = '$title'")->fetch_assoc()['count'] !== '0') {
			$this->response(400, 'Creating error', false, array('message' => array('title' => 'Already exists')));
			return;
		}
		
		// Input fields check
		if (empty($title))
			$errors['title'] = 'Empty title';
		if (empty($manufacturer))
			$errors['manufacturer'] = 'Empty manufacturer';
		if (empty($text))
			$errors['text'] = 'Empty text';
		/*if (empty($image))
			$errors['image'] = 'No image';
		elseif (!(($image['type'] === 'image/jpeg' || $image['type'] === 'image/png') && $image['size'] <= 2 * pow(2, 20)))
			$errors['image'] = 'Invalid file format';*/
		
		if (!empty($errors)) {
			$this->response(400, 'Creating error', false, array('message' => $errors));
			return;
		}
		
		// If there is no errors do that needs
		
		// Upload image
		/*$upload_dir = 'product_images';
		$upload_path = $upload_dir . '\\' . $title . '_' . $image['name'];
		move_uploaded_file($image['tmp_name'], $upload_path);*/
		
		// Upload data
//		$upload_path_patched = $this->db->real_escape_string($upload_path);
		$upload_path_patched = '123 path';
		$a = $this->db->query("INSERT INTO `product` (`title`, `manufacturer`, `text`, `image`) VALUES ('$title', '$manufacturer', '$text', '$upload_path_patched')");
		$product_id = $this->db->insert_id;
		
		// Upload tags
		foreach (explode(',', $tags) as $tag) {
			$tag = trim($tag);
			$this->db->query("INSERT INTO `tag` (`tag`, `product_id`) VALUES ('$tag', '$product_id')");
		}
		
		$this->response(201, 'Successful creation', true, array('post_id' => $product_id));
	}
	
	public function edit($product_id) {
		// Authorization check
//		if (!$this->check_authorized()) return;
		
		$title = $_POST['title'];
		$manufacturer = $_POST['manufacturer'];
		$text = $_POST['text'];
		$tags = $_POST['tags'];
		$image = $_FILES['image'];
		
		$product = $this->db->query("SELECT * FROM `product` WHERE `id` = '$product_id'");
		
		// Existing check
		if ($product->num_rows === 0) {
			$this->response(404, 'Product not fount', null, array('message' => 'Product not fount'));
			return;
		}
		
		$product = $product->fetch_assoc();
		
		if (empty($title))
			$errors['title'] = 'Empty title';
		elseif ($this->db->query("SELECT COUNT(*) AS 'count' FROM `product` WHERE `title` = '$title' AND `id` != '$product_id'")->fetch_assoc()['count'] !== '0')
			$errors['title'] = 'This title already exists';
		if (empty($manufacturer))
			$errors['manufacturer'] = 'Empty manufacturer';
		if (empty($text))
			$errors['text'] = 'Empty text';
		if (empty($image))
			$errors['image'] = 'No image';
		elseif (!(($image['type'] === 'image/jpeg' || $image['type'] === 'image/png') && $image['size'] <= 2 * pow(2, 20)))
			$errors['image'] = 'Invalid file format';
		
		if (!empty($errors)) {
			$this->response(400, 'Creating error', false, array('message' => $errors));
			return;
		}
		
		// If there is no errors do that needs
		
		// Remove image
		if (file_exists($product['image'])) unlink($product['image']);
		// Upload image
		$upload_dir = 'product_images';
		$upload_path = $upload_dir . '\\' . $title . '_' . $image['name'];
		move_uploaded_file($image['tmp_name'], $upload_path);
		
		// Upload data
		$upload_path_patched = $this->db->real_escape_string($upload_path);
		$this->db->query("UPDATE `product` SET `title` = '$title', `manufacturer` = '$manufacturer', `text` = '$text', `image` = '$upload_path_patched' WHERE `id` = '$product_id'");
		
		// Upload tags
		$this->db->query("DELETE FROM `tag` WHERE `product_id` = '$product_id'");
		foreach (explode(',', $tags) as $tag) {
			$tag = trim($tag);
			$this->db->query("INSERT INTO `tag` (`product_id`, `tag`) VALUES ('$product_id', '$tag')");
		}
		
		$this->response(201, 'Successful editing', true, array('post' => array(
			'title' => $title,
			'datetime' => $product['datetime'],
			'manufacturer' => $manufacturer,
			'text' => $text,
			'tags' => $tags,
			'image' => $upload_path
		)));
	}
	
	public function delete($product_id) {
		// Authorization check
		if (!$this->check_authorized()) return;
		
		// Existing check
		if ($this->db->query("SELECT COUNT(*) AS 'count' FROM `product` WHERE `id` = '$product_id'")->fetch_assoc()['count'] === '0') {
			$this->response(404, 'Product not fount', null, array('message' => 'Product not fount'));
			return;
		}
		
		$this->db->query("DELETE FROM `product` WHERE `id` = '$product_id'");
		$this->response(201, 'Successful deletion', true, null);
	}
	
	public function comment($product_id) {
		$author = $_POST['author'];
		$comment = $_POST['comment'];
		
		$this->response(404, 'Product not found', null, array('message' => 'Product not found'));
		
		$this->response(400, 'Commenting error', false, array('message' => 'An error'));
		
		$this->response(201, 'Successful commenting', true, null);
	}
	
	public function delete_comment($product_id, $comment_id) {
	
	}
}
