<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Wallet;

use Cloudoki\Guardian\Guardian;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


/**
 * Wallets Controller
 * The wallets controller uses the Laravel RESTful Resource Controller method.
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
		name="wallets",
		description="the Wallet resources"
	)
 */
class WalletController extends BaseController
{
    const type = 'wallet';
    
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
     *  Get Wallets
     *
     *  @return array
     *
     * @SWG\Get(
			tags={"wallets"},
			path="/wallets",
			summary="global list of wallets (limited access)",
			description="Returns a list of all wallets, superadmin access only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Wallet"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
	    @SWG\Get(
			tags={"wallets"},
			path="/teams/{teamId}/wallets",
			summary="list of team wallets",
			description="Returns a list of the wallets accessible by the team",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Wallet"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		 @SWG\Get(
			tags={"wallets"},
			path="/users/{userId}/wallets",
			summary="list of wallets from user",
			description="Returns a list of the wallets accessible by the user",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Wallet"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
     */
    public function index(Request $request, $id = null)
    {
		# Validate
		Guardian::check ();
		
		$payload = $this->validation ();
		$me		 = User::find (Guardian::userId ());
		$account = $me->account ();
		
		# Abstract call
		if (!$id)
			
			$list = $me->wallets;
		
		# Team related
		else if ($request->is ('*/teams/*'))
		{
			if (!$account->teams ()->where('id', $id)->count ())
				
				throw new AuthorizationException ();
			
			$list = Team::find ($id)->wallets;
		}
		
		# User related
		else if ($request->is ('*/users/*'))
		{
			$user = $account->users()->find($id);
			
			if (!$user)
				throw new AuthorizationException ();
			
			$list = $user->wallets;
		}
		
		# return the wallets
		return response()->json ($list? $list->schema($payload->display): []);
    }
    
    /**
     * Get Me
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"wallets"},
			path="/me",
			summary="related wallet",
			description="Returns the requesting Wallet resource, identified by the auth process",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=401, ref="#/responses/default"),
		)
	 */
    public function me ()
    {
		# Validation
		$payload = $this->validation ([],[]);
		
		$wallet = Guardian::wallet ();
		
		if (!$wallet)
			
			throw new ModelNotFoundException();
		
        # Return Wallet
		return response()->json($wallet->schema ($payload->display));
    }
    
    /**
     * Get Wallet
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"wallets"},
			path="/wallets/{id}",
			summary="single wallet",
			description="Returns a single Wallet resource, identified by ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised"),
			@SWG\Response(response=404, ref="#/responses/not_found")
		)
		
		@SWG\Get(
			tags={"wallets"}, path="accounts/{accountId}/wallets/{id}", summary="alias", description="Alias of /wallets/id",
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
		
		$wallet = $accountid?
		
			$this->getAccountRelWallet ($accountid, $payload->id):
			Wallet::find($payload->id);
			
		if (!$wallet)
			
			throw new ModelNotFoundException();
		

		# Return Account
		return response()->json($wallet->schema ($payload->display));
    }
    
    

    /**
	 * Post Wallet
	 *
	 * @return object
	 *
	 * @SWG\Post(
			tags={"wallets"},
			path="/wallets",
			summary="create wallet (limited access)",
			description="Create a new floating Wallet resource, for superadmin only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Post(
			tags={"wallets"},
			path="/accounts/{accountId}/wallets",
			summary="create wallet",
			description="Create a new Wallet resource",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
	 */
	public function store($id = null)
	{
		$payload = $this->validation (['id'=>  $id], self::$postRules);

		# Validate
		Guardian::check ($id? (int) $id: null);
		
		# Existing wallet
		$wallet = Wallet::where('email', $payload->email)->first();

		if (!$wallet)
		{
			# Save input
			$wallet = new Wallet;
			$wallet->schemaUpdate((array) $payload);
		}

		if ($payload->id)
		{
			$account = Account::find($payload->id);

			if (!$account)

				throw new AuthorizationException ('Not a valid account');

			$related = $wallet->accounts->find($payload->id);

			if ( ! $related)
			{
				$invitation_token = $wallet->makeToken();
				$account->wallets()->attach($wallet->getId(), ['invitation_token' => $invitation_token]);
			}
			else
			{
				$invitation_token = $related->getInvitationToken();
			}

			# Send invitation
			OAuth2Controller::invite($wallet, $account, $invitation_token);
		}


		# Return Account
		return response()->json($wallet->schema($payload->display));
	}

    /**
	 * Update Wallet
	 *
	 * @return object
	 *
	 * @SWG\Patch(
			tags={"wallets"},
			path="/wallets/{id}",
			summary="update wallet",
			description="Update the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="wallet", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/wallets/{id}", summary="alias", description="Alias of /wallets/id",
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
		
		$wallet = $accountid?
		
			$this->getAccountRelWallet ($accountid, $payload->id):
			Wallet::find($payload->id);
			
		if (!$wallet)
			
			throw new ModelNotFoundException();
		
		# Update account
		$wallet->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($wallet->schema ($payload->display));
	}

    /**
     * Updates wallet password
     *
     * @return object
     *
     * @SWG\Patch(
			tags={"wallets"},
			path="/wallets/{id}/password",
			summary="update wallet password",
			description="Update the resource password defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="wallet", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Wallet")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/wallets/{id}/password", summary="alias", description="Alias of /wallets/id/password",
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
		
		$wallet = $accountid?
		
			$this->getAccountRelWallet ($accountid, $payload->id):
			Wallet::find($payload->id);
			
		if (!$wallet)
			
			throw new ModelNotFoundException();
		
		# Update account
		$wallet->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($wallet->schema ($payload->display));
    }

    /**
	 * Delete Wallet
	 *
	 * @return boolean
	 * 
	 * @SWG\Delete(
			tags={"wallets"},
			path="/wallets/{id}",
			summary="delete wallet",
			description="Soft delete the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Response(response=200, ref="#/responses/success_integer"),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Delete(
			tags={"accounts"}, path="account/{accountId}/wallets/{id}", summary="alias", description="Alias of /wallets/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function destroy($id, $id2 = null)
	{
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$wallet = $accountid?
		
			$this->getAccountRelWallet ($accountid, $payload->id):
			Wallet::find($payload->id);
			
		if (!$wallet)
			
			throw new ModelNotFoundException();
		
		# Soft Delete
		$wallet->destroy((int) $payload->id);

		return response()->json(true);
	}
    
    /**
	 * Wallet getter
	 * Helper function to assert Account related Wallet
	 *
	 * @param	mixed	$accountid
	 * @param	mixed	$walletid
	 */
	public function getAccountRelWallet ($accountid, $walletid)
	{
		$account = Account::find ((int) $accountid);
		
		if (!$account)
			
			throw new ModelNotFoundException();
		
		return $account->wallets()->find((int) $walletid);	
	}
}
