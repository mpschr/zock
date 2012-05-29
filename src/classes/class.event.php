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


class Event {

    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////
    
    /**
    * Id for the language
    * @var int 
    */
    public $id          = NULL;


    /**
    * Id for the language
    * @var int 
    */
    public $name          = "";


    /**
     * @var unixtime
     */
    protected $deadline;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var float
     */
    protected $stake;

    /**
     * @var string
     */
    protected $stake_mode;

    /**
     * @var int
     */
    protected $match_nb;

    /**
     * @var int
     */
    protected $stake_back;

    /**
     * @var float
     */
    protected $round;

    /**
     * @var string
     */
    protected $bet_on;

    /**
     * @var string
     */
    protected $score_input_type;

    /**
     * @var unixtime
     */
    protected $bet_until;

    /**
     * @var int
     */
    protected $p_correct;

    /**
     * @var int
     */
    protected $p_almost;

    /**
     * @var int
     */
    protected $p_wrong;

    /**
     * @var string
     */
    protected $jp_fraction_or_fix;

    /**
     * @var float
     */
    protected $jp_fraction;

    /**
     * @var int
     */
    protected $jp_fix;

    /**
     * @var string
     */
    protected $jp_distr_algorithm;


    /**
     * @var string
     */
    protected $jp_distr_exp_value;

    /**
     * @var string
     */
    protected $jp_distr_fix_shares;

    /**
     * @var string
     */
    protected $users_approved;

    /**
     * @var string
     */
    protected $users_waiting;

     /**
     * @var string
     */
    protected $users_denied;


    /**
     * @var int
     */
    protected $public;

    /**
     * @var int
     */
    protected $active;

    /**
     * @var string
     */
    protected $ko_matches;

    /**
     * @var string
     */
    protected $enable_tie;

    /**
     * @var string
     */
    protected $ap_score;

    /**
     * @var bool
     */
    protected $finished;


    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////


    /**
     * @param int $id
     * @throws Exception
     */
     public function __construct($id) {
        global $db;
        $raw = $db->query("SELECT * FROM ".PFIX."_events WHERE `id` LIKE '$id'");

        if (sizeof($raw)==0)
            throw new Exception("No event with id $id in database!");

        $eventDetails =     $raw[0];
        $this->id =         $eventDetails['id'];
        $this->name =       $eventDetails['name'];
        unset($eventDetails['id']);
        unset($eventDetails['name']);

        foreach ($eventDetails as $key => $value) {
            $this->$key = $value;
        }
    }

    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////

    /**
     * @param int $userID
     * @return bool
     */
    public function eventHasApprovedUser($userID) {

        $users = preg_split('/:/',$this->users_approved);
        if (in_array($userID,$users))
            return true;
        else
            return false;

    }

    /**
     * @param int $userID
     * @return bool
     */
    public function eventHasWaitingUser($userID) {

        $users = preg_split('/:/',$this->users_waiting);
        if (in_array($userID,$users))
            return true;
        else
            return false;

    }


    /**
     * @param int $userID
     * @return bool
     */
    public function eventHasDeniedUser($userID) {

        $users = preg_split('/:/',$this->users_denied);
        if (in_array($userID,$users))
            return true;
        else
            return false;

    }

