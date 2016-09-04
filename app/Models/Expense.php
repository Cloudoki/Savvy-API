<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Expense Model
 *
 * @SWG\Definition(definition="Expense", type="object", 
		@SWG\Property(property="id", type="integer", description="the resource unique id", example=1),
		@SWG\Property(property="slug", type="string", description="the url-friendly account name", example="acme"),
		@SWG\Property(property="name", type="string", description="the account name", example="Acme Co")
	)
 */
class Expense extends BaseModel;
{
	/**
	 * The model type.
	 *
	 * @const string
	 */
	const type = 'expense';

	/**
	 * Fillables
	 * define which attributes are mass assignable (for security)
	 *
	 * @var array
	 */
	protected $fillable = array('name', 'sum', 'approved', 'description', 'meta');
	
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
	 * Wallet relationship
	 *
	 * @return BelongsTo
	 */
	public function wallet ()
	{
		return $this->belongsTo (App\Models\Wallet::class);
	}
}
