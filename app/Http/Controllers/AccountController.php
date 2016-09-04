<?php

namespace App\Http\Controllers;

use Cloudoki\OaStack\Models\User;
use Cloudoki\OaStack\Models\Account;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cloudoki\Guardian\Guardian;

/**
 * Accounts Controller
 * The accounts controller uses the Laravel RESTful Resource Controller method.
 *
 * [https://laravel.com/docs/5.2/controllers#restful-resource-controllers]
 *
 * Following routes are supported
 * GET			/resource				index		resource.index
 * POST			/resource				store		resource.store
 * GET			/resource/{resource}	show		resource.show
 * PUT/PATCH	/resource/{resource}	update		resource.update
 * DELETE		/resource/{resource}	destroy		resource.destroy
 *
 *	@SWG\Tag(
		name="accounts",
		description="the Account resources"
	)
 */
class AccountController extends BaseController
{
	const type = 'account';
	
	/**
	 *  Validation Rules
	 *  Based on Laravel Validation
	 */
	protected static $getRules =
	[
		'id'	=> 'required|integer'
	];

	protected static $updateRules =
	[
		'id'	=>  'required|integer',
		'name'  =>  'string|min:2',
		'slug'  =>  'string|min:2'
	];

	protected static $postRules =
	[
		'id'	=>  'integer',
		'name'  =>  'required|string|min:2',
		'slug'  =>  'required|string|min:2'
	];

	/**
	 * Get Accounts
	 *
	 * @return array
	 *
	 * @SWG\Get(
			tags={"accounts"},
			path="/accounts",
			summary="global list of accounts (limited access)",
			description="Returns a list of all accounts, superadmin access only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Account"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
	    @SWG\Get(
			tags={"accounts"},
			path="/users/{userId}/accounts",
			summary="user accessible list of accounts",
			description="Returns a list of the accounts accessible by the user",
			@SWG\Parameter(ref="#/parameters/userId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Account"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
     */
	public function index($id = null)
	{
		# Validate
		Guardian::check ();
		
		$payload = $this->validation ( $id? ['id'=> $id]: [], $id? self::$getRules: []);
		
		# Ids filter
		if (isset ($payload->ids))
		
			$accounts = Guardian::user()->accounts()->find (explode ($payload->ids));
		
		# Retrieve accounts
		else
		
			$accounts = $id?
		
				User::find((int) $id)->accounts:
				Account::orderBy('id')->get ();
		
		return response()->json($accounts->schema ($payload->display));
	}
	
	/**
	 * Get Account
	 *
	 * @return object
	 *
	 * @SWG\Get(
			tags={"accounts"},
			path="/accounts/{id}",
			summary="single account",
			description="Returns a single Account resource, identified by ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Account")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised"),
			@SWG\Response(response=404, ref="#/responses/not_found")
		)
		
		@SWG\Get(
			tags={"accounts"}, path="users/{userId}/accounts/{id}", summary="alias", description="Alias of /accounts/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function show($id, $id2 = null)
	{
		# resources
		$payload = $this->validation (['id'=>  $id2?: $id], self::$getRules);
		$userid = $id2? $id: null;

		# Validate
		Guardian::check ((int) $payload->id);
		
		$account = $userid?
		
			$this->getUserRelAccount ($userid, $payload->id):
			Account::find($payload->id);
			
		if (!$account)
			
			throw new ModelNotFoundException();
		

		# Return Account
		return response()->json($account->schema ($payload->display));
	}

	/**
	 * Post Account
	 *
	 * @return object
	 *
	 * @SWG\Post(
			tags={"accounts"},
			path="/accounts",
			summary="create account (limited access)",
			description="Create a new floating Account resource, for superadmin only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Account")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Account")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Post(
			tags={"accounts"},
			path="/users/{userId}/accounts",
			summary="create account",
			description="Create a new Account resource",
			@SWG\Parameter(ref="#/parameters/userId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Account")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Account")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
	 */
	public function store($id = null)
	{
		$payload = $this->validation (['id'=>  $id], self::$postRules);

		# Validate
		Guardian::check ();
		
		# Create Account
		$account = new Account;
		
		# Save input
		$account->schemaUpdate((array) $payload);
		
		if ($payload->id)
			
			User::find($payload->id)->accounts()->attach ($account);


		# Return Account
		return response()->json($account->schema($payload->display));
	}

	/**
	 * Update Account
	 *
	 * @return object
	 *
	 * @SWG\Patch(
			tags={"accounts"},
			path="/accounts/{id}",
			summary="update account",
			description="Update the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Account")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Account")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="users/{userId}/accounts/{id}", summary="alias", description="Alias of /accounts/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function update($id, $id2 = null)
	{
		# resources
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$userid = $id2? $id: null;

		# Validate
		Guardian::check ((int) $payload->id);
		
		$account = $userid?
		
			$this->getUserRelAccount ($userid, $payload->id):
			Account::find($payload->id);
			
		if (!$account)
			
			throw new ModelNotFoundException();
		
		# Update account
		$account->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($account->schema ($payload->display));
	}

	/**
	 * Delete Account
	 *
	 * @return boolean
	 * 
	 * @SWG\Delete(
			tags={"accounts"},
			path="/accounts/{id}",
			summary="delete account",
			description="Soft delete the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Response(response=200, ref="#/responses/success_integer"),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Delete(
			tags={"accounts"}, path="users/{userId}/accounts/{id}", summary="alias", description="Alias of /accounts/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function destroy($id, $id2 = null)
	{
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$userid = $id2? $id: null;

		# Validate
		Guardian::check ((int) $payload->id);
		
		$account = $userid?
		
			$this->getUserRelAccount ($userid, $payload->id):
			Account::find($payload->id);
			
		if (!$account)
			
			throw new ModelNotFoundException();
		
		# Soft Delete
		$account->destroy((int) $payload->id);

		return response()->json(true);
	}
	
	/**
	 * Account getter
	 * Helper function to assert User related Account
	 *
	 * @param	mixed	$userid
	 * @param	mixed	$accountid
	 */
	public function getUserRelAccount ($userid, $accountid)
	{
		$user = User::find ((int) $userid);
		
		if (!$user)
			
			throw new ModelNotFoundException();
		
		return $user->accounts()->find((int) $accountid);	
	}
}
