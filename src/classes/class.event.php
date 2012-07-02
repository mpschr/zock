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


include_once('src/classes/class.betsContainer.php');
class Event {

    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////
    
    /**
    * Id for the language
    * @var int 
    */
    public $id = null;


    /**
    * Id for the language
    * @var int 
    */
    public $name = "";

    /**
     * @var BetsContainer $result
     */
    public $betsContainer = null;


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
    protected $p_diff;

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
     * @var string
     */
    protected $users_paid;

    /**
     * @var string
     */
    protected $users_reimbursed;


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

        $eventDetails       = $raw[0];
        $this->id           = $eventDetails['id'];
        $this->name         = $eventDetails['name'];
        $this->betsContainer      = new BetsContainer($this);
        unset($eventDetails['id']);
        unset($eventDetails['name']);

        foreach ($eventDetails as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param $users array
     * @param $whats array
     * @return string
     */
    public function manageUsers($users, $whats) {
        global $db;
        //if the admin aproves/refuses users, the different strings in the db have to be changed
        //=> a string is of the form 1:2:3: (for user 1, 2 & 3)
        $event = $this->id;

        for ($i=0; $i<sizeof($users); $i++) {
            $what = $whats[$i];
            $user = $users[$i];
            if($what=='approve'){
                $this->users_waiting = str_replace($user.':', '', $this->users_waiting);
                $this->users_approved .= $user.':';
                if(!$this->addUserToEventDb($user)) {
                    echo 'User not added to database';
                }
            }elseif($what=='deny'){
                $this->users_waiting = str_replace($user.':', '', $this->users_waiting);
                $this->users_denied .= $user.':';
            }elseif($what=='waiting'){
                $this->users_waiting .= $user.':';
            }elseif($what=='retire'){
                $this->users_waiting = str_replace($user.':', '', $this->users_waiting);
            }elseif($what=='paid'){
                $this->users_paid .= $user.':';
            }elseif($what=='notpaid'){
                $this->users_paid = str_replace($user.':', '', $this->users_paid);
            }elseif($what=='reimbursed') {
                $this->users_reimbursed .= $user.':';
            }elseif($what == 'notreimbursed') {
                $this->users_reimbursed = str_replace($user.':', '', $this->users_reimbursed);
            }
        }
        $query = "UPDATE ".PFIX."_events SET users_approved = '$this->users_approved',
                                                            users_waiting = '$this->users_waiting',
                                                            users_denied = '$this->users_denied',
                                                            users_paid = '$this->users_paid',
                                                            users_reimbursed = '$this->users_reimbursed'
                                                            WHERE id = '$this->id';";
        return $db->query($query);
    }

    private function addUserToEventDb($user) {
        global $db;
        if($this->bet_on=='results'){
            $eventquery = "ALTER TABLE ".PFIX."_event_".$this->id."
							ADD ".$user."_h INT DEFAULT NULL,
							ADD ".$user."_v INT DEFAULT NULL,
							ADD ".$user."_points INT DEFAULT NULL,
							ADD ".$user."_money FLOAT DEFAULT NULL,
							ADD ".$user."_ranking INT DEFAULT NULL;";
        }else{
            $eventquery = "ALTER TABLE ".PFIX."_event_".$this->id."
							ADD ".$user."_toto INT(1) DEFAULT NULL,
							ADD ".$user."_points INT DEFAULT NULL,
							ADD ".$user."_money FLOAT DEFAULT NULL,
							ADD ".$user."_ranking INT DEFAULT NULL;";

        }
        return $db->query($eventquery);

    }

    /**
     * @return array|string
     */
    public function generateEventInfo(){
        global $cont, $settings;

        $id = $this->id;
        $matchnb = $this->match_nb;
        $point=1;
        $alphabet = array('0', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k');
        $subpoint=1;


        $header = $cont->get('eventinfo_rules').
            '<br/><br/><a href="javascript: changeFloatingLayer(\''.$id.'_stake\')">'.$cont->get('eventinfo_stake').'</a> |
		<a href="javascript: changeFloatingLayer(\''.$id.'_tips\')">'.$cont->get('mytips_tips').'</a> | ';
        if($this->stake_mode=='permatch') $header .= '<a href="javascript: changeFloatingLayer(\''.$id.'_gain\')">'.$cont->get('ranking_gain').'</a> | ';
        if($this->stake_mode!='none') $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_pointsjp\')">'.$cont->get('ranking_points').' & '.$cont->get('overview_jackpot').'</a> | ';
        if($this->stake_mode=='none') $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_pointsjp\')">'.$cont->get('ranking_points').'</a> | ';
        $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_end\')">'.$cont->get('eventinfo_finalaccount').'</a>';


        //STAKE
        $to_sub1 = array($this->stake.' '.$this->currency);
        $to_sub2 = array($matchnb, $this->stake*$matchnb.' '.$this->currency);

        $layers['stake'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>';

        switch($this->stake_mode){
            case 'none':
                $layers['stake'] .= '<td>'.$cont->get('eventinfo_nostake').'</td>';
                break;
            case 'fix':
                $layers['stake'] .= '<td>'.substitute($cont->get('eventinfo_fixstake'), $to_sub1).'</td>';
                break;
            case 'permatch':
                $layers['stake'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_stakepermatch'), $to_sub1).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_staketotal'), $to_sub2).'</td>';
        }
        $layers['stake'] .= '</tr></table>';

        //TIPS
        //prep
        $to_sub_toto = array ($cont->get('eventinfo_beton_toto_1').', ', $cont->get('eventinfo_beton_toto_2'), ' & '.$cont->get('eventinfo_beton_toto_x'));
        if($this->bet_on != 'toto') array_pop($to_sub_toto);
        $to_sub_matchtypes = array ($cont->get('eventinfo_tournamentmatches'), $cont->get('eventinfo_komatches'));

        $subpoint = 1;
        $layers['tips'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>';
        if($this->bet_on=='results') $layers['tips'] .= '<td>'.$cont->get('eventinfo_precise_tip').'</td>';
        if($this->bet_on=='toto') $layers['tips'] .= '<td>'.substitute($cont->get('eventinfo_beton_toto'), $to_sub_toto).'</td>';
        $layers['tips'] .= '</tr><tr>
			<td/>';
        if($this->ko_matches=='no'){
            $layers['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_inallmatches').' '.substitute($cont->get('eventinfo_allpossible'),
                Array($cont->get('general_victory'), $cont->get('general_defeat'), $cont->get('general_tie'))).'</td>
				</tr><tr>';
        }elseif($this->ko_matches=='only' && $this->enable_tie=='no'){
            $layers['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_inallmatches').' '.substitute($cont->get('eventinfo_tieno'),
                array($cont->get('general_victory'), $cont->get('general_defeat')));
            $layers['tips'] .= ' '.$cont->get('eventinfo_afterpenalties').' ';
            $layers['tips'] .= ($this->ap_score == 'addone') ?
                $cont->get('eventinfo_afterpenalties_one') :
                $cont->get('eventinfo_afterpenalties_all');
            $layers['tips'] .= '</td>
				</tr><tr>';
        }elseif($this->ko_matches=='only' && $this->enable_tie=='yes'){
            $layers['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_inallmatches').' '.substitute($cont->get('eventinfo_allpossible'),
                array($cont->get('general_victory'), $cont->get('general_defeat'), $cont->get('general_tie'))).' '.substitute($cont->get('eventinfo_tietough'), array()).'</td>
				</tr><tr>';
        }elseif($this->ko_matches=='yes' && $this->enable_tie=='yes'){
            $layers['tips'] .= '<td>'.substitute($cont->get('eventinfo_matches_both_types'), $to_sub_matchtypes).'</td></tr><tr>
					<td/><td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_in_matches').' '.$cont->get('eventinfo_tournamentmatches')
                .' '.substitute($cont->get('eventinfo_allpossible'),	array($cont->get('general_victory'), $cont->get('general_defeat'),
                $cont->get('general_tie'))).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_in_matches').' '.$cont->get('eventinfo_komatches')
                .' '.substitute($cont->get('eventinfo_allpossible'),	array($cont->get('general_victory'), $cont->get('general_defeat'),
                $cont->get('general_tie'))).' '.substitute($cont->get('eventinfo_tietough'), ' '.$cont->get('eventinfo_in_matches').' '.$cont->get('eventinfo_komatches')).'</td>
				</tr><tr>';

        }elseif($this->ko_matches=='yes' && $this->enable_tie=='no'){
            $layers['tips'] .= '<td>'.substitute($cont->get('eventinfo_matches_both_types'), $to_sub_matchtypes).'</td></tr><tr>
					<td/><td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_in_matches').' '.$cont->get('eventinfo_tournamentmatches')
                .' '.substitute($cont->get('eventinfo_allpossible'),	array($cont->get('general_victory'), $cont->get('general_defeat'),
                $cont->get('general_tie'))).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_in_matches').' '.$cont->get('eventinfo_komatches')
                .' '.substitute($cont->get('eventinfo_tieno'), array($cont->get('general_victory'), $cont->get('general_defeat')));
            $layers['tips'] .= ' '.$cont->get('eventinfo_afterpenalties').' ';
            $layers['tips'] .= ($this->ap_score == 'addone') ?
                $cont->get('eventinfo_afterpenalties_one') :
                $cont->get('eventinfo_afterpenalties_all');
            $layers['tips'] .='</td>
				</tr><tr>';
        }

        $bu = preg_split('/:/', $this->bet_until);
        if ($bu[0] > 1){
            $plural = 's';
        }
        if ($bu[1] == 'm') $bu[1] = $cont->get('general_minute'.$plural);
        if ($bu[1] == 'h') $bu[1] = $cont->get('general_hour'.$plural);
        if ($bu[1] == 'd') $bu[1] = $cont->get('general_day'.$plural);
        $before = ($bu[2] == 'm') ? $cont->get('eventinfo_match') : $cont->get('eventinfo_thefirstmatch');
        $to_sub_betuntil = Array($bu[0].' '.$bu[1], $before);
        $layers['tips'] .= '<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.substitute($cont->get('eventinfo_bet_until'), $to_sub_betuntil).' '.$cont->get('eventinfo_deadline_toolate').'</td>
		</tr><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.$cont->get('eventinfo_overview').'<td>
		</tr></table>';

        //GAIN
        if ($this->stake_mode == 'permatch'){
            $subpoint = 1;
            $to_sub3 = array($cont->get('general_victory'), $cont->get('general_tie'), $cont->get('general_defeat'), $this->stake.' '.$this->currency);
            $layers['gain'] = $header.'<table class="eventinfo"><tr>
				<td class="topalign"><b>'.$point++.'.</b></td>
				<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_gain_correcttip'), $to_sub1).'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_gain_correcttips').'</td>
			</tr><tr>';
            if ($this->stake_back == 'yes'){
                $layers['gain'] .= '<td class="topalign"><b>'.$point++.'.<b/></td>
				<td>'.$cont->get('eventinfo_gain_nobodycorrect').' '.substitute($cont->get('eventinfo_gain_stakeback'), $to_sub3).'</td>
			</tr></table>';
            }else{
                $layers['gain'] .= '<td class="topalign"><b>'.$point++.'.<b/></td>
				<td>'.$cont->get('eventinfo_gain_nobodycorrect').' '.$cont->get('eventinfo_gain_tojackpot').'</td>
			</tr></table>';
            }
        }

        //POINTS & JACKPOT
        $subpoint = 1;
        $to_sub5 = ($this->jp_fraction_or_fix == 'fix') ? $this->jp_fix : ($this->jp_fraction*100).'% ('.$cont->get('eventinfo_jackpot_floored').')';
        $to_sub_exp = array($this->jp_distr_exp_value, ($this->jp_distr_exp_value*100).'%');
        $layers['pointsjp'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.$cont->get('eventinfo_points').'</td>
		</tr><tr>';
        if($this->p_correct != NULL){
            $layers['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_points_correct'), $this->p_correct).'</td>
		</tr><tr>';
        }
        if($this->p_diff != NULL){
            $layers['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_points_diff'), array($cont->get('general_eg'), $this->p_diff)).'</td>
		</tr><tr>';
        }
        if($this->p_almost != NULL){
            $layers['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_points_almost'), array($cont->get('general_eg'), $this->p_almost)).'</td>
		</tr><tr>';
        }
        if($this->p_wrong != NULL){
            $layers['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_points_wrong'), $this->p_wrong).'</td>
		</tr>';
        }
        if($this->stake_mode!='none'){
            $subpoint = 1;
            $layers['pointsjp'].='<tr><td class="topalign"><b>'.$point++.'.<b/></td>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_jackpot').'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$cont->get('eventinfo_jackpot_samerank').'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($cont->get('eventinfo_jackpot_distributeon'), $to_sub5);
            if($this->jp_distr_algorithm=='exp'){
                $layers['pointsjp'] .= substitute($cont->get('eventinfo_jackpot_expformula'), $to_sub_exp);
            }elseif($this->jp_distr_algorithm=='lin'){
                $layers['pointsjp'] .= $cont->get('eventinfo_jackpot_linformula');
            }else{
                $layers['pointsjp'] .= $cont->get('eventinfo_jackpot_fixformula');
                $percents = preg_split('/:/',$this->jp_distr_fix_shares);
                array_pop($percents);
                $rankcounter=1;
                foreach ($percents as $p){
                    $rankcounter++;
                    $layers['pointsjp'] .= '<br/>'.$rankcounter.'. '.$cont->get('ranking_rank')
                        .': '.$p.'%';
                }
            }
            $layers['pintsjp'].= '</td>
			</tr></table>';
        }else{
            $layers['pointsjp'] .= '</table>';
        }

