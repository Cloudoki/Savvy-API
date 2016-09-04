<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Team Model
 *
 * @SWG\Definition(definition="Team", type="object", 
		@SWG\Property(property="id", type="integer", description="the resource unique id", example=1),
		@SWG\Property(property="slug", type="string", description="the url-friendly account name", example="acme"),
		@SWG\Property(property="name", type="string", description="the account name", example="Acme Co")
	)
 */
class Team extends BaseModel
{
	/**
	 * Teams relationship
	 *
	 * @return HasMany
	 */
	public function users ()
	{
		return $this->belongsToMany (User::class)->withPivot ('start_date', 'end_date', 'primary');
	}	
}
