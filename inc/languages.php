<?php

/*
* LANGUAGES
*/


/*
* try to autodetect the current language from browser and match with active languages, or fallback to the default one
*/
function init_language()
{
	// if language is set by URL (eg. /en/)
	if( !empty( $_GET['lang'] ) )
	{
		set_language( $_GET['lang'] );

	// if language is not set, auto-detect it
	} elseif( !isset( $_COOKIE['ok_lang_code'] )) {
		set_language( autodetect_language() );
	}
	
	// set locale to current language
	setlocale(LC_TIME, get_language_code() );
}


/*
* autodetect the language
*/
function autodetect_language()
{
	$languages = get_languages();
	
	if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	{
		foreach($languages as $language)
		{
			$code = strtoupper( $language->code );
			$shortcode = substr( $code, 0, 2 );
			$country = substr( $code, -2 );
			if( strpos( strtoupper( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ), $code ) !== false
				|| strpos( strtoupper( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ), $shortcode ) !== false
				|| strpos( strtoupper( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ), $country ) !== false
				) return $language->code;
		}
	}

	return get_option( 'default_language', 'en_EN', '*' );
}


/*
* set the current language
*/
function set_language( $code )
{
	// expiration: 60 days
	if( !headers_sent() )
		setcookie( "ok_lang_code", $code, time() + 5184000 );
	
	$_COOKIE['ok_lang_code'] = $code;
}


/*
* get the list of all the languages
* arguments: orderby, status [ publish | pending ]
*/
function get_languages( $args=[] )
{
	if( empty( $args['orderby'] ) ) $args['orderby'] = "order";
	
	$languages = get_option( 'languages', false, '*' );
	
	$languages = unserialize( $languages );
	
	$output = [];
	
	// exclusion based on status
	foreach( $languages as $order => $language )
	{
		if( empty($args['status']) || $language->status == $args['status'] )
			$output[] = $language;
	}

	// sorting
	// orderby = order is already done by previuos foreach
	// sort only in case of language ordering
	if( $args['orderby'] == "language" )
	{
		if( !function_exists( "_lang_sort" ) )
		{
			function _lang_sort( $a, $b)
			{
				return $a->language <=> $b->language;
			}
		}
		
		usort( $output, "_lang_sort");
	}
	
	return $output;
}


/*
* get the current language or, if specified a code, get the requested language
*/
function get_language( $code = null )
{
	// to do
	if( empty( $code ) )
		return $code;
}


/*
* get the current language code or, as fallback, the default language
*/
function get_language_code()
{
	if( !empty( $_COOKIE['ok_lang_code'] ) ) return $_COOKIE['ok_lang_code'];
	
	$default_language = get_option( 'default_language', 'en_EN', '*' );
	return $default_language;
}



/*
* add a new language to the languages list
* mandatary input vars: language, code, status [ publish | pending ]
*/
function insert_language( $args )
{
	if( empty( $args['language'] ) ) return false;
	if( empty( $args['code'] ) ) return false;
	if( empty( $args['status'] ) ) $args['status'] = "pending";
	if( empty( $args['default'] ) ) $args['default'] = false;
	
	$languages = get_languages();

	// check if language already exists
	foreach( $languages as $l )
	{
		if( $l->code == $args['code'] ) return false;
	}
	
	$new_language = new stdClass();
	$new_language->language = $args['language'];
	$new_language->code = $args['code'];
	$new_language->status = $args['status'];

	$languages[] = $new_language;
	
	return update_languages( $languages );
}


/*
* update languages option row in db
*/
function update_languages( $languages )
{
	// check syntax
	if( !is_array( $languages ) )
	{
		trigger_error( "You must provide an array" );
		return false;
	}
	
	foreach( $languages as $order => $language )
	{
		if( !is_numeric( $order ) )
		{
			trigger_error( "Language array keys must be numeric" );
			return false;
		}
		
		if( !is_object( $language ) )
		{
			$language = (object)$language;
		}
		
		if( empty( $language->language ) )
		{
			trigger_error( "Language name not provided" );
			return false;
		}
		
		if( empty( $language->code ) )
		{
			trigger_error( "Language code not provided" );
			return false;
		}
		
		if( empty( $language->status ) || ( $language->status != "publish" && $language->status != "pending" ) )
		{
			trigger_error( "Language status must be publish or pending" );
			return false;
		}
		
		$languages[ $order ] = $language;
	}
	
	update_option( "languages", serialize( $languages ), "*" );
}

