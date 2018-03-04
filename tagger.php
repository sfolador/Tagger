<?php
/*
 Plugin Name: Tagger
 Author: Simone Folador
 License: GPLv2 or later
 Description: This plugin allows admins to tag images. Each tag can be related to a post or page
 Credits: this plugin uses jQuery & Fancybox
*/

require_once 'classes/WPTagCollection.php';
require_once 'classes/WP/TaggerAdmin.php';


class Tagger {
	/**
	 * Tagger constructor.
	 */
	public function __construct() {
		$this->setupTagger();
	}
	
	/**
	 *
	 */
	private function setupTagger() {
		add_shortcode( 'Tagger', [ $this, 'printTags' ] );
		add_action( 'after_setup_theme', [ $this, 'setupPostType' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'registerScriptsAndStylesForTagger' ] );
		add_filter( 'delete_post_metadata', [ $this, 'onRemovedThumbnail' ], 10, 3 );
		new TaggerAdmin();
	}
	
	/**
	 * Register all the scripts and styles for this plugin
	 */
	public function registerScriptsAndStylesForTagger() {
		
		wp_register_script( 'jqueryN', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' );
		wp_enqueue_script( 'jqueryN' );
		wp_register_script( 'taggerJS', '/' . PLUGINDIR . '/tagger/assets/dist/js/application.js' );
		wp_enqueue_script( 'taggerJS' );
		wp_register_style( 'taggerStyle', '/' . PLUGINDIR . '/tagger/assets/dist/css/app.css' );
		wp_enqueue_style( 'taggerStyle' );
	}
	
	/**
	 *
	 * Called when a thumbnail (feature image) is removed from a post that has Tagger Tags
	 *
	 * @param $id
	 * @param $postId
	 * @param $metaName
	 */
	function onRemovedThumbnail( $id, $postId, $metaName ) {
		
		if ( $metaName == '_thumbnail_id' ) {
			$p = get_post( $postId );
			if ( $p->post_type == get_option( 'tagger_post_type' ) ) {
				$wtColl = new WPTagCollection( $p );
				$wtColl->removeAll();
				
			}
		}
		
	}
	
	/**
	 *
	 * Shortcode
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	function printTags( $attr ) {
		
		global $post;
		$s = get_option( 'tagger_use_shortcode' );
		if ( $s == 2 ) {
			return $this->printTaggerImg( $post );
		}
		
		return "";
	}
	
	/**
	 * Registers the post type Room Setting.
	 */
	function setupPostType() {
		$labels = array(
			'name'               => _x( 'Room Settings', 'post type general name' ),
			'singular_name'      => _x( 'Room Setting', 'post type singular name' ),
			'add_new'            => _x( 'Add New', 'room setting' ),
			'add_new_item'       => __( 'Add New Room Setting' ),
			'edit_item'          => __( 'Edit Room Setting' ),
			'new_item'           => __( 'New Room Setting' ),
			'all_items'          => __( 'All Room Settings' ),
			'view_item'          => __( 'View Room Setting' ),
			'search_items'       => __( 'Search Room Settings' ),
			'not_found'          => __( 'No Room Settings found' ),
			'not_found_in_trash' => __( 'No Room Settings found in Trash' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Room Settings'
		
		);
		$args   = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ "slug" => "room-setting" ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' )
		);
		
		register_post_type( 'room_setting', $args );
		
	}
	
	
	/**
	 * @param $post
	 * Prints the image and adds tags (reading from the DB) if any.
	 * This is to be used in the front-end as a template tag.
	 *
	 */
	function printTaggerImg( $post = null ) {
		
		$id = @intval( $post );
		
		if ( ! ( is_object( $post ) ) ) {
			$post     = new stdClass();
			$post->ID = $id;
		}
		
		$img     = get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'item-clip', 'title' => "" ) );
		$tags    = new WPTagCollection( $post );
		$content = "";
		
		if ( $tags->load() ) {
			
			$content .= '<div id="image-container" class="main-image">';
			
			$content .= $img;
			$content .= $tags->printInPage();
			$content .= '<div style="clear:both"></div>';
			$content .= '</div>';
			
		} elseif ( $img ) {
			$content .= $img;
		}
		
		return $content;
	}
	
	static function printProducts( $post ) {
		$tags    = new WPTagCollection( $post );
		$results = array();
		write_log( "print products" );
		if ( $tags->load() ) {
			$points = $tags->getPoints();
			if ( $points->hasElements() ) {
				for ( $i = 0; $i < $points->counter; $i ++ ) {
					if ( ( get_class( $points->publicArray[ $i ] ) == 'TaggerPoint' ) && ( $points->publicArray[ $i ]->data ) ) {
						$tempPost = get_post( $points->publicArray[ $i ]->data );
						
						$thumb = get_the_post_thumbnail( $tempPost->ID, 'product-thumb' );
						if ( $thumb ) {
							$tempPost->thumb = $thumb;
						}
						
						$tempPost->link = get_permalink( $tempPost->ID );
						$results[]      = $tempPost;
					}
					
				}
			}
		}
		
		if ( $results ) {
			return $results;
		}
		
		return false;
		
	}
	
	/**
	 * @param $arr
	 */
	static function printArrayOfRelatedTags( $arr ) {
		echo '<div id="in-this-picture">';
		if ( $arr ) {
			foreach ( $arr as $k => $element ) {
				if ( $element->thumb ) {
					echo $element->thumb;
				}
				echo "{$element->post_title}: <a href='{$element->link}' class='$k'>{$element->post_title}</a><br/>";
			}
		}
		echo '</div>';
	}
	
	static function printRelatedTags( $post ) {
		return Tagger::printArrayOfRelatedTags( Tagger::printProducts( $post ) );
	}
	
}

$t = new Tagger();
