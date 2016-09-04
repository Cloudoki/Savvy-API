<?php

namespace App\Http\Controllers;

use Cloudoki\OaStack\Models\Expense;
use Cloudoki\OaStack\Models\Account;
use Cloudoki\OaStack\OAuth2Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cloudoki\Guardian\Guardian;

/**
 * Expenses Controller
 * The expenses controller uses the Laravel RESTful Resource Controller method.
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
		name="expenses",
		description="the Expense resources"
	)
 */
class ExpenseController extends BaseController
{
    const type = 'expense';
    
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
     *  Get Expenses
     *
     *  @return array
     *
     * @SWG\Get(
			tags={"expenses"},
			path="/expenses",
			summary="global list of expenses (limited access)",
			description="Returns a list of all expenses, superadmin access only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Expense"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
	    @SWG\Get(
			tags={"expenses"},
			path="/accounts/{accountId}/expenses",
			summary="account accessible list of expenses",
			description="Returns a list of the expenses accessible by the account",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Expense"))),
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
			else $list = $account->expenses;
		}
        else
        	$list = Expense::orderBy('id')->get();
		
		
		# return all (account) expenses
		return response()->json ($list->schema($payload->display));
    }
    
    /**
     * Get Me
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"expenses"},
			path="/me",
			summary="related expense",
			description="Returns the requesting Expense resource, identified by the auth process",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=401, ref="#/responses/default"),
		)
	 */
    public function me ()
    {
		# Validation
		$payload = $this->validation ([],[]);
		
		$expense = Guardian::expense ();
		
		if (!$expense)
			
			throw new ModelNotFoundException();
		
        # Return Expense
		return response()->json($expense->schema ($payload->display));
    }
    
    /**
     * Get Expense
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"expenses"},
			path="/expenses/{id}",
			summary="single expense",
			description="Returns a single Expense resource, identified by ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised"),
			@SWG\Response(response=404, ref="#/responses/not_found")
		)
		
		@SWG\Get(
			tags={"expenses"}, path="accounts/{accountId}/expenses/{id}", summary="alias", description="Alias of /expenses/id",
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
		
		$expense = $accountid?
		
			$this->getAccountRelExpense ($accountid, $payload->id):
			Expense::find($payload->id);
			
		if (!$expense)
			
			throw new ModelNotFoundException();
		

		# Return Account
		return response()->json($expense->schema ($payload->display));
    }
    
    

    /**
	 * Post Expense
	 *
	 * @return object
	 *
	 * @SWG\Post(
			tags={"expenses"},
			path="/expenses",
			summary="create expense (limited access)",
			description="Create a new floating Expense resource, for superadmin only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Post(
			tags={"expenses"},
			path="/accounts/{accountId}/expenses",
			summary="create expense",
			description="Create a new Expense resource",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
	 */
	public function store($id = null)
	{
		$payload = $this->validation (['id'=>  $id], self::$postRules);

		# Validate
		Guardian::check ($id? (int) $id: null);
		
		# Existing expense
		$expense = Expense::where('email', $payload->email)->first();

		if (!$expense)
		{
			# Save input
			$expense = new Expense;
			$expense->schemaUpdate((array) $payload);
		}

		if ($payload->id)
		{
			$account = Account::find($payload->id);

			if (!$account)

				throw new AuthorizationException ('Not a valid account');

			$related = $expense->accounts->find($payload->id);

			if ( ! $related)
			{
				$invitation_token = $expense->makeToken();
				$account->expenses()->attach($expense->getId(), ['invitation_token' => $invitation_token]);
			}
			else
			{
				$invitation_token = $related->getInvitationToken();
			}

			# Send invitation
			OAuth2Controller::invite($expense, $account, $invitation_token);
		}


		# Return Account
		return response()->json($expense->schema($payload->display));
	}

    /**
	 * Update Expense
	 *
	 * @return object
	 *
	 * @SWG\Patch(
			tags={"expenses"},
			path="/expenses/{id}",
			summary="update expense",
			description="Update the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="expense", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/expenses/{id}", summary="alias", description="Alias of /expenses/id",
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
		
		$expense = $accountid?
		
			$this->getAccountRelExpense ($accountid, $payload->id):
			Expense::find($payload->id);
			
		if (!$expense)
			
			throw new ModelNotFoundException();
		
		# Update account
		$expense->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($expense->schema ($payload->display));
	}

    /**
     * Updates expense password
     *
     * @return object
     *
     * @SWG\Patch(
			tags={"expenses"},
			path="/expenses/{id}/password",
			summary="update expense password",
			description="Update the resource password defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="expense", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Expense")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/expenses/{id}/password", summary="alias", description="Alias of /expenses/id/password",
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
		
		$expense = $accountid?
		
			$this->getAccountRelExpense ($accountid, $payload->id):
			Expense::find($payload->id);
			
		if (!$expense)
			
			throw new ModelNotFoundException();
		
		# Update account
		$expense->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($expense->schema ($payload->display));
    }

    /**
	 * Delete Expense
	 *
	 * @return boolean
	 * 
	 * @SWG\Delete(
			tags={"expenses"},
			path="/expenses/{id}",
			summary="delete expense",
			description="Soft delete the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Response(response=200, ref="#/responses/success_integer"),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Delete(
			tags={"accounts"}, path="account/{accountId}/expenses/{id}", summary="alias", description="Alias of /expenses/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function destroy($id, $id2 = null)
	{
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$expense = $accountid?
		
			$this->getAccountRelExpense ($accountid, $payload->id):
			Expense::find($payload->id);
			
		if (!$expense)
			
			throw new ModelNotFoundException();
		
		# Soft Delete
		$expense->destroy((int) $payload->id);

		return response()->json(true);
	}
    
    /**
	 * Expense getter
	 * Helper function to assert Account related Expense
	 *
	 * @param	mixed	$accountid
	 * @param	mixed	$expenseid
	 */
	public function getAccountRelExpense ($accountid, $expenseid)
	{
		$account = Account::find ((int) $accountid);
		
		if (!$account)
			
			throw new ModelNotFoundException();
		
		return $account->expenses()->find((int) $expenseid);	
	}
}
