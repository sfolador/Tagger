<?php
/**
 * Created by JetBrains PhpStorm.
 * User: simone
 * Date: 14/12/2011
 * Time: 13:13
 * To change this template use File | Settings | File Templates.
 */
require_once 'point.php';
class pointPlugin extends point
{
    function toString(){
        return $this->__toString();
    }

    function __toString(){
        return $this->coord['x'] .','.$this->coord['y'].':'.$this->data.';';
    }
}
