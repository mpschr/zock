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
     * @var Event
     */
    protected $event;

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
     * @param Event $event
     * @throws Exception
     */
    public function __construct($dict,$event) {
        if (sizeof($dict)==0)
            throw new Exception("empty match");

        $this->event = $event;
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
     * @return array|\unixtime
     */
    function getDueDate()
    {
        global $db;
        $t = $this->time;
        $betuntilRaw = preg_split('/:/',$this->event->getBetUntil());
        $min = 60;
        $hour = $min*60;
        $day = $hour*24;

        /* @var $betuntil unixtime */
        /* @var $before unixtime */

        if($betuntilRaw[2]=='t'){
            $data=$db->query("SELECT time FROM ".PFIX."_event_".$this->event->getId()." ORDER BY time,matchday_id,matchday ASC LIMIT 1;");
            $betuntil = $data[0]['time'];
        } else {
            $betuntil = $this->time;
        }
        if($betuntilRaw[1]=='m'){ $before = $min * $betuntilRaw[0];}
        elseif($betuntilRaw[1]=='h'){ $before = $hour * $betuntilRaw[0];}
        elseif($betuntilRaw[1]=='d'){
            $before = $day * ($betuntilRaw[0]-1);
        }

        return $betuntil - $before;
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
        return 'm'.$this->id;
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

    /**
     * @param $somebet
     * @return int
     */
    public function getSameBets($somebet)
    {
        $samebets=-1;
        $userstring = $this->event->getUsersApproved();
        $users = preg_split('/:/',$userstring);
        foreach ($users as $u) {
            if ($this->getBet($u)==$somebet) {
                $samebets++;
            }
        }
        return $samebets;
    }

    /**
     * @return string
     */
    public function getTendency()
    {
          $toto0 = 0;
          $toto1 = 0;
          $toto2 = 0;
          $totoX = 0;
          $userstring = $this->event->getUsersApproved();
          $users = preg_split('/:/',$userstring);

          foreach ($users as $u){
              if ($u == '')
                  continue;
              if ($this->event->getBetOn()=="results") {
                  $home = $u.'_h';
                  $visitor = $u.'_v';
                  if($this->$home == '' || $this->$visitor == ''){
                      ++$toto0;
                  }else if($this->$home > $this->$visitor){
                         ++$toto1;
                  }else if($this->$home < $this->$visitor){
                     ++$toto2;
                  }else{
                     ++$totoX;
                  }
              }
              elseif ($this->event->getBetOn()=="toto") {
                if ($this->toto == 0) ++$toto0;
                elseif ($this->toto == 1) ++$toto1;
                elseif ($this->toto == 2) ++$toto2;
                elseif ($this->toto == 3) ++$totoX;
              }
          }
          $toto_all= count($users) - $toto0;

          if($toto1>0){$toto_trend1=round($toto1/$toto_all*100);}else{$toto_trend1=0;}
          if($totoX>0){ $toto_trendX=round($totoX/$toto_all*100);}else{$toto_trendX=0;}
          if($toto2>0){ $toto_trend2=round($toto2/$toto_all*100);}else{$toto_trend2=0;}
          return $toto_trend1.' : '.$toto_trendX.' : '.$toto_trend2;
    }

    /**
     * @param int $user
     * @return string
     */
    public function getBet($user)
    {
        $bet = "";
        if ($this->event->getBetOn()=="results") {
            $home = $user.'_h';
            $visitor = $user.'_v';
            $bet .= $this->$home;
            $bet .= ' : ';
            $bet .= $this->$visitor;
            return $bet;
        }
        elseif ($this->event->getBetOn()=="toto") {
            return $this->$user.'_h';
        }
        return "";
    }

    /**
     * @return string
     */
    public function getResult()
    {
        $result = "";

        if ($this->event->getBetOn()=="results") {
            $home = 'score_h';
            $visitor = 'score_v';

            if ($this->$home == null)
                return '';

            $result .= $this->$home;
            $result .= ' : ';
            $result .= $this->$visitor;
            return $result;
        }
        elseif ($this->event->getBetOn()=="toto") {
            return $this->score;
        }
    }

    /**
     * @param string $result
     * @param string $special
     * @return bool
     */
    public function setResult($result,$special='')
    {
        if ($this->event->getBetOn()=="results") {
            $ressplit = preg_split('/ : /',$result);
            $this->score_h = $ressplit[0];
            $this->score_v = $ressplit[1];
            if ($special != '')
                $this->score_special = $special;
        } else {
            $this->score = $result;
            if ($special != '')
                $this->score_special = $special;
        }
        return $this->updatePointsAndMoney();
    }

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
        $labelclass = '';
        $show_mins = true;


        $now = time();
        $betuntil = $this->getDueDate();
        $remaining = $betuntil - $now;
        if ($remaining < 0)
            return 'passed';

        $remainingString = '';
        //days
        $rdays = ($remaining/$sec_day > 0) ? floor($remaining/$sec_day) : '0';
        if ($rdays > 0) {
            $remainingString .= $rdays.$day;
            $remaining -= $rdays*$sec_day;
            $show_mins = false;
            $labelclass = 'label-info';
        }
        //hours
        $rhours = ($remaining/$sec_hour > 0) ? floor($remaining/$sec_hour) : '0';
        if ($rhours > 0) {
            if ($show_mins) {
                $labelclass = 'label-warning';
            }
            $remainingString .= ' '.$rhours.$hour;
            $remaining -= $rhours*$sec_hour;
        }
        //mins
        $rmins = ($remaining/$sec_min > 0) ? floor($remaining/$sec_min) : '0';
        if ($rmins > 0 && $show_mins) {
            if ($remainingString=='') {
                $labelclass = 'label-important';
                $remainingString .= '<'.$rmins.$min;
            } else {
                $remainingString .= ' '.$rmins.$min;
            }
        }
        return '<span class="label '.$labelclass.'">'.$remainingString.'</span>';
    }


    /**
     * @param int $user
     * @param $bet
     * @internal param string $userbet
     * @return void
     */
    public function setBet($user,$bet)
    {
        global $db;

        if (time()>$this->getDueDate())
            return false;

        if ($this->event->getBetOn()=='results') {
            $betparts = preg_split('/:/',$bet);
            if (sizeof($betparts) != 2)
                return false;
            if (!(is_numeric($betparts[0]) && is_numeric($betparts[1])))
                return false;

            $home = $user."_h";
            $visitor = $user."_v";

            if ($betparts[0] == $this->$home && $betparts[1] == $this->$visitor)
                return true;

            $query = "UPDATE ".PFIX."_event_".$this->event->getId()." SET
                        ".$home." = '".$betparts[0]."', ".$visitor." = '".$betparts[1]."'
                        WHERE id = ".$this->id.";";
            $queryres = $db->query($query);
            if ($queryres) {
                $this->$home = $betparts[0];
                $this->$visitor = $betparts[1];
                return true;
            }
        }
        return false;
    }

    /**
     * @return void
     */
    public function getCorrectBets()
    {
        $correctBets = 0;
        $users = preg_split('/:/',$this->event->getUsersApproved());
        foreach ($users as $u) {

            if ($u == '') continue;

            if ($this->getBet($u) == $this->getResult())
                $correctBets++;
        }
        return $correctBets;
    }

    /**
     * @param $user int
     * @return bool
     */
    public function isCorrectBet($user)
    {

        if ($this->getBet($user) == $this->getResult()) {
            return true;
        }else {
            return false;

        }
    }

    /**
     * @param $user int
     * @return bool
     */
    public function isCorrectDiff($user)
    {
        $h = $user.'_h';
        $v = $user.'_v';

        if ($this->getScoreH() == "" || $this->$h == '' && $this->$v == '')
            return false;

        if ($this->getScoreH() - $this->getScoreV() ==
            $this->$h - $this->$v) {
            return true;
        }else {
            return false;

        }
    }

    /**
     * @param $user int
     * @return bool
     */
    public function isCorrectWinner($user)
    {
        $h = $user.'_h';
        $v = $user.'_v';

        if ($this->getScoreH() == "" || $this->$h == '' && $this->$v == '')
            return false;

        if (( $this->getScoreH()==$this->getScoreV() &&
                $this->$h==$this->$v)
            || (($this->getScoreH() > $this->getScoreV()) && ($this->$h > $this->$v))
            || (($this->getScoreH() < $this->getScoreV()) && ($this->$h < $this->$v))) {
            return true;
        }else {
            return false;

        }
    }

    /**
     * @param $user int
     */
    public function getUserPoints($user)
    {
        $userpoints = $user.'_points';
        return $this->$userpoints;
    }

    /**
     * @param $user int
     */
    public function getMoney($user)
    {
        $usermoney = $user.'_money';
        return $this->$usermoney;
    }

    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////


    /**
     * @return bool
     */
    public function updatePointsAndMoney() {

        if ($this->score_h == '')
            return false;

        global $events, $db;

        //prepare data for a lot of calculating

        //=> which and how many users for this event?
        $evUsers = explode(':', $this->event->getUsersApproved());
        array_pop ($evUsers);
        $nb = sizeof($evUsers);


        //=>define which success yields how many points.. (to be adjustable in a later version)
        $correct = $this->event->getPCorrect();
        $diff = $this->event->getPDiff();
        $almost = $this->event->getPAlmost();
        $wrong = $this->event->getPWrong();

        //how many tipped CORRECT (1), DIFF/ALMOST (0) correct, WRONG (-1)
        //+ creating array with success value ($good, indicated in brackets) for each user
        //+ creating array with pionts for each user
        $nbCorrect = $nbDiff = $nbAlmost = $nbWrong = 0;
        $success = array();
        foreach($evUsers as $p){
//            if($this->event->getBetOn()=='results' && $this->event->getScoreInputType()=='results'){

            $userpoints = $p.'_points';

            if($this->isCorrectBet($p)){
                //correct:
                $success[$p] = 1;
                $nbCorrect++;
                $this->$userpoints = $correct;
            }elseif($this->isCorrectDiff($p)){
                //diff:
                $success[$p] = 0;
                $nbAlmost++;
                $this->$userpoints = $diff;
            }elseif($this->isCorrectWinner($p)){
                //almost:
                $success[$p] = 0;
                $nbAlmost++;
                $this->$userpoints = $almost;
                //wrong:
            } else {
                $nbWrong++;
                $success[$p] = -1;
                $this->$userpoints = $wrong;
            }
        }

        /*
        //======DEBUG
        $nb=80;
        $nbCorrect = 3;
        $evData['round'] = 0.05;
        */
        if($this->event->getStakeMode()=='permatch'){
            //money business
            //=>how much gets everybody and is going into the jackpot?
            $factor = (1/$this->event->getRound());
            $totalstake = $nb*$this->event->getStake();
            $this->jackpot = $totalstake;
            if($nbCorrect>0){
                $exact = floor(($factor*$totalstake)/$nbCorrect)/$factor;
                $this->jackpot = $totalstake -($exact*$nbCorrect);
                foreach($evUsers as $p) {
                    $usermoney = $p.'_money';
                    $this->$usermoney = ($success[$p] == 1) ? $exact : 0;
                }
            }elseif($this->event->getStakeBack()=='yes'){
                foreach($evUsers as $p) {
                    $usermoney = $p.'_money';
                    $this->$usermoney = ($success[$p] == 0) ? $this->event->getStake() : 0;
                    $this->jackpot -= $this->$usermoney;
                }
            }else{
                foreach($evUsers as $p) {
                    $usermoney = $p.'_money';
                    $this->$usermoney = 0;
                }
            }
            // avoid stupid php bug:
            if ($this->jackpot < $this->event->getRound())
                $this->jackpot = 0;
        }else{
            foreach($evUsers as $p) {
                $usermoney = $p.'_money';
                $this->$usermoney = 0;
            }
            $this->jackpot = 0;
        }
        return $this->saveUpdatedBet();
    }


    /**
     * @return bool
     */
    public function saveUpdatedBet() {

        global $db;

        if($this->event->getScoreInputType()=='results'){
            $a= $this->getScoreH();
            $b= $this->getScoreV();
            $set = " SET score_h = '".$a."',
						score_v = '".$b."',";
        }else{
            $a = $this->score;
            $set = " SET score = '".$a."', ";
        }

        $evUsers = explode(':', $this->event->getUsersApproved());
        array_pop ($evUsers);

        //query preparation
        //=>1st part
        $query_changes = "UPDATE ".PFIX."_event_".$this->event->id
            .$set.
            "score_special = '".$this->getScoreSpecial()."'";
        //=> jackpot
        $query_changes .= ", jackpot = '".$this->jackpot."'";

        //=>points
        foreach($evUsers as $u) {
            $userpoints = $u.'_points';
            $query_changes .= ", ".$u."_points = '".$this->$userpoints."'";
        }

        //=>money
        foreach($evUsers as $u) {
            $usermoney = $u.'_money';
            $query_changes .= ", ".$u."_money = '".$this->$usermoney."' ";
        }

        /*//=>ranks
        foreach($evUsers as $u) {
            $userranking = $u.'_ranking';
            $query_changes .= ", ".$u."_ranking = '".$this->$userranking."' ";
        }*/

        //=>which row(match-id dependent)
        $query_changes .= "WHERE id = '".$this->id."';";

        //finally update
        //print_r($query_changes);

        $done = $db->query($query_changes);
        return $done;
    }

}
