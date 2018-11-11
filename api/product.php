<?php

require_once 'base_class.php';

class product extends base_class {
	public function __construct() {
		parent::__construct();
	}
	
	public function create($title, $manufacturer, $text, $tags, $image) {
		// Authorization check
		if (!$this->check_authorized()) return;
		
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
		if (empty($image))
			$errors['image'] = 'No image';
		elseif (!(($image['type'] === 'image/jpeg' || $image['type'] === 'image/png') && $image['size'] <= 2 * pow(2, 20)))
			$errors['image'] = 'Invalid file format';//*/
		
		if (!empty($errors)) {
			$this->response(400, 'Creating error', false, array('message' => $errors));
			return;
		}
		
		// If there is no errors do that needs
		
		// Upload image
		$upload_dir = 'product_images';
		$upload_path = $upload_dir . '\\' . $title . '_' . $image['name'];
		move_uploaded_file($image['tmp_name'], $upload_path);//*/
		
		// Upload data
		$upload_path_patched = $this->db->real_escape_string($upload_path);
		//$upload_path_patched = '123 path';
		$datetime = $this->get_datetime();
		$this->db->query("INSERT INTO `product` (`title`, `manufacturer`, `text`, `image`, `datetime`) VALUES ('$title', '$manufacturer', '$text', '$upload_path_patched', '$datetime')");
		$product_id = $this->db->insert_id;
		
		// Upload tags
		$tags = explode(',', $tags);
		$tags = array_unique($tags);
		foreach ($tags as $tag) {
			$tag = trim($tag);
			if ($tag === '') continue;
			$this->db->query("INSERT INTO `tag` (`tag`, `product_id`) VALUES ('$tag', '$product_id')");
		}
		
		$this->response(201, 'Successful creation', true, array('post_id' => $product_id));
	}
	
