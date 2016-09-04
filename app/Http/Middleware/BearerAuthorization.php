<?php

namespace App\Http\Middleware;

use Closure;

class BearerAuthorization
{
    
	public function handle($request, Closure $next)
	{
		// Get Authorization header
		$auth = $request->header ('Authorization');
		
		if(!$auth || strlen ($auth) < 18)
		
			return response ('not authorised', 401);
		
		
		// Add Access token to input
		$bearer = explode(' ', $auth);
		
		config (['app.access_token'=> array_pop ($bearer)]);
		
		return $next($request);
	}
}
