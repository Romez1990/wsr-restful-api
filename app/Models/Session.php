<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model {
	
	public function find_session() {
		return static::where([ [ 'user_id', $this->user_id ], [ 'ip', $this->ip ], [ 'user_agent', $this->user_agent ] ])->first();
	}
	
	public function check_authorized($token) {
		return static::where([ [ 'ip', $this->ip ], [ 'user_agent', $this->user_agent ], [ 'token', $token ] ])->count() > 0;
	}
	
}
