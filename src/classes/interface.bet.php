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


interface Bet {

    /**
     * @abstract
     * @return array
     */
    public function getDueDate();

    /**
     * @abstract
     * @return unixtime
     */
    public function getTime();

    /**
     * @abstract
     * @param $unixtime
     * @return mixed
     */
    public function setTime($unixtime);
}