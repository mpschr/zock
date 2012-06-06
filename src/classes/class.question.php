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

include_once('src/classes/interface.bet.php');

class Question implements Bet{

    /**
     * @var unixtime
     */
    protected $time;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $event_id;

    /**
     * @var array
     */
    protected $bets = array();

    /**
     * @var int
     */
    protected $matchday_id;

    /**
     * @var string
     */
    protected $question;

    /**
     * @var string
     */
    protected $possibilities;

    /**
     * @var string
     */
    protected $points;

    /**
     * @var string
     */
    protected $answer;

    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////


    /**
     * @param array $dict
     * @param Event $event
     * @throws Exception
     */
    public function __construct($dict,$event) {
        if (sizeof($dict)==0)
            throw new Exception("empty question");


        foreach ($dict as $key => $value) {
            $this->$key = $value;
        }
    }

    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////


    /**
     * @param string $somebet
     * @return int
     */
    public function getSameBets($somebet)
    {
        // TODO: Implement getSameBets() method.
        return 0;
    }

    /**
     * @return string
     */
    public function getTendency()
    {
        // TODO: Implement getTendancy() method.
        return "";
    }

    /**
     * @param int $user
     * @param string $bet
     * @return string
     */
    public function setBet($user,$bet)
    {
        $this->bets[$user] = $bet;
    }

    /**
     * @param int $user
     * @return string
     */
    public function getBet($user)
    {
       return $this->bets[$user];
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->answer;
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
     * @return string
     */
    public function getId()
    {
        return 'q'.$this->id;
    }

    /**
     * @return int
     */
    public function getDbId()
    {
        return $this->id;
    }


    /**
     * @param string $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
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
     * @param int $event_id
     */
    public function setEventId($event_id)
    {
        $this->event_id = $event_id;
    }

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->event_id;
    }

    /**
     * @param string $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @return string
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param string $possibilities
     */
    public function setPossibilities($possibilities)
    {
        $this->possibilities = $possibilities;
    }

    /**
     * @return string
     */
    public function getPossibilities()
    {
        return $this->possibilities;
    }

    /**
     * @param string $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getRemainingTime()
    {
        // TODO: Implement getRemainingTime() method.
        return '';
    }
    public function getRemainingTimeSpecial()
    {
        // TODO: Implement getRemainingTime() method.
        return '';
    }
}

