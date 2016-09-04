<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function() {
	return view('welcome');
});

# System
Route::get('version', 'ApiController@apiversion');

/**
 * Oauth2 Endpoints.
 * Might be provided by the Oauth stack
 *
 * They are.
 */

/**
 *	Version 1 routes
 */
include "routes/routes-v1.php";

/**
 *	Default redirect.
 */
Route::get('/', function()
{
	return redirect (config ('app.url', 'http://api.savvy.fyi/docs'), 301);
});