        $to_sub7 = array($cont->get('general_bettingOffice').' '.$settings['name']);
        $layers['end'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>';
        if($this->stake_mode == 'permatch'){
            $layers['end'] .= $cont->get('eventinfo_finalaccount_gainplusjp');
        }elseif($this->stake_mode == 'fix'){
            $layers['end'] .= $cont->get('eventinfo_finalaccount_jp');
        }else{
            $layers['end'] .= $cont->get('eventinfo_finalaccount_points');
        }
        $layers['end'] .= '</td>
		</tr>';
        if(file_exists('data/bo_img/seal@thumb')){ $layers['end'] .=  '</tr>
			<td/>
			<td>'.substitute($cont->get('eventinfo_sealofapproval'), $to_sub7).'<br/><img padding="10px;"  src="data/bo_img/seal@thumb"/></td>
		</tr>';
        }
        $layers['end'] .= '</table>';

        return $layers;

    }

    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////

    /**
     * @param int $userID
     * @return bool
     */
    public function userIsApproved($userID) {


        $users = preg_split('/:/',$this->users_approved);
        if (in_array($userID,$users)) {
            return true;
        }
        else{
            return false;
        }

    }

    /**
     * @param int $userID
     * @return bool
     */
    public function userIsWaiting($userID) {


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
    public function userIsDenied($userID) {


        $users = preg_split('/:/',$this->users_denied);
        if (in_array($userID,$users))
            return true;
        else
            return false;
    }

    /**
     * @param int $userID
     * @return bool
     */
    public function userHasPaid($userID) {

        $users = preg_split('/:/',$this->users_paid);
        if (in_array($userID,$users))
            return true;
        else
            return false;
    }

    /**
     * @param int $userID
     * @return bool
     */
    public function userHasBeenReimbursed($userID) {

        $users = preg_split('/:/',$this->users_reimbursed);
        if (in_array($userID,$users))
            return true;
        else
            return false;
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
     * @return string
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
     * @return string
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

    /**
     * @return \BetsContainer
     */
    public function getBetsContainer()
    {
        return $this->betsContainer;
    }

    /**
     * @param $id
     * @return Bet
     */
    public function getBetById($id) {
        foreach ($this->betsContainer->getBets() as $bet) {
            /* @var $bet Bet */
            if ($bet->getId() == $id)
                return $bet;
        }
    }

    /**
     * @param int $p_diff
     */
    public function setPDiff($p_diff)
    {
        $this->p_diff = $p_diff;
    }

    /**
     * @return int
     */
    public function getPDiff()
    {
        return $this->p_diff;
    }

}
