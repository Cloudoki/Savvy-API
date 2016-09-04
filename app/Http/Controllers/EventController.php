<?php

namespace App\Http\Controllers;

use Cloudoki\OaStack\Models\Event;
use Cloudoki\OaStack\Models\Account;
use Cloudoki\OaStack\OAuth2Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cloudoki\Guardian\Guardian;

/**
 * Events Controller
 * The events controller uses the Laravel RESTful Resource Controller method.
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
		name="events",
		description="the Event resources"
	)
 */
class EventController extends BaseController
{
    const type = 'event';
    
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
     *  Get Events
     *
     *  @return array
     *
     * @SWG\Get(
			tags={"events"},
			path="/events",
			summary="global list of events (limited access)",
			description="Returns a list of all events, superadmin access only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Event"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
	    @SWG\Get(
			tags={"events"},
			path="/accounts/{accountId}/events",
			summary="account accessible list of events",
			description="Returns a list of the events accessible by the account",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Event"))),
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
			else $list = $account->events;
		}
        else
        	$list = Event::orderBy('id')->get();
		
		
		# return all (account) events
		return response()->json ($list->schema($payload->display));
    }
    
    /**
     * Get Me
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"events"},
			path="/me",
			summary="related event",
			description="Returns the requesting Event resource, identified by the auth process",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=401, ref="#/responses/default"),
		)
	 */
    public function me ()
    {
		# Validation
		$payload = $this->validation ([],[]);
		
		$event = Guardian::event ();
		
		if (!$event)
			
			throw new ModelNotFoundException();
		
        # Return Event
		return response()->json($event->schema ($payload->display));
    }
    
    /**
     * Get Event
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"events"},
			path="/events/{id}",
			summary="single event",
			description="Returns a single Event resource, identified by ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised"),
			@SWG\Response(response=404, ref="#/responses/not_found")
		)
		
		@SWG\Get(
			tags={"events"}, path="accounts/{accountId}/events/{id}", summary="alias", description="Alias of /events/id",
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
		
		$event = $accountid?
		
			$this->getAccountRelEvent ($accountid, $payload->id):
			Event::find($payload->id);
			
		if (!$event)
			
			throw new ModelNotFoundException();
		

		# Return Account
		return response()->json($event->schema ($payload->display));
    }
    
    

    /**
	 * Post Event
	 *
	 * @return object
	 *
	 * @SWG\Post(
			tags={"events"},
			path="/events",
			summary="create event (limited access)",
			description="Create a new floating Event resource, for superadmin only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Post(
			tags={"events"},
			path="/accounts/{accountId}/events",
			summary="create event",
			description="Create a new Event resource",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
	 */
	public function store($id = null)
	{
		$payload = $this->validation (['id'=>  $id], self::$postRules);

		# Validate
		Guardian::check ($id? (int) $id: null);
		
		# Existing event
		$event = Event::where('email', $payload->email)->first();

		if (!$event)
		{
			# Save input
			$event = new Event;
			$event->schemaUpdate((array) $payload);
		}

		if ($payload->id)
		{
			$account = Account::find($payload->id);

			if (!$account)

				throw new AuthorizationException ('Not a valid account');

			$related = $event->accounts->find($payload->id);

			if ( ! $related)
			{
				$invitation_token = $event->makeToken();
				$account->events()->attach($event->getId(), ['invitation_token' => $invitation_token]);
			}
			else
			{
				$invitation_token = $related->getInvitationToken();
			}

			# Send invitation
			OAuth2Controller::invite($event, $account, $invitation_token);
		}


		# Return Account
		return response()->json($event->schema($payload->display));
	}

    /**
	 * Update Event
	 *
	 * @return object
	 *
	 * @SWG\Patch(
			tags={"events"},
			path="/events/{id}",
			summary="update event",
			description="Update the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="event", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/events/{id}", summary="alias", description="Alias of /events/id",
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
		
		$event = $accountid?
		
			$this->getAccountRelEvent ($accountid, $payload->id):
			Event::find($payload->id);
			
		if (!$event)
			
			throw new ModelNotFoundException();
		
		# Update account
		$event->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($event->schema ($payload->display));
	}

    /**
     * Updates event password
     *
     * @return object
     *
     * @SWG\Patch(
			tags={"events"},
			path="/events/{id}/password",
			summary="update event password",
			description="Update the resource password defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="event", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Event")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/events/{id}/password", summary="alias", description="Alias of /events/id/password",
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
		
		$event = $accountid?
		
			$this->getAccountRelEvent ($accountid, $payload->id):
			Event::find($payload->id);
			
		if (!$event)
			
			throw new ModelNotFoundException();
		
		# Update account
		$event->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($event->schema ($payload->display));
    }

    /**
	 * Delete Event
	 *
	 * @return boolean
	 * 
	 * @SWG\Delete(
			tags={"events"},
			path="/events/{id}",
			summary="delete event",
			description="Soft delete the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Response(response=200, ref="#/responses/success_integer"),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Delete(
			tags={"accounts"}, path="account/{accountId}/events/{id}", summary="alias", description="Alias of /events/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function destroy($id, $id2 = null)
	{
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$event = $accountid?
		
			$this->getAccountRelEvent ($accountid, $payload->id):
			Event::find($payload->id);
			
		if (!$event)
			
			throw new ModelNotFoundException();
		
		# Soft Delete
		$event->destroy((int) $payload->id);

		return response()->json(true);
	}
    
    /**
	 * Event getter
	 * Helper function to assert Account related Event
	 *
	 * @param	mixed	$accountid
	 * @param	mixed	$eventid
	 */
	public function getAccountRelEvent ($accountid, $eventid)
	{
		$account = Account::find ((int) $accountid);
		
		if (!$account)
			
			throw new ModelNotFoundException();
		
		return $account->events()->find((int) $eventid);	
	}
}
