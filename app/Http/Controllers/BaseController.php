<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;

/**
 * Class BaseController
 *
 * @package App\Http\Controllers
 */
class BaseController extends Controller
{
    /**
     * Default schema
     */
    const display = 'basic';

    /**
     * Default validation rules
     */
    protected $baseValidationRules = [
        'display' => 'min:4',
        'ids'     => 'regex:[\d,]',
		'q'		  => ''
    ];
	
    /**
     *  Request object
     */
    var $request;


    /**
     * BaseController construct
     * MQ preps
     */
    public function __construct (Request $request)
    {
        $this->request = $request;
    }

    /**
     * Provide all required parameters to Input
     * Returns all input attibutes in array
     *
     * @return array
     */
    protected function prepInput ($attr)
    {
        // Add display fallback
        $attr['display'] = $this->request->input ('display', self::display);

        return array_merge ($this->request->all(), $attr);
    }
    
    /**
     *  REST Validation
     *  Allowed parameters intersect with validation
     *
     *  @return object
     */
    public function validation ($input = [], $rules = [])
    {
        # Extend rules
        $rules = array_merge ($this->baseValidationRules, $rules);

        # Validation
        return (object) array_intersect_key ($this->validate ($input, $rules), $rules);
    }

    /**
     * Validate Input
     * Returns Laravel Validator object
     *
     * @throws Exception
     */
    public function validate ($input, $rules = [])
    {
        // Add path attributes
        $input = $this->prepInput ($input);


        // Perform validation
        $validator = Validator::make ($input, $rules);


        // Check if the validator failed
        if ($validator->fails ())
        
            throw new \Cloudoki\InvalidParameterException( 'Parameters validation failed!', $validator->messages()->all ());

        // return all input
        return $input;
    }
    
    /**
     * Dispatch
     * The basic controller action between API and Worker
     *
     * @return mixed response
     */
	public static function jobdispatch($job, $jobload)
	{
		# Add general data
		$jobload->access_token = config ('app.access_token');

		# Response
		$response = app()->frontqueue->request($job, $jobload);
		
		if (isset ($response->error)) 
			
			return response ($response->error, $response->code);

		# Frontqueue call
		return response()->json
		(
			$response
		);
	}
    
    /**
     *  REST Dispatch
     *  Jobdispatch extension with validation
     *
     *  @return Job response
     */
    public function restDispatch ($method, $controller, $input = [], $rules = [])
    {
        # Extend rules
        $rules = array_merge ($this->baseValidationRules, $rules);

        # Validation
        $payload = array_intersect_key ($this->validate ($input, $rules), $rules);

        # Request Foreground Job
        return self::jobdispatch ( 'controllerDispatch', (object)
        [
            'action'=>      $method,
            'controller'=>  $controller,
            'payload'=>     $payload
        ]);
    }
    
    /**
	 * Ids Filter
	 * Filter resources by id list
	 *
	 * @param 	mixed	$model_base
	 * @param	string	$ids
	 * @param 	string	$plural
	 * @return	response
	 */
	public function filterByIds ($model_base, $ids, $plural = null)
	{
		return $model_base->{$plural?: $this::type . 's'}()->find (explode (',', $ids));
	}
	
	/**
	 * Search Filter
	 * Filter resources by set of searchable fields
	 *
	 * @param 	mixed	$model_base
	 * @param	string	$query
	 * @param	array	$fields
	 * @param 	string	$plural
	 * @return	response
	 */
	public function search ($model_base, $query, $fields = ['name'], $plural = null)
	{
		return $model_base->{$plural?: $this::type . 's'}()->where(
		
			function ($q) use ($query, $fields)
			{
				foreach ($fields as $field)
				
					$q->orWhere($field, 'like', '%' . str_replace (' ', '', $query) .'%');
	
			})->get ();
	}
}

