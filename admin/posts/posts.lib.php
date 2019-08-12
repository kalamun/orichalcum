<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

ok_enqueue_script( 'posts', 'js/posts.js' );

add_action( 'ok_ajax_do_ajax', 'posts_process_ajax' );

function posts_process_ajax()
{
	$response = "";

	switch( $_REQUEST['fn'] )
	{

		/* update post */
		case 'update-post':
			if( ok_verify_nonce( $_REQUEST['nonce'], 'update-post' ) )
			{
				trigger_error( 'Invalid nonce.' );
				break;
			}
			
			$attr = [
				"post_title" => $_REQUEST['post_title'],
				"post_subtitle" => $_REQUEST['post_subtitle'],
				"post_content" => $_REQUEST['post_content'],
			];
			
			if( !empty( $_REQUEST['id'] ) )
				$attr[ "id" ] = $_REQUEST['id'];
			
			$response = ok_update_post( $attr );
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

