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


class Match implements Bet{

    /**
     * @var unixtime
     */
    protected $time;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $matchday;

    /**
     * @var int
     */
    protected $matchday_id;

    /**
     * @var int
     */
    protected $komatch;

    /**
     * @var string
     */
    protected $home;

    /**
     * @var string
     */
    protected $visitor;

    /**
     * @var int
     */
    protected $score_h;

    /**
     * @var int
     */
    protected $score_v;

    /**
     * @var string
     */
    protected $score_special;

    /**
     * @var float
     */
    protected $jackpot;

    /**
     * @var array
     */
    protected $bets;


    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////


    /**
     * @param array $dict
     * @throws Exception
     */
    public function __construct($dict) {
        if (sizeof($dict)==0)
            throw new Exception("empty match");


        foreach ($dict as $key => $value) {
            $this->$key = $value;
        }
    }


    /////////////////////////////////////////////////
    // GETTERS & SETTERS
    /////////////////////////////////////////////////


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

    /**
     * @param array $bets
     */
    public function setBets($bets)
    {
        $this->bets = $bets;
    }

    /**
     * @return array
     */
    public function getBets()
    {
        return $this->bets;
    }

    /**
     * @param string $home
     */
    public function setHome($home)
    {
        $this->home = $home;
    }

    /**
     * @return string
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $jackpot
     */
    public function setJackpot($jackpot)
    {
        $this->jackpot = $jackpot;
    }

    /**
     * @return float
     */
    public function getJackpot()
    {
        return $this->jackpot;
    }

    /**
     * @param int $komatch
     */
    public function setKomatch($komatch)
    {
        $this->komatch = $komatch;
    }

    /**
     * @return int
     */
    public function getKomatch()
    {
        return $this->komatch;
    }

    /**
     * @param string $matchday
     */
    public function setMatchday($matchday)
    {
        $this->matchday = $matchday;
    }

    /**
     * @return string
     */
    public function getMatchday()
    {
        return $this->matchday;
    }

    /**
     * @param int $matchday_id
     */
    public function setMatchdayId($matchday_id)
    {
        $this->matchday_id = $matchday_id;
    }

    /**
     * @return int
     */
    public function getMatchdayId()
    {
        return $this->matchday_id;
    }

    /**
     * @param int $score_h
     */
    public function setScoreH($score_h)
    {
        $this->score_h = $score_h;
    }

    /**
     * @return int
     */
    public function getScoreH()
    {
        return $this->score_h;
    }

    /**
     * @param string $score_special
     */
    public function setScoreSpecial($score_special)
    {
        $this->score_special = $score_special;
    }

    /**
     * @return string
     */
    public function getScoreSpecial()
    {
        return $this->score_special;
    }

    /**
     * @param int $score_v
     */
    public function setScoreV($score_v)
    {
        $this->score_v = $score_v;
    }

    /**
     * @return int
     */
    public function getScoreV()
    {
        return $this->score_v;
    }

    /**
     * @param string $visitor
     */
    public function setVisitor($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @return string
     */
    public function getVisitor()
    {
        return $this->visitor;
    }
}

