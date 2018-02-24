<?php

do_action( 'delete_post', $postid );
$result = $wpdb->delete( $wpdb->posts, array( 'ID' => $postid ) );
if ( ! $result ) {
	return false;
}


add_action('action_di_wordpress', 'my_function', 10, 3 );

function my_function($param1,$param2,$param3){
//codice
}


function example_callback( $example ) {
	// Maybe modify $example in some way.
	return $example;
}
add_filter( 'example_filter', 'example_callback' );
