<?php

namespace App\Http\Controllers;

use Cloudoki\OaStack\Models\User;
use Cloudoki\OaStack\Models\Account;
use Cloudoki\OaStack\OAuth2Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cloudoki\Guardian\Guardian;

/**
 * Users Controller
 * The users controller uses the Laravel RESTful Resource Controller method.
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
		name="users",
		description="the User resources"
	)
 */
class UserController extends BaseController
{
    const type = 'user';
    
    /**
     *  Validation Rules
     *  Based on Laravel Validation
     */
    protected static $getRules =
    [
        'id'    =>  'required|integer'
    ];

    protected static $updateRules =
    [
        'id'        =>  'required|integer',
        'firstname' =>  'min:2',
        'lastname'  =>  'min:2',
        'email'     =>  'email',
        'avatar'    =>  'min:2'
    ];

    protected static $postRules =
    [
        'id' 		=>  'required|integer',
        'firstname' =>  'required|min:2',
        'lastname'  =>  'required|min:2',
        'email'     =>  'required|email',
    ];

    protected static $updatePasswordRules =
    [
        'id'        =>  'required|integer',
        'password'  =>  'required|min:8'
    ];


    /**
     *  RESTful actions
     */

    /**
     *  Get Users
     *
     *  @return array
     *
     * @SWG\Get(
			tags={"users"},
			path="/users",
			summary="global list of users (limited access)",
			description="Returns a list of all users, superadmin access only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/User"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
	    @SWG\Get(
			tags={"users"},
			path="/accounts/{accountId}/users",
			summary="account accessible list of users",
			description="Returns a list of the users accessible by the account",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/User"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
     */
    public function index($id = null)
    {
		# Validate
		Guardian::check ();
		
        $payload = $this->validation ( $id? ['id'=> $id]: [], $id? self::$getRules: []);

		if ($id)
		{
			$account = Account::find((int) $payload->id);
			
			# Ids Filter
			if (isset ($payload->ids))
				
				$list = $this->filterByIds ($account, $payload->ids);
	
			# Search Filter
			else if (isset ($payload->q))
				
				$list = $this->search ($account, $payload->q, ['firstname', 'lastname', 'email']);
			
			# Account related
			else $list = $account->users;
		}
        else
        	$list = User::orderBy('id')->get();
		
		
		# return all (account) users
		return response()->json ($list->schema($payload->display));
    }
    
    /**
     * Get Me
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"users"},
			path="/me",
			summary="related user",
			description="Returns the requesting User resource, identified by the auth process",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=401, ref="#/responses/default"),
		)
	 */
    public function me ()
    {
		# Validation
		$payload = $this->validation ([],[]);
		
		$user = Guardian::user ();
		
		if (!$user)
			
			throw new ModelNotFoundException();
		
        # Return User
		return response()->json($user->schema ($payload->display));
    }
    
    /**
     * Get User
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"users"},
			path="/users/{id}",
			summary="single user",
			description="Returns a single User resource, identified by ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised"),
			@SWG\Response(response=404, ref="#/responses/not_found")
		)
		
		@SWG\Get(
			tags={"users"}, path="accounts/{accountId}/users/{id}", summary="alias", description="Alias of /users/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function show($id, $id2 = null)
    {
		# resources
		$payload = $this->validation (['id'=>  $id2?: $id], self::$getRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$user = $accountid?
		
			$this->getAccountRelUser ($accountid, $payload->id):
			User::find($payload->id);
			
		if (!$user)
			
			throw new ModelNotFoundException();
		

		# Return Account
		return response()->json($user->schema ($payload->display));
    }
    
    

    /**
	 * Post User
	 *
	 * @return object
	 *
	 * @SWG\Post(
			tags={"users"},
			path="/users",
			summary="create user (limited access)",
			description="Create a new floating User resource, for superadmin only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Post(
			tags={"users"},
			path="/accounts/{accountId}/users",
			summary="create user",
			description="Create a new User resource",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
	 */
	public function store($id = null)
	{
		$payload = $this->validation (['id'=>  $id], self::$postRules);

		# Validate
		Guardian::check ($id? (int) $id: null);
		
		# Existing user
		$user = User::where('email', $payload->email)->first();

		if (!$user)
		{
			# Save input
			$user = new User;
			$user->schemaUpdate((array) $payload);
		}

		if ($payload->id)
		{
			$account = Account::find($payload->id);

			if (!$account)

				throw new AuthorizationException ('Not a valid account');

			$related = $user->accounts->find($payload->id);

			if ( ! $related)
			{
				$invitation_token = $user->makeToken();
				$account->users()->attach($user->getId(), ['invitation_token' => $invitation_token]);
			}
			else
			{
				$invitation_token = $related->getInvitationToken();
			}

			# Send invitation
			OAuth2Controller::invite($user, $account, $invitation_token);
		}


		# Return Account
		return response()->json($user->schema($payload->display));
	}

    /**
	 * Update User
	 *
	 * @return object
	 *
	 * @SWG\Patch(
			tags={"users"},
			path="/users/{id}",
			summary="update user",
			description="Update the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="user", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/users/{id}", summary="alias", description="Alias of /users/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function update($id, $id2 = null)
	{
		# resources
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$user = $accountid?
		
			$this->getAccountRelUser ($accountid, $payload->id):
			User::find($payload->id);
			
		if (!$user)
			
			throw new ModelNotFoundException();
		
		# Update account
		$user->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($user->schema ($payload->display));
	}

    /**
     * Updates user password
     *
     * @return object
     *
     * @SWG\Patch(
			tags={"users"},
			path="/users/{id}/password",
			summary="update user password",
			description="Update the resource password defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="user", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/User")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/users/{id}/password", summary="alias", description="Alias of /users/id/password",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
    public function updatePassword($id, $id2 = null)
    {
        # resources
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updatePasswordRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$user = $accountid?
		
			$this->getAccountRelUser ($accountid, $payload->id):
			User::find($payload->id);
			
		if (!$user)
			
			throw new ModelNotFoundException();
		
		# Update account
		$user->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($user->schema ($payload->display));
    }

    /**
	 * Delete User
	 *
	 * @return boolean
	 * 
	 * @SWG\Delete(
			tags={"users"},
			path="/users/{id}",
			summary="delete user",
			description="Soft delete the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Response(response=200, ref="#/responses/success_integer"),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Delete(
			tags={"accounts"}, path="account/{accountId}/users/{id}", summary="alias", description="Alias of /users/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function destroy($id, $id2 = null)
	{
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$user = $accountid?
		
			$this->getAccountRelUser ($accountid, $payload->id):
			User::find($payload->id);
			
		if (!$user)
			
			throw new ModelNotFoundException();
		
		# Soft Delete
		$user->destroy((int) $payload->id);

		return response()->json(true);
	}
    
    /**
	 * User getter
	 * Helper function to assert Account related User
	 *
	 * @param	mixed	$accountid
	 * @param	mixed	$userid
	 */
	public function getAccountRelUser ($accountid, $userid)
	{
		$account = Account::find ((int) $accountid);
		
		if (!$account)
			
			throw new ModelNotFoundException();
		
		return $account->users()->find((int) $userid);	
	}
}
