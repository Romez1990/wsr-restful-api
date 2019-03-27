<?php

namespace App\Http\Middleware;

use App\JsonResponse;
use App\Models\Session;
use Closure;

class Authorized {
	
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$session = new Session();
		$session->ip = $request->server('REMOTE_ADDR');
		$session->user_agent = $request->server('HTTP_USER_AGENT');
		if (empty($request->header('Authorization')) || !$session->check_authorized($request->header('Authorization')))
			return response([ 'message' => 'Unauthorized' ])->setStatusCode(401, 'Unauthorized');
		
		return $next($request);
	}
	
}
