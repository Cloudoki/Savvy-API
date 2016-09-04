<?php

namespace App\Http\Controllers;

use Cloudoki\OaStack\Models\Team;
use Cloudoki\OaStack\Models\Account;
use Cloudoki\OaStack\OAuth2Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cloudoki\Guardian\Guardian;

/**
 * Teams Controller
 * The teams controller uses the Laravel RESTful Resource Controller method.
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
		name="teams",
		description="the Team resources"
	)
 */
class TeamController extends BaseController
{
    const type = 'team';
    
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
     *  Get Teams
     *
     *  @return array
     *
     * @SWG\Get(
			tags={"teams"},
			path="/teams",
			summary="global list of teams (limited access)",
			description="Returns a list of all teams, superadmin access only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Team"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
	    @SWG\Get(
			tags={"teams"},
			path="/accounts/{accountId}/teams",
			summary="account accessible list of teams",
			description="Returns a list of the teams accessible by the account",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Team"))),
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
			else $list = $account->teams;
		}
        else
        	$list = Team::orderBy('id')->get();
		
		
		# return all (account) teams
		return response()->json ($list->schema($payload->display));
    }
    
    /**
     * Get Me
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"teams"},
			path="/me",
			summary="related team",
			description="Returns the requesting Team resource, identified by the auth process",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=401, ref="#/responses/default"),
		)
	 */
    public function me ()
    {
		# Validation
		$payload = $this->validation ([],[]);
		
		$team = Guardian::team ();
		
		if (!$team)
			
			throw new ModelNotFoundException();
		
        # Return Team
		return response()->json($team->schema ($payload->display));
    }
    
    /**
     * Get Team
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"teams"},
			path="/teams/{id}",
			summary="single team",
			description="Returns a single Team resource, identified by ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised"),
			@SWG\Response(response=404, ref="#/responses/not_found")
		)
		
		@SWG\Get(
			tags={"teams"}, path="accounts/{accountId}/teams/{id}", summary="alias", description="Alias of /teams/id",
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
		
		$team = $accountid?
		
			$this->getAccountRelTeam ($accountid, $payload->id):
			Team::find($payload->id);
			
		if (!$team)
			
			throw new ModelNotFoundException();
		

		# Return Account
		return response()->json($team->schema ($payload->display));
    }
    
    

    /**
	 * Post Team
	 *
	 * @return object
	 *
	 * @SWG\Post(
			tags={"teams"},
			path="/teams",
			summary="create team (limited access)",
			description="Create a new floating Team resource, for superadmin only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Post(
			tags={"teams"},
			path="/accounts/{accountId}/teams",
			summary="create team",
			description="Create a new Team resource",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
	 */
	public function store($id = null)
	{
		$payload = $this->validation (['id'=>  $id], self::$postRules);

		# Validate
		Guardian::check ($id? (int) $id: null);
		
		# Existing team
		$team = Team::where('email', $payload->email)->first();

		if (!$team)
		{
			# Save input
			$team = new Team;
			$team->schemaUpdate((array) $payload);
		}

		if ($payload->id)
		{
			$account = Account::find($payload->id);

			if (!$account)

				throw new AuthorizationException ('Not a valid account');

			$related = $team->accounts->find($payload->id);

			if ( ! $related)
			{
				$invitation_token = $team->makeToken();
				$account->teams()->attach($team->getId(), ['invitation_token' => $invitation_token]);
			}
			else
			{
				$invitation_token = $related->getInvitationToken();
			}

			# Send invitation
			OAuth2Controller::invite($team, $account, $invitation_token);
		}


		# Return Account
		return response()->json($team->schema($payload->display));
	}

    /**
	 * Update Team
	 *
	 * @return object
	 *
	 * @SWG\Patch(
			tags={"teams"},
			path="/teams/{id}",
			summary="update team",
			description="Update the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="team", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/teams/{id}", summary="alias", description="Alias of /teams/id",
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
		
		$team = $accountid?
		
			$this->getAccountRelTeam ($accountid, $payload->id):
			Team::find($payload->id);
			
		if (!$team)
			
			throw new ModelNotFoundException();
		
		# Update account
		$team->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($team->schema ($payload->display));
	}

    /**
     * Updates team password
     *
     * @return object
     *
     * @SWG\Patch(
			tags={"teams"},
			path="/teams/{id}/password",
			summary="update team password",
			description="Update the resource password defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="team", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Team")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/teams/{id}/password", summary="alias", description="Alias of /teams/id/password",
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
		
		$team = $accountid?
		
			$this->getAccountRelTeam ($accountid, $payload->id):
			Team::find($payload->id);
			
		if (!$team)
			
			throw new ModelNotFoundException();
		
		# Update account
		$team->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($team->schema ($payload->display));
    }

    /**
	 * Delete Team
	 *
	 * @return boolean
	 * 
	 * @SWG\Delete(
			tags={"teams"},
			path="/teams/{id}",
			summary="delete team",
			description="Soft delete the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Response(response=200, ref="#/responses/success_integer"),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Delete(
			tags={"accounts"}, path="account/{accountId}/teams/{id}", summary="alias", description="Alias of /teams/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function destroy($id, $id2 = null)
	{
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$team = $accountid?
		
			$this->getAccountRelTeam ($accountid, $payload->id):
			Team::find($payload->id);
			
		if (!$team)
			
			throw new ModelNotFoundException();
		
		# Soft Delete
		$team->destroy((int) $payload->id);

		return response()->json(true);
	}
    
    /**
	 * Team getter
	 * Helper function to assert Account related Team
	 *
	 * @param	mixed	$accountid
	 * @param	mixed	$teamid
	 */
	public function getAccountRelTeam ($accountid, $teamid)
	{
		$account = Account::find ((int) $accountid);
		
		if (!$account)
			
			throw new ModelNotFoundException();
		
		return $account->teams()->find((int) $teamid);	
	}
}
