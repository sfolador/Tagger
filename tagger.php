<?php
/*
Plugin Name: Tagger
Author: Simone Folador
License: GPLv2 or later
Description: This plugin allows admins to tag images. Each tag can be related to a post/page etc..
Credits: this plugin uses jquery fancybox
*/

require_once 'classes/tagsPlugin.php';

global $post;



if ( ! $post ) {
	$post = new stdClass();
}

$redirect = get_option( 'tagger_redirect_page' );
if ( empty( $redirect ) ) {
	$redirect = 'rs';
}

$post_type = get_option( 'tagger_post_type' );
if ( empty( $post_type ) ) {
	$post_type = 'page';
}



//if ( ( $post->post_type = $post_type ) ) {
//	wp_register_script( 'jqueryN', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' );
//	wp_enqueue_script( 'jqueryN' );
//	wp_register_script( 'taggerJS', '/' . PLUGINDIR . '/tagger/js/tagger.js' );
//	wp_enqueue_script( 'taggerJS' );
//	wp_register_style( 'taggerStyle', '/' . PLUGINDIR . '/tagger/css/style.css' );
//	wp_enqueue_style( 'taggerStyle' );
//}

add_action( 'wp_enqueue_scripts', 'tagger_register_scripts');

function tagger_register_scripts()
{
	wp_register_script( 'jqueryN', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' );
	wp_enqueue_script( 'jqueryN' );
	wp_register_script( 'taggerJS', '/' . PLUGINDIR . '/tagger/js/tagger.js' );
	wp_enqueue_script( 'taggerJS' );
	wp_register_style( 'taggerStyle', '/' . PLUGINDIR . '/tagger/css/style.css' );
	wp_enqueue_style( 'taggerStyle' );
}

add_action( 'init', 'redirectOnTagger' ); //redirects requests of the "rs" page to page-tagger.php

function redirectOnTagger() {
	
	wp_register_script( 'jqueryN', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' );
	wp_enqueue_script( 'jqueryN' );
//wp_register_style('fancyCss', home_url() . '/sys/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css');
	wp_register_style( 'fancyCss', site_url() . '/' . PLUGINDIR . '/tagger/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' );
	wp_enqueue_style( 'fancyCss' );
//wp_register_script('fancybox', home_url() . '/sys/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js');
	wp_register_script( 'fancybox', site_url() . '/' . PLUGINDIR . '/tagger/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js' );
	wp_enqueue_script( 'fancybox' );
 
 
	$redirect = get_option( 'tagger_redirect_page' );
	if ( empty( $redirect ) ) {
		$redirect = 'rs';
	}
	
	if ( ! current_user_can( 'administrator' ) && isset($_GET['page']) &&( $_GET['page'] == $redirect ) ) { //if the user is not an admin, he can't access the page (page-tagger.php)
		wp_redirect( '404' ); //TODO change this!
	}
	
	if ( (isset($_GET['page'])) && ($_GET['page'] == $redirect) ) {
		include( 'page-tagger.php' );
		exit();
	}
}

add_action( 'add_meta_boxes', 'tagger_meta_box' ); //creates custom fields box

function tagger_meta_box() {
	if ( function_exists( 'add_meta_box' ) ) {
		$post_type = get_option( 'tagger_post_type' );
		if ( empty( $post_type ) ) {
			$post_type = 'page';
		}
		
		$customfield_title = get_option( 'tagger_customfield_title' );
		if ( empty( $customfield_title ) ) {
			$customfield_title = 'Tagger - Image';
		}
		add_meta_box( 'tagger_id', $customfield_title, 'customfield_box', $post_type, 'normal', 'default' );
	}
}

function customfield_box() {
	global $post;
	echo "Select image:" . '<br/>';
	
	$redirect = get_option( 'tagger_redirect_page' );
	if ( empty( $redirect ) ) {
		$redirect = 'rs';
	}
	
	$url    = get_bloginfo( 'url' );
	$iframe = $url . '?page=' . $redirect . '&post=' . $post->ID;
	
	//echo "POST ID: ".$post->ID."<br/>";
	$thumb = get_the_post_thumbnail( $post->ID, 'thumbnail' );
	if ( empty( $thumb ) ) {
		echo "Please insert image by selecting a Featured Image. Remember to update the post. ";
	} else {
		echo '<script type="text/javascript">';
		echo 'jQuery(document).ready(function(){';
		echo 'jQuery(".fan").fancybox({';
		echo "'width'				: '85%',
		'height'			: '85%',
        'autoScale'     	: false,
        'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe'
	});
		});

		</script>";
		echo "<a href='$iframe' class='fan'>$thumb</a>";
	}
	
}

