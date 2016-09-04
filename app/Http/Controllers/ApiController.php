<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Class ApiController
 *
 *	@SWG\Tag(
		name="system",
		description="info endpoints"
	)
 */
class ApiController extends BaseController
{
	/**
	 *	Get the API version
	 *	Also used as basic API up test
	 *
	 *	@return	object
	 *
	 * @SWG\Get(
			tags={"system"},
			path="/version",
			summary="API version",
			description="Returns a the current stable api version. Public endpoint",
			@SWG\Response(response=200, ref="#/responses/success_object")
		)
	 */
	public function apiversion ()
	{
		# Default version on config file
		return response ()->json (['version'=> env ('APP_VERSION')]);
	}

	/**
	 *	Get the API beat
	 *	Also used as basic BLM up test
	 *
	 *	@return	string
	 *
	 * @SWG\Get(
			tags={"system"},
			path="/ping",
			summary="status",
			description="Returns a pong if the api stack is up. Public endpoint",
			@SWG\Response(response=200, ref="#/responses/success_string")
		)
	 */
	public function ping ()
	{
		return $this->restDispatch('pong', 'ApiController');
	}
	
	/**
	 *	Return folded API beat
	 *	Also used as basic BLM up test
	 */
	public function pong ()
	{
		return "pong";
	}
}
