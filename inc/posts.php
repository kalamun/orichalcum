<?php

/*
* POSTS FUNCTIONS
*/


/*
* POST TYPES
*/

/*
* get the list of all post types
* arguments: orderby, status [ publish | private | hidden ]
*/
function get_post_types( $args=[] )
{
	if( empty( $args['orderby'] ) ) $args['orderby'] = "slug";
	
	$post_types = get_option( 'post_types', false, '*' );
	
	$post_types = unserialize( $post_types );
	
	$output = [];
	
	if( empty( $post_types ) )
		return $output;
	
	// exclusion based on status
	foreach( $post_types as $order => $post_type )
	{
		if( empty($args['status']) || $post_type->status == $args['status'] )
			$output[] = $post_type;
	}

	// sorting
	// orderby = order is already done by previuos foreach
	// sort only in case of language ordering
	if( $args['orderby'] == "slug" )
	{
		if( !function_exists( "_slug_sort" ) )
		{
			function _slug_sort( $a, $b)
			{
				return $a->slug <=> $b->slug;
			}
		}
		
		usort( $output, "_slug_sort");
	}
	
	return $output;
}

/*
* get a single post type
*/
function get_post_type( $slug )
{
	foreach( get_post_types() as $type )
	{
		if( $type->slug == $slug )
			return $type;
	}
	
	return false;
}

/*
* add a new post type to the list
* mandatary input vars: slug, name, singular, status [ publish | private | hidden ]
*/
function add_post_type( $args )
{
	if( empty( $args['slug'] ) ) return false;
	if( empty( $args['name'] ) ) return false;
	if( empty( $args['singular'] ) ) $args['singular'] = $args['name'];
	if( empty( $args['status'] ) ) $args['status'] = 'publish';
	
	$post_types = get_post_types();

	// check if post type already exists
	foreach( $post_types as $post_type )
	{
		if( $post_type->slug == $args['slug'] ) return false;
	}
	
	$new_post_type = new stdClass();
	$new_post_type->slug = $args['slug'];
	$new_post_type->name = $args['name'];
	$new_post_type->singular = $args['singular'];
	$new_post_type->status = $args['status'];

	$post_types[] = $new_post_type;
	
	return update_post_types( $post_types );
}


/*
* update post type in db
*/
function update_post_types( $post_types )
{
	// check syntax
	if( !is_array( $post_types ) )
	{
		trigger_error( "You must provide an array" );
		return false;
	}
	
	foreach( $post_types as $order => $post_type )
	{
		if( !is_numeric( $order ) )
		{
			trigger_error( "Post types array keys must be numeric" );
			return false;
		}
		
		if( !is_object( $post_type ) )
		{
			$post_type = (object)$post_type;
		}
		
		if( empty( $post_type->slug ) )
		{
			trigger_error( "Slug not provided" );
			return false;
		}
		
		if( empty( $post_type->status ) || ( $post_type->status != "publish" && $post_type->status != "private" && $post_type->status != "hidden" ) )
		{
			trigger_error( "Post type status must be publish, private or hidden" );
			return false;
		}
		
		$post_types[ $order ] = $post_type;
	}
	
	update_option( "post_types", serialize( $post_types ), "*" );
}


/*
* POSTS
*/

function ok_update_post( $args )
{
	global $ok_db;
	
	$fields = [];
	
	if( isset( $args['id'] ) ) $fields['id'] = intval( $args['id'] );
	if( isset( $args['post_type'] ) ) $fields['post_type'] = $args['post_type'];
	if( isset( $args['post_lang'] ) ) $fields['post_lang'] = $args['post_lang'];
	if( isset( $args['post_slug'] ) ) $fields['post_slug'] = $args['post_slug'];
	if( isset( $args['post_title'] ) ) $fields['post_title'] = ok_clean_text( $args['post_title'] );
	if( isset( $args['post_subtitle'] ) ) $fields['post_subtitle'] = ok_clean_text( $args['post_subtitle'] );
	if( isset( $args['post_content'] ) ) $fields['post_content'] = ok_clean_text( $args['post_content'] );
	if( isset( $args['post_excerpt'] ) ) $fields['post_excerpt'] = ok_clean_text( $args['post_excerpt'] );
	if( isset( $args['post_date'] ) ) $fields['post_date'] = $args['post_date'];
	if( isset( $args['post_author'] ) ) $fields['post_author'] = intval( $args['post_author'] );
	if( isset( $args['post_status'] ) ) $fields['post_status'] = intval( $args['post_status'] );
	
	// additional checks on post creation
	if( empty( $args['id'] ) )
	{
		// default post type: page
		if( empty( $fields['post_type'] ) ) $fields['post_type'] = 'page';
		
		// empty post lang, get default
		if( empty( $fields['post_lang'] ) ) $fields['post_lang'] = get_language_shortcode();
		
		// empty post title
		if( empty( $fields['post_title'] ) ) $fields['post_title'] = __( "Undefined" );
		
		// empty post subtitle
		if( empty( $fields['post_subtitle'] ) ) $fields['post_subtitle'] = "";
		
		// empty post content
		if( empty( $fields['post_content'] ) ) $fields['post_content'] = "";
		
		// empty post excerpt
		if( empty( $fields['post_excerpt'] ) ) $fields['post_excerpt'] = "";
		
		// empty post slug
		if( empty( $fields['post_slug'] ) ) $fields['post_slug'] = $fields['post_title'];
		$fields['post_slug'] = get_valid_slug( $fields['post_slug'], $fields['post_lang'] );
		
		// empty post date
		if( empty( $fields['post_date'] ) ) $fields['post_date'] = date( "Y-m-d H:i:s" );
		
		// empty post author
		if( empty( $fields['post_author'] ) ) $fields['post_author'] = get_current_user_id();
		
		// status can be: publish, future, draft, pending, trash, private
		if( !isset( $args['post_status'] ) ) $fields['post_status'] = 'draft';
		
		$ok_db->insert( $ok_db->prefix . 'posts', $fields, [ 'on duplicate key update' => $fields ] );

		return $ok_db->last_insert_id();
	
	// update
	} else {
		
		// get previous post
		$post = get_post( $args['id'] );
		if( empty( $post ) )
		{
			trigger_error( 'Invalid post id: the post does not exists' );
			return false;
		}
		
		$where = [
			[
				"key" => "id",
				"compare" => "=",
				"value" => $args['id'],
			]
		];
		
		// empty post lang
		if( empty( $fields['post_lang'] ) ) $fields['post_lang'] = $post->post_lang;
		
		// empty post slug
		if( empty( $fields['post_slug'] ) ) $fields['post_slug'] = $post->post_slug;
		$fields['post_slug'] = get_valid_slug( $fields['post_slug'], $fields['post_lang'], $post->post_slug );
		
		return $ok_db->update( $ok_db->prefix . 'posts', $fields, [ 'where' => $where, 'limit' => 1 ] );
	}
	
}


