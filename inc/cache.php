<?php

/*
* MEMORY CACHE SYSTEM
* it's used to safety store results and variables into memory
*/

class ok_cache
{
	protected $cache;
	
	public function __construct()
	{
		$this->cache = [];
	}
	
	public function set( $param, $value, $lang = null )
	{
		if( empty( $lang ) && $param != 'default_language' ) $lang = get_language_code();
		
		$this->cache[ $lang . '-' . $param ] = $value;
	}
	
	public function unset( $param, $value, $lang = null )
	{
		if( empty( $lang ) && $param != 'default_language' ) $lang = get_language_code();
		
		unset( $this->cache[ $lang . '-' . $param ] );
	}
	
	public function get( $param, $lang = null )
	{
		if( empty( $lang ) && $param != 'default_language' ) $lang = get_language_code();
		
		if( isset( $this->cache[ $lang . '-' . $param ] ) )
			return $this->cache[ $lang . '-' . $param ];

		// fallback on * lang
		if( isset( $this->cache[ '*-' . $param ] ) )
			return $this->cache[ '*-' . $param ];
		
		return null;
	}
	
}