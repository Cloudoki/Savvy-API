<?php

namespace App\Http\Middleware;

use Closure;

class CorsHeaders 
{
    
    public function handle($request, Closure $next)
    {
        # API Headers
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, PATCH, POST, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
		header('Access-Control-Allow-Credentials: true');
		
		if ($request->getMethod() == "OPTIONS")
		{
            return response ('ok', 200);
        }
		
		return $next($request);
    }
}
