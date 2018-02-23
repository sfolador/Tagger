<?php
/**
 * @author Simone Folador - simone@oxlink.net
 */
require_once 'point.php';
require_once 'ArrayList.php';


class tags
{
    public $points;
    public $post;

    function tags($post){
        $this->__construct($post);
    }

    function __construct($post){
        $this->post = $post;
        $this->points = new ArrayList();
    }

    function addPoint($point){
        $this->points->add($point);
    }

    function getPoints(){
        return $this->points;
    }

    function removePoint($point){
        return $this->points->removeElement($point);
    }

    function removeAll(){
        $this->points->resetAll();
    }

    function __toString(){

    }


}