    /**
     * @return array
     * @throws Exception
     */
    public function getBetsAndResults() {

        $results = array();
        throw new Exception("Not yet implemented");
        return $results;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getQuestions() {

        $results = array();
        throw new Exception("Not yet implemented");
        return $results;
    }

    /////////////////////////////////////////////////
    // GETTERS AND SETTERS
    /////////////////////////////////////////////////

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $ap_score
     */
    public function setApScore($ap_score)
    {
        $this->ap_score = $ap_score;
    }

    /**
     * @return string
     */
    public function getApScore()
    {
        return $this->ap_score;
    }

    /**
     * @param string $bet_on
     */
    public function setBetOn($bet_on)
    {
        $this->bet_on = $bet_on;
    }

    /**
     * @return string
     */
    public function getBetOn()
    {
        return $this->bet_on;
    }

    /**
     * @param \unixtime $bet_until
     */
    public function setBetUntil($bet_until)
    {
        $this->bet_until = $bet_until;
    }

    /**
     * @return \unixtime
     */
    public function getBetUntil()
    {
        return $this->bet_until;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \unixtime $deadline
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * @return \unixtime
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * @param string $enable_tie
     */
    public function setEnableTie($enable_tie)
    {
        $this->enable_tie = $enable_tie;
    }

    /**
     * @return string
     */
    public function getEnableTie()
    {
        return $this->enable_tie;
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
     * @param string $jp_distr_algorithm
     */
    public function setJpDistrAlgorithm($jp_distr_algorithm)
    {
        $this->jp_distr_algorithm = $jp_distr_algorithm;
    }

    /**
     * @return string
     */
    public function getJpDistrAlgorithm()
    {
        return $this->jp_distr_algorithm;
    }

    /**
     * @param string $jp_distr_exp_value
     */
    public function setJpDistrExpValue($jp_distr_exp_value)
    {
        $this->jp_distr_exp_value = $jp_distr_exp_value;
    }

    /**
     * @return string
     */
    public function getJpDistrExpValue()
    {
        return $this->jp_distr_exp_value;
    }

    /**
     * @param string $jp_distr_fix_shares
     */
    public function setJpDistrFixShares($jp_distr_fix_shares)
    {
        $this->jp_distr_fix_shares = $jp_distr_fix_shares;
    }

    /**
     * @return string
     */
    public function getJpDistrFixShares()
    {
        return $this->jp_distr_fix_shares;
    }

    /**
     * @param int $jp_fix
     */
    public function setJpFix($jp_fix)
    {
        $this->jp_fix = $jp_fix;
    }

    /**
     * @return int
     */
    public function getJpFix()
    {
        return $this->jp_fix;
    }

    /**
     * @param float $jp_fraction
     */
    public function setJpFraction($jp_fraction)
    {
        $this->jp_fraction = $jp_fraction;
    }

    /**
     * @return float
     */
    public function getJpFraction()
    {
        return $this->jp_fraction;
    }

    /**
     * @param string $jp_fraction_or_fix
     */
    public function setJpFractionOrFix($jp_fraction_or_fix)
    {
        $this->jp_fraction_or_fix = $jp_fraction_or_fix;
    }

    /**
     * @return string
     */
    public function getJpFractionOrFix()
    {
        return $this->jp_fraction_or_fix;
    }

    /**
     * @param string $ko_matches
     */
    public function setKoMatches($ko_matches)
    {
        $this->ko_matches = $ko_matches;
    }

    /**
     * @return string
     */
    public function getKoMatches()
    {
        return $this->ko_matches;
    }

    /**
     * @param int $match_nb
     */
    public function setMatchNb($match_nb)
    {
        $this->match_nb = $match_nb;
    }

    /**
     * @return int
     */
    public function getMatchNb()
    {
        return $this->match_nb;
    }

    /**
     * @param int $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $p_almost
     */
    public function setPAlmost($p_almost)
    {
        $this->p_almost = $p_almost;
    }

    /**
     * @return int
     */
    public function getPAlmost()
    {
        return $this->p_almost;
    }

    /**
     * @param int $p_correct
     */
    public function setPCorrect($p_correct)
    {
        $this->p_correct = $p_correct;
    }

    /**
     * @return int
     */
    public function getPCorrect()
    {
        return $this->p_correct;
    }

    /**
     * @param int $p_wrong
     */
    public function setPWrong($p_wrong)
    {
        $this->p_wrong = $p_wrong;
    }

    /**
     * @return int
     */
    public function getPWrong()
    {
        return $this->p_wrong;
    }

    /**
     * @param int $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * @return int
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param float $round
     */
    public function setRound($round)
    {
        $this->round = $round;
    }

    /**
     * @return float
     */
    public function getRound()
    {
        return $this->round;
    }

    /**
     * @param string $score_input_type
     */
    public function setScoreInputType($score_input_type)
    {
        $this->score_input_type = $score_input_type;
    }

    /**
     * @return string
     */
    public function getScoreInputType()
    {
        return $this->score_input_type;
    }

    /**
     * @param float $stake
     */
    public function setStake($stake)
    {
        $this->stake = $stake;
    }

    /**
     * @return float
     */
    public function getStake()
    {
        return $this->stake;
    }

    /**
     * @param int $stake_back
     */
    public function setStakeBack($stake_back)
    {
        $this->stake_back = $stake_back;
    }

    /**
     * @return int
     */
    public function getStakeBack()
    {
        return $this->stake_back;
    }

    /**
     * @param string $stake_mode
     */
    public function setStakeMode($stake_mode)
    {
        $this->stake_mode = $stake_mode;
    }

    /**
     * @return string
     */
    public function getStakeMode()
    {
        return $this->stake_mode;
    }

    /**
     * @param string $users_approved
     */
    public function setUsersApproved($users_approved)
    {
        $this->users_approved = $users_approved;
    }

    /**
     * @return string
     */
    public function getUsersApproved()
    {
        return $this->users_approved;
    }

    /**
     * @param string $users_denied
     */
    public function setUsersDenied($users_denied)
    {
        $this->users_denied = $users_denied;
    }

    /**
     * @return string
     */
    public function getUsersDenied()
    {
        return $this->users_denied;
    }

    /**
     * @param string $users_waiting
     */
    public function setUsersWaiting($users_waiting)
    {
        $this->users_waiting = $users_waiting;
    }

    /**
     * @return string
     */
    public function getUsersWaiting()
    {
        return $this->users_waiting;
    }

    /**
     * @param boolean $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;
    }

    /**
     * @return boolean
     */
    public function getFinished()
    {
        return $this->finished;
    }


}
