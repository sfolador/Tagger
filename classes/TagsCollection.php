<?php
/**
 * @author Simone Folador
 */
require_once 'Point.php';
require_once 'ArrayableCollection.php';


class TagsCollection {
	/**
	 * @var ArrayableCollection
	 */
	public $points;
	public $post;
	
	
	function __construct( $post ) {
		$this->post   = $post;
		$this->points = new ArrayableCollection();
	}
	
	function addPoint( $point ) {
		$this->points->add( $point );
	}
	
	function getPoints() {
		return $this->points;
	}
	
	function removePoint( $point ) {
		return $this->points->removeElement( $point );
	}
	
	function removeAll() {
		$this->points->resetAll();
	}
	
	
}
