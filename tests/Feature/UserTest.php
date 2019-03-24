<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase {
	
	use WithFaker;
	
	//#region Success
	
	private $login;
	private $password;
	
	public function test_authentication() {
		$this->login    = $this->faker->userName;
		$this->password = $this->faker->password;
		
		$this->post('/api/reg', [
			'login'    => $this->login,
			'password' => $this->password
		])
		     ->assertStatus('201 Successful registration')
		     ->assertJson([
			                  'status' => true
		                  ]);
		
		$this->assertDatabaseHas('users', [
			'login'    => $this->login,
			'password' => md5($this->password)
		]);
		
		// With errors
		$this->post('/api/reg', [
			'login'    => $this->login,
			'password' => $this->password
		])
		     ->assertStatus('400 Registration error')
		     ->assertJson([
			                  'status'  => false,
			                  'message' => [
				                  'login' => 'Already exists'
			                  ]
		                  ]);
		
		// Authorization
		$this->post('/api/auth', [
			'login'    => $this->login,
			'password' => $this->password
		])
		     ->assertStatus('200 Successful authorization')
		     ->assertJson([
			                  'status' => true
		                  ]);
		
		// With errors
		$this->post('/api/auth', [
			'login'    => $this->login . 1,
			'password' => $this->password
		])
		     ->assertStatus('400 Authorization error')
		     ->assertJson([
			                  'status'  => false,
			                  'message' => [
				                  'login' => 'Not found'
			                  ]
		                  ]);
		
		$this->post('/api/auth', [
			'login'    => $this->login,
			'password' => $this->password . 1
		])
		     ->assertStatus('400 Authorization error')
		     ->assertJson([
			                  'status'  => false,
			                  'message' => [
				                  'password' => 'Does not match'
			                  ]
		                  ]);
	}
	
	//#endregion
	
}