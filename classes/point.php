<?php
/**
 * Created by JetBrains PhpStorm.
 * User: simone
 * Date: 12/12/2011
 * Time: 17:22
 * To change this template use File | Settings | File Templates.
 */
class point
{
    public $coord = array('x' => 0, 'y' => 0);
    public $data;

    function point($coord, $data){
        $this->__construct($coord,$data);
    }

    /**
     * @param $coord array (must be like array('x' => 0, 'y'=> 0)
     * @param $data typically a string
     */
    function __construct($coord,$data){
        $this->coord = $coord;
        $this->data = $data;
    }


    function __toString(){
        //return $this->coord['x'] .','.$this->coord['y'].':'.$this->data.';';
    }
}
