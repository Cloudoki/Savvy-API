<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Wallet Model
 *
 * @SWG\Definition(definition="Wallet", type="object", 
		@SWG\Property(property="id", type="integer", description="the resource unique id", example=1),
		@SWG\Property(property="slug", type="string", description="the url-friendly account name", example="acme"),
		@SWG\Property(property="name", type="string", description="the account name", example="Acme Co")
	)
 */
class Wallet extends BaseModel
{
	/**
	 * The model type.
	 *
	 * @const string
	 */
	const type = 'wallet';

	/**
	 * Fillables
	 * define which attributes are mass assignable (for security)
	 *
	 * @var array
	 */
	protected $fillable = array('name', 'cumul', 'total', 'description', 'meta');
	
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
	
	/**
	 * Get related user
	 *
	 * @return	string
	 */
	public function getUser ()
	{
		return $this->user;
	}
	
	/**
	 * Get model name
	 *
	 * @return	string
	 */
	public function getName ()
	{
		return $this->name;
	}

	/**
	 * Set model name
	 *
	 * @param	string	$name
	 */
	public function setName ($name)
	{
		$this->name = $name;

		return $this;
	}
	
	/**
	 * Get model cumulative
	 *
	 * @return	string
	 */
	public function getCumulative ()
	{
		return $this->cumul;
	}

	/**
	 * Set model cumulative
	 *
	 * @param	string	$value
	 */
	public function setCumulative ($value)
	{
		$this->cumul = $value;

		return $this;
	}
	
	/**
	 * Get model total
	 *
	 * @return	string
	 */
	public function getTotal ()
	{
		return $this->total;
	}

	/**
	 * Set model cumulative
	 *
	 * @param	string	$value
	 */
	public function setTotal ($value)
	{
		$this->total = $value;

		return $this;
	}
}
