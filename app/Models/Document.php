<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Document Model
 *
 * @SWG\Definition(definition="Document", type="object", 
		@SWG\Property(property="id", type="integer", description="the resource unique id", example=1),
		@SWG\Property(property="slug", type="string", description="the url-friendly account name", example="acme"),
		@SWG\Property(property="name", type="string", description="the account name", example="Acme Co")
	)
 */
class Document extends BaseModel;
{
	/**
	 * The model type.
	 *
	 * @const string
	 */
	const type = 'document';

	/**
	 * Fillables
	 * define which attributes are mass assignable (for security)
	 *
	 * @var array
	 */
	protected $fillable = array('name', 'description', 'meta');
	
	/**
	 * Expense relationship
	 *
	 * @return BelongsTo
	 */
	public function expense ()
	{
		return $this->belongsTo (App\Models\Expense::class);
	}
	
	/**
	 * Team relationship
	 *
	 * @return BelongsTo
	 */
	public function team ()
	{
		return $this->belongsTo (App\Models\Team::class);
	}
	
	/**
	 * User relationship
	 *
	 * @return BelongsTo
	 */
	public function user ()
	{
		return $this->belongsTo (App\Models\User::class);
	}
}
