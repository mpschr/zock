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
     * @return string
     */
    public function getRemainingTime();

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

    /**
     * @abstract
     * @return int
     */
    public function getId();

    /**
     * @abstract
     * @param string $somebet
     * @return int
     */
    public function getSameBets($somebet);

    /**
     * @abstract
     * @return string
     */
    public function getTendency();

    /**
     * @abstract
     * @param int $user
     * @return string
     */
    public function getBet($user);

    /**
     * @abstract
     * @return string

    public function getBetType();
     * **/

    /**
     * @abstract
     * @return string
     */
    public function getResult();

    /**
     * @abstract
     * @param $user int
     * @param $bet
     * @internal param string $userbet
     * @return void
     */
    public function setBet($user,$bet);


    /**
     * @abstract
     * @return void
     */
    public function getCorrectBets();

    /**
     * @abstract
     * @param $user int
     */
    public function isCorrectBet($user);

    /**
     * @abstract
     * @param $user int
     */
    public function getUserPoints($user);

    /**
     * @abstract
     * @param $user int
     */
    public function getMoney($user);

    /**
     * @abstract
     * @return mixed
     */
    public function getMatchdayId();
}