add_action( 'admin_menu', 'my_plugin_menu' ); //creates admin menu

function my_plugin_menu() {
	add_options_page( 'Tagger Options', 'Tagger Settings', 'manage_options', 'tagger-options', 'my_plugin_options' );
}

function my_plugin_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	if ( $_POST['redirect-page'] ) {
		update_option( 'tagger_redirect_page', $_POST['redirect-page'] );
	}
	if ( $_POST['post-type'] ) {
		update_option( 'tagger_post_type', $_POST['post-type'] );
	}
	if ( $_POST['tag-related'] ) {
		update_option( 'tagger_tag_related', $_POST['tag-related'] );
	}
	if ( $_POST['customfield-title'] ) {
		
		update_option( 'tagger_customfield_title', $_POST['customfield-title'] );
	}
	
	if ( $_POST['use-shortcode'] ) {
		
		update_option( 'tagger_use_shortcode', $_POST['use-shortcode'] );
	}
	
	echo '<div class="wrap">';
	?>
    <h3>Tagger Settings</h3>
    <p>
        This plugin allows an admin to tag photos and relate them to posts/pages.
    </p>
    <p>Please select your options:</p>


    <form action="options-general.php?page=tagger-options" method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="redirect-page">Redirect page: </label></th>
                <td><input type="text" name="redirect-page" id="redirect-page" class="regular-text"
                           value="<?php echo get_option( 'tagger_redirect_page' ); ?>"/>
                    <span class="description">The page called by the admin section</span>


                </td>
            </tr>
            <tr>
                <th scope="row"><label for="customfield-title">Custom field title: </label></th>
                <td><input type="text" name="customfield-title" id="customfield-title" class="regular-text"
                           value="<?php echo get_option( 'tagger_customfield_title' ); ?>"/>
                    <span class="description">The title displayed on the custom field Box</span>


                </td>
            </tr>
            <tr>
                <th scope="row"><label for="post-type">Type of posts that Tagger will be attached to (as a custom
                        field): </label></th>
                <td>

                    <select name="post-type" id="post-type">
						<?php
						$selected = "";
						
						$post_types = get_post_types();
						foreach ( $post_types as $pt ) {
							$ptObj = get_post_type_object( $pt );
							if ( $pt == get_option( 'tagger_post_type' ) ) {
								$selected = 'selected="selected"';
							}
							echo '<option value="' . $pt . '" ' . $selected . '>' . $ptObj->labels->singular_name . '</option>';
							$selected = "";
						}
						
						?>
                    </select>
                    <span class="description">(default) Room Setting. Example: All the Room Setting Posts will have the custom field box Room Settings.</span>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="tag-related">Select a type of post which tag will be related (a tag on a
                        picture will be related
                        to a post type): </label></th>
                <td>
                    <select name="tag-related" id="tag-related" class="regular-text">
						<?php
						$post_types = get_post_types();
						$selected   = "";
						foreach ( $post_types as $pt ) {
							$ptObj = get_post_type_object( $pt );
							if ( $pt == get_option( 'tagger_tag_related' ) ) {
								$selected = 'selected="selected"';
							}
							echo '<option value="' . $pt . '" ' . $selected . '>' . $ptObj->labels->singular_name . '</option>';
							$selected = "";
						}
						
						?>

                    </select>
                    <span class="description">This means that when a point is selected on an image (admin), only posts of this type will be displayed.</span>
                </td>
            </tr>


            <tr>
                <th scope="row"><label for="use-shortcode">Use shortcode?</label></th>
                <td>
					<?php $s  = get_option( 'tagger_use_shortcode' );
					$selected = 'selected="selected"';
					
					$useShortCode = $s;
					?>
                    <select name="use-shortcode" id="use-shortcode">

                        <option value="1" <?php echo ( $s == '1' ) ? $selected : ""; ?>>no</option>
                        <option value="2" <?php echo ( $s == '2' ) ? $selected : ""; ?>>yes</option>


                    </select>
                    <span class="description"></span>
                </td>
            </tr>

            <tr>
                <td><input type="submit" class="button-primary"/></td>
            </tr>

        </table>
    </form>
	<?php
	
	echo '</div>';
	?>

    <div>
		<?php if ( $useShortCode == 1 ) {
			
			remove_shortcode( 'Tagger' );
			
			?>
            <p>Please, create a new template (if you don't have one) for the post type
                <b> <?php echo get_option( 'tagger_post_type' ); ?> </b><br/>
                and add this code where you would like to have the tagged image (inside the LOOP):<br/></p>

            <code>
                echo print_tagger_img($post);
            </code>

            <p> if the previous line is put outside the Loop, it's better to use: </p>
            <code>
                echo print_tagger_img($postID);
            </code>
            <p>Where $postID is the ID of the Room Setting post.</p>
			<?php
		} else {
			?>
            <p>You chose to use the Shortcode. If you are writing a post of post type
                <b> <?php echo get_option( 'tagger_post_type' ); ?> </b><br/>
                simply add this easy shortcode to the content:<br/></p>
            <code>
                [Tagger]
            </code>
		
		<?php } ?>
    </div>
	
	<?php
}

