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

class Ranking {

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var string
     */
    protected $match_grouping;

    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////


    /**
     * @param Event $event
     * @param string $match_grouping
     * @throws Exception
     */
    public function __construct($event,$match_grouping=null) {
        $this->event = $event;

        if (!is_null($match_grouping)) {
            $this->match_grouping = $match_grouping;
        } else {
            $this->match_grouping = $event->getMatchGrouping();
        }
    }


    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////


    private function matchQuery($select,$until="") {
        $event = $this->event;
        $ev = $event->getId();
        $score_field = ($this->event->getScoreInputType() == 'results') ? 'score_h' : 'score';

        $query = "SELECT ".$select." FROM ".PFIX."_event_".$ev." WHERE ".$score_field." IS NOT NULL ORDER BY time ASC;";
        if($until!="") {
            $u = preg_split('/:/',$until);
            if($u[0] == 'matchday_id')
                $query = "SELECT ".$select." FROM ".PFIX."_event_".$ev."
			WHERE matchday_id <= '".$u[1]."'
			AND ".$score_field." IS NOT NULL
			ORDER BY matchday_id ASC, time ASC;";
            elseif($u[0] == 'date')
                $query = "SELECT ".$select.",FROM_UNIXTIME(time, '%Y%m%d') as vdate
			FROM ".PFIX."_event_".$ev."
			WHERE FROM_UNIXTIME(time, '%Y%m%d') <= ".$u[1]."
			AND ".$score_field." IS NOT NULL
			ORDER BY time ASC ;";
            elseif($u[0] == 'match')
                $query = "SELECT ".$select." FROM ".PFIX."_event_".$ev."
			WHERE ".$score_field." IS NOT NULL
			ORDER BY time ASC LIMIT 0, ".$u[1].";";
        }
        return $query;
    }

    /**
     * @param string $until
     * @internal param $ev
     * @return array
     */

