<?php
/**
 * @Author: simone folador
 *
 */
class ArrayList
{

    //fields

    /**
     * @var array
     * this is a reference to the actual array, @param $array
     */
    public $publicArray;
    /**
     * @var array
     * keeps all the data
     */
    private $array;
    /**
     * @var int
     * number of elements in the array
     */
    public $counter;
    /**
     * @var int
     * the current (next) index
     */
    private $index;


    //constructors

    /**
     * PHP 4 compliant
     */
    function ArrayList(){
        $this->__construct();
    }

    /**
     * PHP 5 compliant constructor
     */
    function __construct(){
        $this->counter = 0;
        $this->index = 0;
        $this->array = array();
        $this->publicArray = &$this->array;
    }

    /**
     * @param $element
     * Adds an element to the list. The element is placed on the next available position.
     */
    function add($element){
        $this->array[$this->index] = $element;
        $this->counter++;
        $this->index++;
    }

    /**
     * @param $i
     * @param $element
     * insert an element in a specified position. If $i is greater than the current array length,
     * the element is placed at the end of the list.
     * if $i is lower than 0 the element is placed at the beginning
     *
     */
    function insert($i,$element){
        if ($i > $this->counter) $i = $this->counter; // if i is too big, it gets reduced to counter (last index)
        if ($i < 0) $i = 0;
        for ($j = $this->counter-1; $j >= $i; $j--){
            $this->array[$j+1] = $this->array[$j];
        }
        $this->array[$i] = $element;
        $this->counter++;
        $this->index++;
    }

    /**
     * @param $i
     * @return mixed
     * returns the element at the specified index
     */
    function get($i){
        return $this->array[$i];
    }

    /**
     * @param $element
     * @return mixed
     * unuseful
     */
    function getElement($element){
          return $this->get($this->getIndex($element));
    }

    /**
     * @return mixed
     */
    function getLast(){
        return $this->get($this->index-1);
    }

    /**
     * @return mixed
     * accessory function. just returns the head of the list
     */
    function getFirst(){
        return $this->get(0);
    }

    /**
     * @param $i int or boolean
     * @return bool
     * Removes an item from the list at the provided index.
     */
    function remove($i){

        if (is_int($i) && ($i <= $this->counter)){
            for($j = $i; $j <= $this->counter-1; $j++){
                $this->array[$j] = $this->array[$j+1];
            }
            $this->counter--;
            $this->index--;
            return true;
        }
        return false;
    }

    /**
     * @param $element
     * @return bool
     * Removes an element from the list. Without specifying the position
     */
    function removeElement($element){
        return $this->remove($this->getIndex($element));
    }

    /**
     * @param $element
     * @return bool|int
     * returns the position of $element inside the list. If not found, returns false.
     */
    private function getIndex($element){
       for($i = 0; $i < $this->counter; $i++){

            if ($this->array[$i] === $element){

                return $i;
                break;
            }
        }
        return false;
    }

    /**
     * accessory function. Resets the list.
     */
    public function resetAll(){
        $this->array = array();
    }


    /**
     * @return bool
     * checks if this array has elements
     */
    public function hasElements(){
        return $this->counter > 0;
    }


    /**
     * @param $element
     * @param string $compare the field we want to compare
     * @return bool|mixed if found returns the element, if not, returns false
     */
    public function searchBy($element, $compare = array('ID','ID')){
        $c = 0;
        $flag = false;
        list($firstField, $secondField) = $compare;
        for ($i =0; $i<$this->counter;$i++){
            if ($this->array[$i]->$firstField == $element->$secondField)
            {
                $c = $i;
                $flag = true;
            }
        }
        if (!$flag) {return false;}
        return $this->array[$c];
    }

}
