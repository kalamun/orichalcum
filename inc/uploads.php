<?php

/*
* UPLOADS
*/

function get_upload_directory()
{
	return get_site_directory() . '/' . trim( UPLOAD_DIR, "/" ) . '/';
}

// alias of get_upload_directory
function ok_upload_dir()
{
	return get_upload_directory();
}

function get_upload_directory_uri()
{
	return get_site_directory_uri() . '/' . trim( UPLOAD_DIR, "/" ) . '/';
}

class ok_file_uploader
{
	public function ajax_upload_file()
	{
		$upload_dir = get_upload_directory();
		$file_path = trailingslashit( $upload_dir ) . $_POST['file'];
		$file_data = $this->decode_chunk( $_POST['file_data'] );
		if( false === $file_data )
			wp_send_json_error();

		file_put_contents( $file_path, $file_data, FILE_APPEND );
		//wp_send_json_success();
	}
	
	public function decode_chunk( $data )
	{
		$data = explode( ';base64,', $data );

		if( !is_array( $data ) || !isset( $data[1] ) )
			return false;

		$data = base64_decode( $data[1] );
		if( !$data )
			return false;

		return $data;
	}
}
new ok_file_uploader();

function get_uploads( $attr = [] )
{
	
}