    function getRankingDetails ($until=""){

        global $db;


//select from db ordered by time and only matches with results!
//*simulation*/ /*

        $event = $this->event;
        $ev = $event->getId();
        $score_field = ($this->event->getScoreInputType() == 'results') ? 'score_h' : 'score';

        $query = $this->matchQuery("*",$until);
        $pastmatches =  $db->query($query);
        $jackpot = 0;
        $evUsNb = eventUserNumber($ev);
        $points = array();
        $correct = array();
        $almost = array();
        $diff = array();
        $wrong = array();
        $money = array();
        foreach($pastmatches as $pm){
            foreach($pm as $label => $info){
                // accumulate points and money for each user and the jackpot
                if(substr($label, -7) == '_points') {
                    $nick = substr($label,0,-7);
                    $points[$nick] += $info;
                    if($info == $event->getPCorrect()) $correct[$nick]++;
                    if($info == $event->getPDiff()) $diff[$nick]++;
                    elseif($info == $event->getPAlmost()) $almost[$nick]++;
                    elseif($info == $event->getPWrong()) $wrong[$nick]++;
                    if(!isset($correct[$nick])) $correct[$nick] = 0;
                    if(!isset($diff[$nick])) $diff[$nick] = 0;
                    if(!isset($almost[$nick])) $almost[$nick] = 0;
                    if(!isset($wrong[$nick])) $wrong[$nick] = 0;
                }
                if(substr($label, -6) == '_money') $money[substr($label, 0, -6)] += $info;
                if($label == 'jackpot') $jackpot += $info;
                // evaluate rank (everytime overwritten, last is acutal);
                if(substr($label, -8) == '_ranking') $rank[substr($label, 0, -8)] = $info;
            }
        }
//*simulation*/ $evUsNb = 10;
//*simulation*/ $jackpot = 189;
//*simulation*/ $points = array( 50,  99, 10, 0, 5, 45, 12, 15, 66, 50);
//*simulation*/ $money = array( 50,  99, 10, 0, 5, 45, 12, 15, 66, 50);
//*simulation*/ $rank = array( 3,  1, 8, 10, 9, 5, 7, 6, 2, 3);


        // get further info for the event
        $event_info = $db->query("SELECT * FROM ".PFIX."_events WHERE id='".$ev."'");
        if($event_info[0]['stake_mode']=='fix') $jackpot = $evUsNb*$event_info[0]['stake'];

        if($event_info[0]['extra_stake'] > 0){
            if ($event_info[0]['extra_stake_purpose'] == 'jackpot') {
                $jackpot += $event_info[0]['extra_stake']*$evUsNb;
            }
        }
        //TODO: exra_stake to winner


//*simulation*/ $event_info[0]['jp_fraction_or_fix'] = 'fix';
//*simulation*/ $event_info[0]['jp_fraction'] = 1;
//*simulation*/ $event_info[0]['jp_fix'] = 5;
//*simulation*/ $event_info[0]['jp_distr_algorithm'] = 'fix';
//*simulation*/ $event_info[0]['jp_distr_exp_value'] = '0.6';
//*simulation*/ $event_info[0]['jp_distr_fix_shares'] = '0.5:0.3:0.1:0.07:0.03';

        //estimate the number of players sharing the jackpot: either fraction or fix number
        $jackpotters = ($event_info[0]['jp_fraction_or_fix'] == 'fraction') ?
            floor($evUsNb*$event_info[0]['jp_fraction']) :
            $event_info[0]['jp_fix'];
        //make a linear,exponential or fix distribution: divide jackpot into tiny pieces for distribution
        //!fix is only possible if also a fix number of jackpot sharers are set!

        $round = $event_info[0]['round'];
        $reciprocal = 1/$round;
        $jackpots[] = array();
        $counter = 0;
        $jackpotparts = 0;

        switch ($event_info['0']['jp_distr_algorithm']){
            case 'lin': //linear distribution
                for($j = $jackpotters; $j > 0; $j--) $jackpotparts += $j;
                $singlepart = $jackpot/$jackpotparts;
                for($j = $jackpotters; $j > 0; $j--){
                    $counter++;
                    $jackpots[$counter] = (round($reciprocal*($singlepart * $j))/$reciprocal);
                }
                break;
            case 'exp': //exponential distribution
                while(true){
                    for($j = $jackpotters; $j > 0; $j--){
                        $counter++;
                        $jackpots[$counter] += (round($reciprocal*($event_info[0]['jp_distr_exp_value']*($jackpot-array_sum($jackpots))))/$reciprocal);
                        $difference = ($jackpot-array_sum($jackpots)) - $round;
                        //because $difference is a floating point binary, we have to check for a range, not an absolute value!
                        if ($difference <= 0.001){
                            if ($difference > -0.001)
                                if($counter!=$jackpotters) {
                                    $jackpots[$counter+1] += $round;
                                }else{
                                    break;
                                }
                            if ($counter == $jackpotters) break 2;
                        }
                    }
                    $counter = 0;
                }
                break; //end of case 'exp'
            case 'fix': //fix shares
                $fix_shares = preg_split('/:/', $event_info[0]['jp_distr_fix_shares']);
                for($j = $jackpotters; $j > 0; $j--){
                    $counter++;
                    $jackpots[$counter] = (round($reciprocal*(($fix_shares[$counter-1]/100)*$jackpot))/$reciprocal);
                }
                break;
        }


        if ($until=="" || $until == "none") {
            global $events_test;
            $event = $events_test->getEventById($ev);
            $bets = $event->getBetsContainer()->getBets();
            $userscl = new UserCollection();
            $usersar = $userscl->getEventUsers($event);
            foreach ($bets as $bet) {
                if ($bet instanceof Question) {
                    /* @var $bet Question  */

                    if ($bet->getAnswer() == '')
                        continue;

                    foreach ($usersar as $u) {
                        /* @var $u User */
                        $p = $bet->getUserPoints($u->getId());
                        $points[$u->getId()] += $p;
                    }
                }
            }
        }

        arsort($points);
        $counter = 1;
        $ranker = 1;
        $rank = array();
        foreach ($points as $id => $pt){
            $points_p[] = $pt;
            $points_u[] = $id;
        }
        foreach ($points_p as $index => $pt){
            $rank[$points_u[$index]] = $ranker;
            $counter++;
            if ($points_p[$counter-1] != $points_p[$counter-2])
                $ranker = $counter;
        }

        //gather info for check if multiple users on the same rank
        //and divde their jackpots equally plus forward the undividable part;
        $rank_quantity = array();
        foreach($rank as $r) $rank_quantity[$r]++;
        for($p=1; $p<=$jackpotters; $p++){
            if ($rank_quantity[$p] > 1){
                $accumulate = 0;
                for($q = $p; $q < $p + $rank_quantity[$p]; $q++) {
                    if ($p<=$q) $accumulate += $jackpots[$q]; //add jackpots of the empty ranks to concerning one
                    if ($q > $p) unset($jackpots[$q]);  //no jackpot for the ranks where's *nobody on*
                }
                $jackpots[$p] = floor(($reciprocal* $accumulate) / $rank_quantity[$p]) /$reciprocal;
                $undividable = $accumulate - $rank_quantity[$p]*$jackpots[$p];
                if ($jackpots[$p] != pos($jackpots)) while (next($jackpots) != $jackpots[$p]);
                while($undividable > 0.0001){
                    $next = next($jackpots);
                    if(array_search($next, $jackpots) != $p){
                        $player = (int)array_search($next, $jackpots);
                        $jackpots[$player] = (float)$jackpots[$player] + (float)$undividable;
                        $undividable = 0;
                    }
                }
            }
        }

        $info = Array();
        $info['money'] = $money;
        $info['jackpots'] = $jackpots;
        $info['r_quant'] = $rank_quantity;
        $info['rank'] = $rank;
        $info['points'] = $points;
        $info['correct'] = $correct;
        $info['diff'] = $diff;
        $info['almost'] = $almost;
        $info['wrong'] = $wrong;
        $info['pastmatches'] = sizeof($pastmatches);
        $info['totalmatches'] = $db->row_count("SELECT id FROM ".PFIX."_event_".$ev."; ");
        return $info;
    }

    function getRanking ($until="") {
        global $db;

        $select = "";
        foreach ($this->event->toArray($this->event->getUsersApproved()) as $id) {
            if (strlen($select) > 0) {
                $select .= ", ";
            }
            $select .= "SUM(".$id."_points) AS '".$id."'";
        }

        $query = $this->matchQuery($select,$until);

        $ranking = $db->query($query)[0];

        asort($ranking);

        return($ranking);


        //print_r($ranking);
    }


    /////////////////////////////////////////////////
    // GETTERS & SETTERS
    /////////////////////////////////////////////////


    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $match_grouping
     */
    public function setMatchGrouping($match_grouping)
    {
        $this->match_grouping = $match_grouping;
    }

    /**
     * @return string
     */
    public function getMatchGrouping()
    {
        return $this->match_grouping;
    }

}

