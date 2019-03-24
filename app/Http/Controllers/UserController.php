<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Session;
use Illuminate\Http\Request;

class UserController extends Controller {
	
	public function register(Request $request) {
		$user = new User();
		$user->login = $request->input('login');
		$user->password = md5($request->input('password'));
		
		if ($user->check_login_exists())
			return response([ 'status' => false, 'message' => [ 'login' => 'Already exists' ] ])->setStatusCode(400, 'Registration error');
		
		$user->save();
		
		return response([ 'status' => true ])->setStatusCode(201, 'Successful registration');
	}
	
	public function authorize_(Request $request) {
		$user = new User();
		
		$user->login = $request->input('login');
		if (!$user->check_login_exists())
			return response([ 'status' => false, 'message' => [ 'login' => 'Not found' ] ])->setStatusCode(400, 'Authorization error');
		
		$user->password = md5($request->input('password'));
		if (!($user = $user->check_password_right()))
			return response([ 'status' => false, 'message' => [ 'password' => 'Does not match' ] ])->setStatusCode(400, 'Authorization error');
		
		$session = new Session();
		$session->user_id = $user->id;
		$session->ip = $request->server('REMOTE_ADDR');
		$session->user_agent = $request->server('HTTP_USER_AGENT');
		if ($res = $session->find_session()) {
			$session = $res;
		} else {
			$session->token = md5($user->password . $session->ip . $session->user_agent);
			$session->save();
		}
		
		return response([ 'status' => true, 'token' => $session->token ])->setStatusCode(200, 'Successful authorization');
	}
	
}
