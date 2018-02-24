<?php
/**
 * Created by PhpStorm.
 * User: simone
 * Date: 24/02/2018
 * Time: 17:33
 */

class TaggerAdmin {
	
	/**
	 * TaggerAdmin constructor.
	 */
	public function __construct() {
		
		$this->setupTaggerAdmin();
		
	}
	
	/**
	 *
	 */
	public function setupTaggerAdmin() {
		//admin
		add_action( 'init', [
			$this,
			'redirectOnTaggerPage'
		] ); //redirects requests of the "rs" page to page-tagger.php
		add_action( 'add_meta_boxes', [ $this, 'createTaggerMetaBox' ] ); //creates custom fields box
		add_action( 'admin_menu', [ $this, 'createTaggerAdminMenu' ] ); //creates admin menu
		add_action( 'admin_notices', [
			$this,
			'createTaggerCustomErrorNotice'
		] ); //display warning if user didn't upload the featured image
	}
	
	/**
	 * Redirects the user if the URL is admin page of Tagger (defaults to "rs").
	 */
	public function redirectOnTaggerPage() {
		wp_register_script( 'jqueryN', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' );
		wp_enqueue_script( 'jqueryN' );
		wp_register_style( 'fancyCss', site_url() . '/' . PLUGINDIR . '/tagger/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' );
		wp_enqueue_style( 'fancyCss' );
		wp_register_script( 'fancybox', site_url() . '/' . PLUGINDIR . '/tagger/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js' );
		wp_enqueue_script( 'fancybox' );
		
		$redirect = get_option( 'tagger_redirect_page' );
		if ( empty( $redirect ) ) {
			$redirect = 'rs';
		}
		
		if ( ! current_user_can( 'administrator' ) && isset( $_GET['page'] ) && ( $_GET['page'] == $redirect ) ) { //if the user is not an admin, he can't access the page (page-tagger.php)
			wp_redirect( '404' ); //TODO change this!
		}
		
		if ( ( isset( $_GET['page'] ) ) && ( $_GET['page'] == $redirect ) ) {
			include( PLUGINDIR . '/tagger/admin-page-tagger.php' );
			exit();
		}
	}
	
	/**
	 * creates the meta box for the chosen post type
	 */
	public function createTaggerMetaBox() {
		if ( function_exists( 'add_meta_box' ) ) {
			$post_type = get_option( 'tagger_post_type' );
			if ( empty( $post_type ) ) {
				$post_type = 'page';
			}
			
			$customfield_title = get_option( 'tagger_customfield_title' );
			if ( empty( $customfield_title ) ) {
				$customfield_title = 'Tagger - Image';
			}
			add_meta_box( 'tagger_id', $customfield_title, [
				$this,
				'createCustomFieldBox'
			], $post_type, 'normal', 'default' );
		}
	}
	
	/**
	 * create the HTML of the custom field box
	 */
	public function createCustomFieldBox() {
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
			?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery(".fan").fancybox({
                        'width': '85%',
                        'height': '85%',
                        'z-index' : 10000,
                        'autoScale': true,
                        'transitionIn': 'none',
                        'transitionOut': 'none',
                        'type': 'iframe'
                    });
                });

            </script>
			<?php
			echo "<a href='$iframe' class='fan'>$thumb</a>";
		}
		
	}
	
	/**
	 * Adds Tagger options to the settings menu
	 */
	public function createTaggerAdminMenu() {
		add_options_page( 'Tagger Options', 'Tagger Settings', 'manage_options', 'tagger-options', [
			$this,
			'addTaggerPluginOptionPage'
		] );
	}
	
	/**
	 * Adds a notice on the top of every post page that warns the users to insert a Featured Image
     * to use Tagger.
	 */
	public function createTaggerCustomErrorNotice() {
		global $current_screen;
		global $post_ID;
		
		if ( $current_screen->id == get_option( 'tagger_post_type' ) ) {
			$img = get_the_post_thumbnail( $post_ID );
			if ( ! $img ) {
				$attention = " <p> <b> Please remember to add a Featured image in order to use Tagger features</b><br/>
                   </p> ";
				echo '<div class="error">' . $attention . '</div>';
			}
		}
	}
	
	/**
	 * the HTML of the admin Tagger options page
	 */
	public function addTaggerPluginOptionPage() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		if ( isset( $_POST['redirect-page'] ) ) {
			update_option( 'tagger_redirect_page', $_POST['redirect-page'] );
		}
		if ( isset( $_POST['post-type'] ) ) {
			update_option( 'tagger_post_type', $_POST['post-type'] );
		}
		if ( isset( $_POST['tag-related'] ) ) {
			update_option( 'tagger_tag_related', $_POST['tag-related'] );
		}
		if ( isset( $_POST['customfield-title'] ) ) {
			
			update_option( 'tagger_customfield_title', $_POST['customfield-title'] );
		}
		
		if ( isset( $_POST['use-shortcode'] ) ) {
			
			update_option( 'tagger_use_shortcode', $_POST['use-shortcode'] );
		}
		
		echo '
        <div class="wrap">';
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
                    <th scope="row"><label for="post-type">Type of posts that Tagger will be attached to (as a
                            custom
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
                    <th scope="row"><label for="tag-related">Select a type of post which tag will be related (a tag
                            on a
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
	
	
}