	public function edit($product_id, $title, $manufacturer, $text, $tags, $image) {
		// Authorization check
		if (!$this->check_authorized()) return;
		
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
		$tags = explode(',', $tags);
		$tags = array_unique($tags);
		foreach ($tags as $tag) {
			$tag = trim($tag);
			if ($tag === '') continue;
			$this->db->query("INSERT INTO `tag` (`product_id`, `tag`) VALUES ('$product_id', '$tag')");
		}
		
		$datetime = date('H:i d.m.Y', strtotime($product['datetime']));
		$this->response(201, 'Successful editing', true, array('post' => array(
			'title' => $title,
			'datetime' => $datetime,
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
		
		// If there is no errors do that needs
		$this->db->query("DELETE FROM `product` WHERE `id` = '$product_id'");
		$this->response(201, 'Successful deletion', true);
	}
	
	public function view() {
		$products_db = $this->db->query("SELECT `id`, `title`, `datetime`, `manufacturer`, `text`, `image` FROM `product`");
		
		while ($product = $products_db->fetch_assoc()) {
			$product_id = $product['id'];
			
			// Find tags
			$tags_db = $this->db->query("SELECT `tag` FROM `tag` WHERE `product_id` = '$product_id'");
			while ($tag = $tags_db->fetch_assoc())
				$tags[] = $tag['tag'];
			if (isset($tags))
				$product['tags'] = implode(', ', $tags);
			
			// Find comments
			$comments_db = $this->db->query("SELECT `id` AS 'comment_id', `datetime`, `author`, `text` FROM `comment` WHERE `product_id` = '$product_id'");
			while ($comment = $comments_db->fetch_assoc())
				$product['comments'][] = $comment;
			
			unset($product['id']);
			
			$products[] = $product;
		}
		
		$this->response(200, 'List products', null, isset($products) ? $products : null);
	}
	
	public function view_one($product_id) {
		$product = $this->db->query("SELECT `title`, `datetime`, `manufacturer`, `text`, `image` FROM `product` WHERE `id` = '$product_id'");
		
		if ($product->num_rows === 0) {
			$this->response(404, 'Product not found', null, array('message' => 'Product not found'));
			return;
		}
		
		$product = $product->fetch_assoc();
		
		// Find tags
		$tags_db = $this->db->query("SELECT `tag` FROM `tag` WHERE `product_id` = '$product_id'");
		while ($tag = $tags_db->fetch_assoc())
			$tags[] = $tag['tag'];
		if (isset($tags))
			$product['tags'] = implode(', ', $tags);
		
		// Find comments
		$comments_db = $this->db->query("SELECT `id` AS 'comment_id', `datetime`, `author`, `text` FROM `comment` WHERE `product_id` = '$product_id'");
		while ($comment = $comments_db->fetch_assoc())
			$product['comments'][] = $comment;
		
		$this->response(200, 'View product', null, $product);
	}
	
	public function comment($product_id, $author, $text) {
		// Product existing check
		if ($this->db->query("SELECT COUNT(*) AS 'count' FROM `product` WHERE `id` = '$product_id'")->fetch_assoc()['count'] === '0') {
			$this->response(404, 'Product not found', null, array('message' => 'Product not found'));
			return;
		}
		
		// Author check
		if ($this->check_authorized(false)) {
			if ($author === null)
				$author = 'Admin';
		} else {
			if (empty($text))
				$errors['author'] = 'Empty author';
		}
		
		// Text check
		if (empty($text))
			$errors['text'] = 'Empty text';
		elseif (strlen($text) > 255)
			$errors['text'] = 'Text is too large';
		
		if (!empty($errors)) {
			$this->response(400, 'Commenting error', false, array('message' => $errors));
			return;
		}
		
		// If there is no errors do that needs
		$datetime = $this->get_datetime();
		$this->db->query("INSERT INTO `comment` (`product_id`, `author`, `text`, `datetime`) VALUE ('$product_id', '$author', '$text', '$datetime')");
		$this->response(201, 'Successful commenting', true);
	}
	
	public function delete_comment($product_id, $comment_id) {
		// Authorization check
		if (!$this->check_authorized()) return;
		
		// Product existing check
		if ($this->db->query("SELECT COUNT(*) AS 'count' FROM `product` WHERE `id` = '$product_id'")->fetch_assoc()['count'] === '0') {
			$this->response(404, 'Product not found', null, array('message' => 'Product not found'));
			return;
		}
		
		// Comment existing check
		if ($this->db->query("SELECT COUNT(*) AS 'count' FROM `comment` WHERE `product_id` = '$product_id' AND `id` = '$comment_id'")->fetch_assoc()['count'] === '0') {
			$this->response(404, 'Comment not found', null, array('message' => 'Comment not found'));
			return;
		}
		
		// If there is no errors do that needs
		$this->db->query("DELETE FROM `comment` WHERE `product_id` = '$product_id' AND `id` = '$comment_id'");
		$this->response(201, 'Successful deleting', true);
	}
	
	public function search_by_tag($tag) {
		$product_ids_db = $this->db->query("SELECT `product_id` FROM `tag` WHERE `tag` = '$tag'");
		while ($product_id = $product_ids_db->fetch_assoc())
			$product_ids[] = $product_id['product_id'];
		
		if (empty($product_ids)) {
			$this->response(200, 'Products not found');
			return;
		}
		
		foreach ($product_ids as $product_id) {
			// Find product
			$product = $this->db->query("SELECT `title`, `datetime`, `manufacturer`, `text`, `image` FROM `product` WHERE `id` = '$product_id'");
			$product = $product->fetch_assoc();
			
			// Find tags
			$tags_db = $this->db->query("SELECT `tag` FROM `tag` WHERE `product_id` = '$product_id'");
			while ($tag = $tags_db->fetch_assoc())
				$tags[] = $tag['tag'];
			if (isset($tags))
				$product['tags'] = implode(', ', $tags);
			
			$products[] = $product;
		}
		
		/** @noinspection PhpUndefinedVariableInspection */
		$this->response(200, 'Found products', null, $products);
	}
}
