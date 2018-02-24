<?php
/**
 * @author Simone Folador
 */
require_once 'Point.php';

class TaggerPoint extends Point {
	
	
	function __toString() {
		return $this->coord['x'] . ',' . $this->coord['y'] . ':' . $this->data . ';';
	}
	
	public static function pointFromJson($jsonObject)
	{
		return new self(array( 'x' => $jsonObject->coord->x, 'y' => $jsonObject->coord->y ),
			$jsonObject->data );
	}
}
