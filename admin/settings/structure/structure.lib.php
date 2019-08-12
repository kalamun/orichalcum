<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

ok_enqueue_script( 'structure', 'js/structure.js' );

add_action( 'ok_ajax_do_ajax', 'structure_process_ajax' );

function structure_process_ajax()
{
	$response = "";

	switch($_REQUEST['fn'])
	{

		/* import user media with progress bar */
		case 'add-post-type':
			if( ok_verify_nonce( $_REQUEST['nonce'], 'add-post-type' ) )
			{
				trigger_error( 'Invalid nonce.' );
				break;
			}
			
			$attr = [
				"slug" => $_REQUEST['new_post_type_slug'],
				"name" => $_REQUEST['new_post_type_name'],
				"singular" => $_REQUEST['new_post_type_singular'],
				"status" => $_REQUEST['new_post_type_status'],
			];
			$response = add_post_type( $attr );
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

