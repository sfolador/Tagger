<?php
/**
 * @author Simone Folador - <simone@oxlink.net>
 * Class provides methods to retrieve/save points (tags).
 */

require_once 'config/config.php';
require_once 'TaggerPoint.php';
require_once 'TagsCollection.php';

class WPTagCollection extends TagsCollection {
	
	/**
	 * @var ArrayableCollection
	 */
	private $relatedposts;
	
	function tagsPlugin( $post ) {
		$this->__construct( $post );
	}
	
	function __construct( $post ) {
		parent::__construct( $post );
		$this->relatedposts = new ArrayableCollection();
		$this->getAllThePosts();
	}
	
	
	function save() {
		return $this->savePointsToDB();
	}
	
	function savePointsToDB() {
		return update_post_meta( $this->post->ID, constant( 'TAGGER_METAFIELDNAME' ), $this->__toString() );
	}
	
	function load() {
		return $this->getPointsFromDB();
	}
	
	function getPointsFromDB() {
		$points = get_post_meta( $this->post->ID, constant( 'TAGGER_METAFIELDNAME' ), true );
		
		return $this->parsePoints( $points );
	}
	
	function parsePoints( $points ) {
		
		if ( empty( $points ) ) {
			return false;
		}
		$pointsArray = json_decode( $points );
		foreach ( $pointsArray as $pointElement ) {
			if ( ! empty( $pointElement ) ) {
				
				$this->addPoint( TaggerPoint::pointFromJson( $pointElement ) );
			}
		}
		
		return true;
	}
	
	
	function __toString() {
		
		return json_encode( $this->points->publicArray );
	}
	
	/**
	 * @return string
	 * returns tags as html elements ready to be printed on an image
	 */
	function printInPage() {
		
		$str  = "";
		$iter = 0;
		if ( $this->points->hasElements() ) {
			foreach ( $this->points->publicArray as $point ) {
				$data = $this->getPointExtendedData( $point->data );
				$str  .= '<div class="element ' . $iter . '" style="left: ' . $point->coord['x'] . 'px;top: ' . $point->coord['y'] . 'px">
                      <a href="' . get_permalink( $data->ID ) . '"><img src="' . site_url() . '/' . PLUGINDIR . '/tagger/' . '/images/add-icon.png" />
                             </a>
                    </div>';
				
				// <div class="element-data">
//                <div class="content">' . generateExcerpt($data,10) . '</div>
//                </div>
				
				$iter ++;
			}
		}
		
		return $str;
	}
	
	
	/**
	 * gets all the posts of a given post type.
	 */
	function getAllThePosts() {
		
		$post_type = get_option( 'tagger_tag_related' );
		if ( empty( $post_type ) ) {
			$post_type = 'post';
		}
		
		$args = array(
			'numberposts' => - 1,
			'post_type'   => $post_type,
			'post_status' => 'publish'
		);
		$ps   = get_posts( $args );
		
		foreach ( $ps as $p ) {
			$this->relatedposts->add( $p ); //array('id' => $p->ID, 'title' => $p->post_title)
			
		}
		unset( $ps );
	}
	
	/**
	 * @param $data the post ID
	 *
	 * @return string the post data required
	 * returns the post data.
	 */
	function getPointExtendedData( $data ) {
		foreach ( $this->relatedposts->publicArray as $post ) {
			if ( $post->ID == $data ) {
				return $post;
				break;
			}
			
		}
		
		return "";
	}
	
	
	function generateOptions( $single = false ) {
		if ( $single ) {
			return $this->generateOptionsForATag( null, true );
		}
		$str = "";
		$i   = 0;
		foreach ( $this->points->publicArray as $point ) {
			$str .= $this->generateOptionsForATag( $point, false, $i );
			$i ++;
		}
		
		return $str;
		
	}
	
	/**
	 * @param $point
	 * @param bool $empty
	 * @param string $class
	 *
	 * @return string
	 * produce a html select based on the tag
	 */
	function generateOptionsForATag( $point, $empty = false, $class = '' ) {
		$str = "";
		if ( ! is_null( $point ) ) {
			$str = '<div class="' . $class . ' select-box" id="' . $class . '">';
		} else {
			write_log( "null point" );
		}
		$str       .= '<select name="related[]">';
		$highlight = "";
		foreach ( $this->relatedposts->publicArray as $post ) {
			if ( ! is_null( $point ) ) {
				if ( ( $post->ID == $point->data ) && ( ! $empty ) ) {
					$highlight = 'selected = "selected"';
					
				}
			}
			$str       .= '<option value="' . $post->ID . '" ' . $highlight . ' >' . $post->post_title . '</option>';
			$highlight = '';
		}
		$str .= '</select>';
		
		if ( ! is_null( $point ) ) {
			$str .= '<input type="hidden" name="elements[]" value="' . $point->coord['x'] . ',' . $point->coord['y'] . '"/>';
			$str .= '<a href="#" class="remove">Remove</a>';
			$str .= '</div>';
		}
		
		return $str;
	}
	
	
	function getRelatedPosts() {
		return $this->relatedposts;
	}
	
	
	function retrievePoints() {
		return $this->points;
	}
	
	
	/**
	 * @static
	 *
	 * @param $tag
	 *             post ID
	 *             get all the posts that have that tag (on the picture)
	 */
	static function getPostsThatHaveThisTag( $tag ) {
		$tagged = get_posts( array( 'post_type' => get_option( 'tagger_post_type' ) ) );
		
		$results = array();
		foreach ( $tagged as $p ) {
			$temp = new TagsCollection( $p ); //temp are room settings
			$temp->load(); //gets all the posts tagged in that room setting
			
			if ( $temp->points->hasElements() && $temp->points->searchBy( $tag, array( 'data', 'ID' ) ) ) {
				$p->link   = get_permalink( $p->ID );
				$results[] = $p;
				
			}
			
		}
		
		return $results;
	}
	
	
}
