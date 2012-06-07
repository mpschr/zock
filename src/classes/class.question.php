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
     * @var Event
     */
    protected $event;

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
        $this->event = $event;

        foreach ($dict as $key => $value) {
            $this->$key = $value;
        }
    }

    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////


    /**
     * @param string $somebet
     * @return string
     */
    public function getSameBets($somebet)
    {
        $userbet = preg_split("/:/", $somebet);
        $counts = array();
        foreach ($userbet as $bet) {
            if ($bet == '')
                continue;
            $counts[$bet] = -1;
        }

        foreach($this->bets as $user => $bets) {
            foreach(preg_split("/:/", $bets) as $bet) {
                if (isset($counts[$bet]))
                    $counts[$bet] +=  1;
            }

        }

        $samebetString = '';
        foreach ($counts as $b => $c) {
            $samebetString .= '<nobr>'.$b.': '.$c.'</nobr> <br/>';
        }
        return ($samebetString == '') ? "?" : $samebetString;
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
    public function assignBet($user,$bet)
    {
        $this->bets[$user] = $bet;
    }


    /**
     * @param int $user
     * @param string $bet
     * @return string|void
     */
    public function setBet($user,$bet)
    {
        global $db;

        if ($this->answer == $bet)
            return true;

        $this->event->getId();

        if (isset($this->bets[$user])) {

            $query = "UPDATE ".PFIX."_qa_bets  SET
                        answer = '".$bet."'
                        WHERE `question_id`=".$this->id." AND `user_id` = ".$user." ;";

        } else {
            $query = "INSERT INTO ".PFIX."_qa_bets (
                            `answer`, `question_id`, `user_id`)
                        VALUES (
                            '".$bet."','".$this->id."','".$user."'
                        );";
        }


        $queryres = $db->query($query);

        if ($queryres) {
            $this->bets[$user] = $bet;
            return true;
        }
        return false;
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
    /**
     * @return string
     */
    public function getRemainingTime()
    {
        global $cont;
        $day = 'd';
        $sec_day = 24*60*60;
        $hour = 'h';
        $sec_hour = 60*60;
        $min = 'm';
        $sec_min = 60;

        $show_mins = true;


        $now = time();
        $betuntil = $this->getDueDate();
        $remaining = $betuntil - $now;
        if ($remaining < 0)
            return $cont->get('mytips_passed');

        $remainingString = '';
        $rdays = ($remaining/$sec_day > 0) ? floor($remaining/$sec_day) : '0';
        if ($rdays > 0) {
            $remainingString .= $rdays.$day;
            $remaining -= $rdays*$sec_day;
            $show_mins = false;
        }
        $rhours = ($remaining/$sec_hour > 0) ? floor($remaining/$sec_hour) : '0';
        if ($rhours > 0) {
            $remainingString .= ' '.$rhours.$hour;
            $remaining -= $rhours*$sec_hour;
        }
        $rmins = ($remaining/$sec_min > 0) ? floor($remaining/$sec_min) : '0';
        if ($rmins > 0 && $show_mins) {
            if ($remainingString=='')
                $remainingString .= '<'.$rmins;
            else
                $remainingString .= ' '.$rmins.$min;
        }
        return $remainingString;
    }
}

