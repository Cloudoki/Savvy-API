<?php

namespace App\Models;

use Cloudoki\OaStack\Models\User as OaUser;

/**
 * User Model
 *
 * @SWG\Definition(definition="User", type="object", 
		@SWG\Property(property="id", type="integer", description="the resource unique id", example=1),
		@SWG\Property(property="email", type="string", description="the resource e-mail address", example="zen@cloudoki.com"),
		@SWG\Property(property="firstname", type="string", description="the resource first name", example="Zen"),
		@SWG\Property(property="lastname", type="string", description="the resource last name", example="Bot"),
		@SWG\Property(property="password", type="string", description="hashed password string"),
		@SWG\Property(property="avatar", type="string", description="absolute uri to resource avatar image")
	)
 */
class User extends OaUser
{
	/**
	 * Active account
	 *
	 * @return Account
	 */
	public function account ()
	{
		return $this->accounts()->wherePivot('selected', 1)->first();
	}
	
	/**
	 * Accounts relationship
	 *
	 * @return BelongsToMany
	 */
	public function accounts ()
	{
		return $this->belongsToMany (Account::class)->withPivot ('invitation_token');
	}
	
	/**
	 * Teams relationship
	 *
	 * @return HasMany
	 */
	public function teams ()
	{
		return $this->belongsToMany (Team::class)->withPivot ('start_date', 'end_date', 'primary');
	}
	
	/**
	 * Wallets relationship
	 *
	 * @return HasMany
	 */
	public function wallets ()
	{
		return $this->hasMany (Wallet::class);
	}
}
