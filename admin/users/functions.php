<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

add_action( "init", "save_user" );
add_action( "init", "delete_user" );

function save_user()
{
	if( !isset( $_POST['edit-user'] ) ) return false;
	
	if( !ok_verify_nonce( $_POST['nonce'], 'edit-user' ) ) return false;
	
	$_POST['date_created'] = date( "Y-m-d H:i:s" );
	$_POST['date_last_login'] = null;
	$_POST['type'] = "GUEST";
	
	if( !isset( $_POST['user_id'] ) ) $_POST['status'] = "ACT";

	$results = ok_insert_user( $_POST );

	if( $results != false )
	{
		if( isset( $_POST['user_id'] ) )
			insert_success( "Utente modificato con successo" );
		else {
			insert_success( "Utente inserito con successo" );
			ok_redirect( "index.php" );
		}
	} else
		insert_error( "Non è stato possibile salvare l’utente" );
	
	ok_redirect( "" );
}


function delete_user()
{
	if( !isset( $_POST['delete-user'] ) ) return false;
		
	if( !isset( $_POST['user_id'] ) )
	{
		trigger_error( 'id utente non definito' );
		return false;
	}
	
	if( !ok_verify_nonce( $_POST['nonce'], 'delete-user' ) ) return false;
	
	$user = get_user_by( "user_id", $_POST['user_id'] );
	if( $user->type == "ADMIN" )
	{
		trigger_error( "Invalid user ID" );
		return false;
	}
	
	$results = ok_delete_user( $_POST['user_id'] );

	if( $results != false )
	{
		insert_success( "Utente eliminato con successo" );
		ok_redirect( "index.php" );
		
	} else {
		insert_error( "Non è stato possibile eliminare l’utente" );
		ok_redirect( "" );
	}
	
}


