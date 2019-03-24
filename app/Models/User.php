<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
	
	public function check_login_exists() {
		return static::where('login', $this->login)->count() > 0;
	}
	
	public function check_password_right() {
		return static::where([ [ 'login', $this->login ], [ 'password', $this->password ] ])->first();
	}
	
}
