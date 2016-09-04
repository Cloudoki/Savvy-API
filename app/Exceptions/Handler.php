<?php

namespace App\Exceptions;

use Exception;
use Cloudoki\InvalidUserException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
		# Abort
        if ($e instanceof ModelNotFoundException) 
        
        	return response ('Resource or endpoint not found', 404);
        	
        else if ($e instanceof InvalidUserException) 
        
        	return response ('Not Authourised to access this endpoint or resource', 403);
        	
        else if ($e instanceof ValidationException) 
        
        	return response ($e->getMessage (), 500);
        	
       
	    parent::report($e);
	    
    	return response ('You just experienced the staggering waves of a tiny bug', 500);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
}
