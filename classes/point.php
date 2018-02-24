<?php

/**
 * @author Simone Folador
 */
class Point {
	/**
	 * @var array
	 */
	public $coord = array( 'x' => 0, 'y' => 0 );
	/**
	 * @var string
	 */
	public $data;
	
	/**
	 * @param $coord array (i.e.: array('x' => 0, 'y'=> 0))
	 * @param $data  string
	 */
	function __construct( $coord, $data ) {
		$this->coord = $coord;
		$this->data  = $data;
	}
	
}
