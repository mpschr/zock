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

    /**
     * @return array|string
     */
    public function generateEventInfo(){
        global $cont, $settings;

        $id = $this->id;
        $dbinfo = array();
        $e = $dbinfo[0];
        $matchnb = $e['match_nb'];
        $point=1;
        $alphabet = array('0', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k');
        $subpoint=1;


        $header = $lang['eventinfo_rules'].
            '<br/><br/><a href="javascript: changeFloatingLayer(\''.$id.'_stake\')">'.$lang['eventinfo_stake'].'</a> |
		<a href="javascript: changeFloatingLayer(\''.$id.'_tips\')">'.$lang['mytips_tips'].'</a> | ';
        if($e['stake_mode']=='permatch') $header .= '<a href="javascript: changeFloatingLayer(\''.$id.'_gain\')">'.$lang['ranking_gain'].'</a> | ';
        if($e['stake_mode']!='none') $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_pointsjp\')">'.$lang['ranking_points'].' & '.$lang['overview_jackpot'].'</a> | ';
        if($e['stake_mode']=='none') $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_pointsjp\')">'.$lang['ranking_points'].'</a> | ';
        $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_end\')">'.$lang['eventinfo_finalaccount'].'</a>';


        //STAKE
        $to_sub1 = array($e['stake'].' '.$e['currency']);
        $to_sub2 = array($matchnb, $e['stake']*$matchnb.' '.$e['currency']);

        $ei['stake'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>';

        switch($e['stake_mode']){
            case 'none':
                $ei['stake'] .= '<td>'.$lang['eventinfo_nostake'].'</td>';
                break;
            case 'fix':
                $ei['stake'] .= '<td>'.substitute($lang['eventinfo_fixstake'], $to_sub1).'</td>';
                break;
            case 'permatch':
                $ei['stake'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_stakepermatch'], $to_sub1).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_staketotal'], $to_sub2).'</td>';
        }
        $ei['stake'] .= '</tr></table>';

        //TIPS
        //prep
        $to_sub_toto = array ($lang['eventinfo_beton_toto_1'].', ', $lang['eventinfo_beton_toto_2'], ' & '.$lang['eventinfo_beton_toto_x']);
        if($e['bet_on'] != 'toto') array_pop($to_sub_toto);
        $to_sub_matchtypes = array ($lang['eventinfo_tournamentmatches'], $lang['eventinfo_komatches']);

        $subpoint = 1;
        $ei['tips'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>';
        if($e['bet_on']=='results') $ei['tips'] .= '<td>'.$lang['eventinfo_precise_tip'].'</td>';
        if($e['bet_on']=='toto') $ei['tips'] .= '<td>'.substitute($lang['eventinfo_beton_toto'], $to_sub_toto).'</td>';
        $ei['tips'] .= '</tr><tr>
			<td/>';
        if($e['ko_matches']=='no'){
            $ei['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_inallmatches'].' '.substitute($lang['eventinfo_allpossible'],
                Array($lang['general_victory'], $lang['general_defeat'], $lang['general_tie'])).'</td>
				</tr><tr>';
        }elseif($e['ko_matches']=='only' && $e['enable_tie']=='no'){
            $ei['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_inallmatches'].' '.substitute($lang['eventinfo_tieno'],
                array($lang['general_victory'], $lang['general_defeat']));
            $ei['tips'] .= ' '.$lang['eventinfo_afterpenalties'].' ';
            $ei['tips'] .= ($e['ap_score'] == 'addone') ?
                $lang['eventinfo_afterpenalties_one'] :
                $lang['eventinfo_afterpenalties_all'];
            $ei['tips'] .= '</td>
				</tr><tr>';
        }elseif($e['ko_matches']=='only' && $e['enable_tie']=='yes'){
            $ei['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_inallmatches'].' '.substitute($lang['eventinfo_allpossible'],
                array($lang['general_victory'], $lang['general_defeat'], $lang['general_tie'])).' '.substitute($lang['eventinfo_tietough'], array()).'</td>
				</tr><tr>';
        }elseif($e['ko_matches']=='yes' && $e['enable_tie']=='yes'){
            $ei['tips'] .= '<td>'.substitute($lang['eventinfo_matches_both_types'], $to_sub_matchtypes).'</td></tr><tr>
					<td/><td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_tournamentmatches']
                .' '.substitute($lang['eventinfo_allpossible'],	array($lang['general_victory'], $lang['general_defeat'],
                $lang['general_tie'])).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_komatches']
                .' '.substitute($lang['eventinfo_allpossible'],	array($lang['general_victory'], $lang['general_defeat'],
                $lang['general_tie'])).' '.substitute($lang['eventinfo_tietough'], ' '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_komatches']).'</td>
				</tr><tr>';

        }elseif($e['ko_matches']=='yes' && $e['enable_tie']=='no'){
            $ei['tips'] .= '<td>'.substitute($lang['eventinfo_matches_both_types'], $to_sub_matchtypes).'</td></tr><tr>
					<td/><td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_tournamentmatches']
                .' '.substitute($lang['eventinfo_allpossible'],	array($lang['general_victory'], $lang['general_defeat'],
                $lang['general_tie'])).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_komatches']
                .' '.substitute($lang['eventinfo_tieno'], array($lang['general_victory'], $lang['general_defeat']));
            $ei['tips'] .= ' '.$lang['eventinfo_afterpenalties'].' ';
            $ei['tips'] .= ($e['ap_score'] == 'addone') ?
                $lang['eventinfo_afterpenalties_one'] :
                $lang['eventinfo_afterpenalties_all'];
            $ei['tips'] .='</td>
				</tr><tr>';
        }

        $bu = preg_split('/:/', $e['bet_until']);
        if ($bu[0] > 1){
            $plural = 's';
        }
        if ($bu[1] == 'm') $bu[1] = $lang['general_minute'.$plural];
        if ($bu[1] == 'h') $bu[1] = $lang['general_hour'.$plural];
        if ($bu[1] == 'd') $bu[1] = $lang['general_day'.$plural];
        $before = ($bu[2] == 'm') ? $lang['eventinfo_match'] : $lang['eventinfo_thefirstmatch'];
        $to_sub_betuntil = Array($bu[0].' '.$bu[1], $before);
        $ei['tips'] .= '<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.substitute($lang['eventinfo_bet_until'], $to_sub_betuntil).' '.$lang['eventinfo_deadline_toolate'].'</td>
		</tr><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.$lang['eventinfo_overview'].'<td>
		</tr></table>';

        //GAIN
        if ($e['stake_mode'] == 'permatch'){
            $subpoint = 1;
            $to_sub3 = array($lang['general_victory'], $lang['general_tie'], $lang['general_defeat'], $e['stake'].' '.$e['currency']);
            $ei['gain'] = $header.'<table class="eventinfo"><tr>
				<td class="topalign"><b>'.$point++.'.</b></td>
				<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_gain_correcttip'], $to_sub1).'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_gain_correcttips'].'</td>
			</tr><tr>';
            if ($e['stake_back'] == 'yes'){
                $ei['gain'] .= '<td class="topalign"><b>'.$point++.'.<b/></td>
				<td>'.$lang['eventinfo_gain_nobodycorrect'].' '.substitute($lang['eventinfo_gain_stakeback'], $to_sub3).'</td>
			</tr></table>';
            }else{
                $ei['gain'] .= '<td class="topalign"><b>'.$point++.'.<b/></td>
				<td>'.$lang['eventinfo_gain_nobodycorrect'].' '.$lang['eventinfo_gain_tojackpot'].'</td>
			</tr></table>';
            }
        }

        //POINTS & JACKPOT
        $subpoint = 1;
        $to_sub5 = ($e['jp_fraction_or_fix'] == 'fix') ? $e['jp_fix'] : ($e['jp_fraction']*100).'% ('.$lang['eventinfo_jackpot_floored'].')';
        $to_sub_exp = array($e['jp_distr_exp_value'], ($e['jp_distr_exp_value']*100).'%');
        $ei['pointsjp'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.$lang['eventinfo_points'].'</td>
		</tr><tr>';
        if($e['p_correct'] != NULL){
            $ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_correct'], $e['p_correct']).'</td>
		</tr><tr>';
        }
        if($e['p_diff'] != NULL){
            $ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_diff'], array($lang['general_eg'], $e['p_diff'])).'</td>
		</tr><tr>';
        }
        if($e['p_almost'] != NULL){
            $ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_almost'], array($lang['general_eg'], $e['p_almost'])).'</td>
		</tr><tr>';
        }
        if($e['p_wrong'] != NULL){
            $ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_wrong'], $e['p_wrong']).'</td>
		</tr>';
        }
        if($e['stake_mode']!='none'){
            $subpoint = 1;
            $ei['pointsjp'].='<tr><td class="topalign"><b>'.$point++.'.<b/></td>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_jackpot'].'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_jackpot_samerank'].'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_jackpot_distributeon'], $to_sub5);
            if($e['jp_distr_algorithm']=='exp'){
                $ei['pointsjp'] .= substitute($lang['eventinfo_jackpot_expformula'], $to_sub_exp);
            }elseif($e['jp_distr_algorithm']=='lin'){
                $ei['pointsjp'] .= $lang['eventinfo_jackpot_linformula'];
            }else{
                $ei['pointsjp'] .= $lang['eventinfo_jackpot_fixformula'];
                $percents = preg_split('/:/',$e['jp_distr_fix_shares']);
                array_pop($percents);
                $rankcounter=1;
                foreach ($percents as $p){
                    $rankcounter++;
                    $ei['pointsjp'] .= '<br/>'.$rankcounter.'. '.$lang['ranking_rank']
                        .': '.$p.'%';
                }
            }
            $ei['pintsjp'].= '</td>
			</tr></table>';
        }else{
            $ei['pointsjp'] .= '</table>';
        }

        $to_sub7 = array($lang['general_bettingOffice'].' '.$settings['name']);
        $ei['end'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>';
        if($e['stake_mode'] == 'permatch'){
            $ei['end'] .= $lang['eventinfo_finalaccount_gainplusjp'];
        }elseif($e['stake_mode'] == 'fix'){
            $ei['end'] .= $lang['eventinfo_finalaccount_jp'];
        }else{
            $ei['end'] .= $lang['eventinfo_finalaccount_points'];
        }
        $ei['end'] .= '</td>
		</tr>';
        if(file_exists('data/bo_img/seal@thumb')){ $ei['end'] .=  '</tr>
			<td/>
			<td>'.substitute($lang['eventinfo_sealofapproval'], $to_sub7).'<br/><img padding="10px;"  src="data/bo_img/seal@thumb"/></td>
		</tr>';
        }
        $ei['end'] .= '</table>';

        return $ei;

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
