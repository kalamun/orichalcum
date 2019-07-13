<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

ok_enqueue_script( 'languages', 'js/languages.js' );

add_action( 'ok_ajax_do_ajax', 'languages_process_ajax' );

function languages_process_ajax()
{
	$response = "";
	
	switch($_REQUEST['fn'])
	{

		/* import user media with progress bar */
		case 'print-language':
			$response = print_language( $_REQUEST );
			break;
		
		/* save language modifications */
		case 'save-languages':
			$response = save_languages( $_REQUEST );
			break;
			
		default:
			$response = 'No function specified.';
			break;
	}

	print_clean_errors();

	if(is_array($response) || is_object($response))
	{
		$response = json_encode($response);
		print_r($response);  
		
	} else {
		echo $response;
		
	}
	die;
}



/*
* called to print out any table line in the language table
* args:
* - language (name)
* - code
* - shortcode
* - (boolean) active
* - (boolean) default
*/
function print_language( $args )
{
	
	print_r( $arg );
	echo 'okokokokok';
	
}


/*
* called to save the languages
* accept an array of languages with:
* - language (name)
* - code
* - shortcode
* - (boolean) active
* - (boolean) default
*/
function save_languages( $args )
{
	$languages = [];
	
	foreach( $args['language_name'] as $i => $name )
	{
		$args['language_status'][$i] = empty( $args['language_status'][$i] ) ? 'pending' : 'publish';
		$args['language_default'][$i] = empty( $args['language_default'][$i] ) ? false : true;

		$languages[] = [
			'language' => $args['language_name'][$i],
			'code' => $args['language_code'][$i],
			'shortcode' => $args['language_shortcode'][$i],
			'status' => $args['language_status'][$i],
			'default' => $args['language_default'][$i],
		];
	}
print_r( $languages );
	return update_languages( $languages );
}


function languages_process_actions()
{
	
	//if( isset( $_POST['
	
}
