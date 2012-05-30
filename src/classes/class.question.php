<?php
/*
===================================
zock!

Developed by
------------
* Michael Schroeder:
   michael.p.schroeder@gmail.com 
*
*

http://zock.sf.net

zock! is a free software licensed under GPL (General public license) v3
      more information look in the root folder for "LICENSE".
===================================
*/


class Question implements Bet{

    /**
     * @var unixtime
     */
    protected $time;

    /**
     * @var int
     */
    protected $id;

    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////


    /**
     * @param array $dict
     * @throws Exception
     */
    public function __construct($dict) {
        if (sizeof($dict)==0)
            throw new Exception("empty question");


        foreach ($dict as $key => $value) {
            $this->$key = $value;
        }
    }


    /**
     * @return unixtime
     */
    function getTime()
    {
        return $this->time;
    }

    /**
     * @param $unixtime
     * @return mixed
     */
    function setTime($unixtime)
    {
        $this->time = $unixtime;
    }

    /**
     * @return array
     */
    function getDueDate()
    {
        return $this->time;
    }
}

