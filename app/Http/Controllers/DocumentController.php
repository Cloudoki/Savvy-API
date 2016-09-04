<?php

namespace App\Http\Controllers;

use Cloudoki\OaStack\Models\Document;
use Cloudoki\OaStack\Models\Account;
use Cloudoki\OaStack\OAuth2Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cloudoki\Guardian\Guardian;

/**
 * Documents Controller
 * The documents controller uses the Laravel RESTful Resource Controller method.
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
		name="documents",
		description="the Document resources"
	)
 */
class DocumentController extends BaseController
{
    const type = 'document';
    
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
     *  Get Documents
     *
     *  @return array
     *
     * @SWG\Get(
			tags={"documents"},
			path="/documents",
			summary="global list of documents (limited access)",
			description="Returns a list of all documents, superadmin access only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Document"))),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
	    @SWG\Get(
			tags={"documents"},
			path="/accounts/{accountId}/documents",
			summary="account accessible list of documents",
			description="Returns a list of the documents accessible by the account",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_array", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Document"))),
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
			else $list = $account->documents;
		}
        else
        	$list = Document::orderBy('id')->get();
		
		
		# return all (account) documents
		return response()->json ($list->schema($payload->display));
    }
    
    /**
     * Get Me
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"documents"},
			path="/me",
			summary="related document",
			description="Returns the requesting Document resource, identified by the auth process",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=401, ref="#/responses/default"),
		)
	 */
    public function me ()
    {
		# Validation
		$payload = $this->validation ([],[]);
		
		$document = Guardian::document ();
		
		if (!$document)
			
			throw new ModelNotFoundException();
		
        # Return Document
		return response()->json($document->schema ($payload->display));
    }
    
    /**
     * Get Document
     *
     * @return object
	 *
	 * @SWG\Get(
			tags={"documents"},
			path="/documents/{id}",
			summary="single document",
			description="Returns a single Document resource, identified by ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised"),
			@SWG\Response(response=404, ref="#/responses/not_found")
		)
		
		@SWG\Get(
			tags={"documents"}, path="accounts/{accountId}/documents/{id}", summary="alias", description="Alias of /documents/id",
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
		
		$document = $accountid?
		
			$this->getAccountRelDocument ($accountid, $payload->id):
			Document::find($payload->id);
			
		if (!$document)
			
			throw new ModelNotFoundException();
		

		# Return Account
		return response()->json($document->schema ($payload->display));
    }
    
    

    /**
	 * Post Document
	 *
	 * @return object
	 *
	 * @SWG\Post(
			tags={"documents"},
			path="/documents",
			summary="create document (limited access)",
			description="Create a new floating Document resource, for superadmin only",
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Post(
			tags={"documents"},
			path="/accounts/{accountId}/documents",
			summary="create document",
			description="Create a new Document resource",
			@SWG\Parameter(ref="#/parameters/accountId"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="account", in="body", description="The resource object", required=true, @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
	 */
	public function store($id = null)
	{
		$payload = $this->validation (['id'=>  $id], self::$postRules);

		# Validate
		Guardian::check ($id? (int) $id: null);
		
		# Existing document
		$document = Document::where('email', $payload->email)->first();

		if (!$document)
		{
			# Save input
			$document = new Document;
			$document->schemaUpdate((array) $payload);
		}

		if ($payload->id)
		{
			$account = Account::find($payload->id);

			if (!$account)

				throw new AuthorizationException ('Not a valid account');

			$related = $document->accounts->find($payload->id);

			if ( ! $related)
			{
				$invitation_token = $document->makeToken();
				$account->documents()->attach($document->getId(), ['invitation_token' => $invitation_token]);
			}
			else
			{
				$invitation_token = $related->getInvitationToken();
			}

			# Send invitation
			OAuth2Controller::invite($document, $account, $invitation_token);
		}


		# Return Account
		return response()->json($document->schema($payload->display));
	}

    /**
	 * Update Document
	 *
	 * @return object
	 *
	 * @SWG\Patch(
			tags={"documents"},
			path="/documents/{id}",
			summary="update document",
			description="Update the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="document", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/documents/{id}", summary="alias", description="Alias of /documents/id",
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
		
		$document = $accountid?
		
			$this->getAccountRelDocument ($accountid, $payload->id):
			Document::find($payload->id);
			
		if (!$document)
			
			throw new ModelNotFoundException();
		
		# Update account
		$document->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($document->schema ($payload->display));
	}

    /**
     * Updates document password
     *
     * @return object
     *
     * @SWG\Patch(
			tags={"documents"},
			path="/documents/{id}/password",
			summary="update document password",
			description="Update the resource password defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Parameter(ref="#/parameters/display"),
			@SWG\Parameter(name="document", in="body", description="The resource object (not all field are required)", required=true, @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=200, ref="#/responses/success_object", @SWG\Schema(ref="#/definitions/Document")),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Patch(
			tags={"accounts"}, path="accounts/{accountId}/documents/{id}/password", summary="alias", description="Alias of /documents/id/password",
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
		
		$document = $accountid?
		
			$this->getAccountRelDocument ($accountid, $payload->id):
			Document::find($payload->id);
			
		if (!$document)
			
			throw new ModelNotFoundException();
		
		# Update account
		$document->schemaUpdate ((array) $payload);
		
		
		# Return Account
		return response()->json($document->schema ($payload->display));
    }

    /**
	 * Delete Document
	 *
	 * @return boolean
	 * 
	 * @SWG\Delete(
			tags={"documents"},
			path="/documents/{id}",
			summary="delete document",
			description="Soft delete the resource defined by its ID",
			@SWG\Parameter(ref="#/parameters/id"),
			@SWG\Response(response=200, ref="#/responses/success_integer"),
			@SWG\Response(response=401, ref="#/responses/default"),
			@SWG\Response(response=403, ref="#/responses/not_authorised")
		)
		
		@SWG\Delete(
			tags={"accounts"}, path="account/{accountId}/documents/{id}", summary="alias", description="Alias of /documents/id",
			@SWG\Response(response=200, ref="#/responses/success_alias")
		)
	 */
	public function destroy($id, $id2 = null)
	{
		$payload = $this->validation (['id'=>  $id2?: $id], self::$updateRules);
		$accountid = $id2? (int) $id: null;

		# Validate
		Guardian::check ($accountid);
		
		$document = $accountid?
		
			$this->getAccountRelDocument ($accountid, $payload->id):
			Document::find($payload->id);
			
		if (!$document)
			
			throw new ModelNotFoundException();
		
		# Soft Delete
		$document->destroy((int) $payload->id);

		return response()->json(true);
	}
    
    /**
	 * Document getter
	 * Helper function to assert Account related Document
	 *
	 * @param	mixed	$accountid
	 * @param	mixed	$documentid
	 */
	public function getAccountRelDocument ($accountid, $documentid)
	{
		$account = Account::find ((int) $accountid);
		
		if (!$account)
			
			throw new ModelNotFoundException();
		
		return $account->documents()->find((int) $documentid);	
	}
}