/*
* get a list of posts matching the given args:
* post_title
* post_subtitle
* post_slug
* post_content
* post_date
* post_lang (auto-detected if not specified)
* post_author
* post_status ("publish" if not specified)
*/

function get_posts( $params )
{
	global $ok_db;

	if( empty( $params['post_lang'] ) )
		$params['post_lang'] = get_language_shortcode();

	if( empty( $params['post_status'] ) )
		$params['post_status'] = "publish";

	$args = [
		"where" => [
			"relation" => "AND",
			[
				"key" => "post_lang",
				"compare" => "=",
				"value" => $params['post_lang'],
			],
		],
	];
	
	// todo: this should be improved with arrays of values
	/*
	foreach( $params as $k => $v )
	{
		$args['where'][] = [
			"key" => $k,
			"compare" => "=",
			"value" => $v,
		];
	}
	*/

	$args['orderby'] = 'post_title';
	$args['order'] = 'ASC';
	var_dump( $args );
	$posts = $ok_db->select( $ok_db->prefix . 'posts', $args );
	
	return $ok_db->fetchAll();
}



/*
* get a single post by id or slug
*/

function get_post( $id, $language = false )
{
	global $ok_db;

	if( empty( $language ) )
		$language = get_language_shortcode();

	$args = [
		"where" => [
			"relation" => "AND",
			[
				"relation" => "OR",
				[
					"key" => "id",
					"compare" => "=",
					"value" => $id,
				],
				[
					"key" => "post_slug",
					"compare" => "=",
					"value" => $id,
				],
			],
			[
				"key" => "post_lang",
				"compare" => "=",
				"value" => $language,
			],
		],
	];
	
	$post = $ok_db->select( $ok_db->prefix . 'posts', $args );
	
	return $ok_db->fetch();
}


/*
* create an unique slug starting from existing slug or title and language
* if previous_slug is defined, slug is allowed to be equal to the previous one
*/
function get_valid_slug( $slug, $language = false, $previous_slug = false )
{
	// lowercase
	$slug = strtolower( $slug );
	
	// remove tags
	$slug = strip_tags( $slug );
	
	// remove new lines
	$slug = str_replace( "\n", " ", $slug );
	$slug = str_replace( "\r", " ", $slug );
	$slug = trim( $slug );
	
	// replace invalid chars with "-"
	$slug = preg_replace( "/[^\w\.\-\p{L}]+/", "-", $slug );
	
	// check if the slug already exists
	if( !empty( $previous_slug ) && $slug == $previous_slug )
		return $slug;
	
	/*
	while( get_post( $slug, $language ) !== false )
	{
		$slug .= rand( 1, 9 );
	}
	*/
	
	return $slug;
}


/*
* clean a text removing empty links, ending paragraphs and nl, etc
*/
function ok_clean_text( $string )
{
	// remove empty links
	$string = preg_replace( "/<a .*?>\s*<\/a>/", "", $string );
	
	// remove ending paragraphs
	$string = preg_replace( "/(<p.*?>\s*<\/p>\s*)+$/", "", $string );
	
	// remove ending br
	$string = preg_replace( "/(<br\/?>\s*)+$/", "", $string );
	
	// remove ending nl
	$string = trim( $string );
	
	return $string;
}
