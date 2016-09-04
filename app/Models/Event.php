<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Event Model
 *
 * @SWG\Definition(definition="Event", type="object", 
		@SWG\Property(property="id", type="integer", description="the resource unique id", example=1),
		@SWG\Property(property="slug", type="string", description="the url-friendly account name", example="acme"),
		@SWG\Property(property="name", type="string", description="the account name", example="Acme Co")
	)
 */
class Event extends BaseModel;
{
	/**
	 * The model type.
	 *
	 * @const string
	 */
	const type = 'event';

	/**
	 * Fillables
	 * define which attributes are mass assignable (for security)
	 *
	 * @var array
	 */
	protected $fillable = array('name', 'start_date', 'duration');
}