$c = 0;

/**
 * @param $post
 * Prints the image and adds tags (reading from the DB) if any.
 * This is to be used in the front-end as a template tag.
 *
 */
function print_tagger_img( $post = null ) {
	
	$id = @intval( $post );
	
	if ( ! ( is_object( $post)  ) ) {
		$post     = new stdClass();
		$post->ID = $id;
	}
	
	$img     = get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'item-clip', 'title' => "" ) );
	$tags    = new tagsPlugin( $post );
	$content = "";
	
	
	
	
	
	if ( $tags->load() ) {
		
		
		$content .= '<div id="image-container" class="main-image">';

//        $content .= '<span class="showroom-grid-item-top"></span> ';
//        $content .= '<span class="showroom-grid-item-btm"></span> ';

//    $img = "<div class='item-clip'>$img</div> ";
		
		$content .= $img;
		$content .= $tags->printInPage();
		$content .= '<div style="clear:both"></div>';
		$content .= '</div>';
		
	} elseif ( $img ) {
		$content .= $img;
	}

	
	return $content;
}

add_shortcode( 'Tagger', 'printTags' );

function printTags( $attr ) {
	
	global $post;
	$s = get_option( 'tagger_use_shortcode' );
	if ( $s == 2 ) {
		return print_tagger_img( $post );
	}
	
	return "";
}

add_action( 'after_setup_theme', 'setupTagger' );

function setupTagger() {
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
		'rewrite'            => true,
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' )
	);
	
	register_post_type( 'room_setting', $args );
	
	
}

add_action( 'admin_notices', 'custom_error_notice' ); //display warning if user didn't upload the featured image

function custom_error_notice() {
	global $current_screen;
	global $post_ID;
	
	if ( $current_screen->id == 'room_setting' ) {
		$img = get_the_post_thumbnail( $post_ID );
		if ( ! $img ) {
			$attention = " <p> <b> Please remember to add a Featured image in order to use Room Setting features</b><br/>
                   </p> ";
			echo '<div class="error">' . $attention . '</div>';
		}
	}
	
}

function printProducts( $post ) {
	$tags    = new tagsPlugin( $post );
	$results = array();
	write_log( "print products");
	if ( $tags->load() ) {
		$points = $tags->getPoints();
		if ( $points->hasElements() ) {
			for ( $i = 0; $i < $points->counter; $i ++ ) {
				if ( ( get_class( $points->publicArray[ $i ] ) == 'pointPlugin' ) && ( $points->publicArray[ $i ]->data ) ) {
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

function printArrayOfRelatedTags( $arr ) {
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

//TODO add function that removes tags if the featured image is removed