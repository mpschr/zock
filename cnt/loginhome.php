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

global $events_test, $db, $cont;


$body .= '<h2>'.$cont->get('loginhome_title').'</h2>';

$thisuser =  new User($_SESSION['userid']);

//get the number of events, in which the user participates
$userevents = $events_test->getUserEvents();
$nb = sizeof($userevents);
$eventid_array=array();
$eventid_string="";
$showEventsToRegister = false;
$show_comments_later = false;

$hasNotPaidEvent = false;
$notGottenReimbursed = false;
$mustPayBack = false;

if ($nb > 0) {
    foreach ($userevents as $event) {
        /* @var $event Event */
        if ($event->getFinished()) {
            if(!$event->userHasBeenReimbursed($_SESSION['userid']))
                $notGottenReimbursed = true;
        } else {
            if(!$event->userHasPaid($_SESSION['userid']))
                $hasNotPaidEvent = true;
        }
    }
}


if ($hasNotPaidEvent) {
    $body .= indication($cont->get('loginhome_paystake'));
}
else if ($thisuser->getAccountDetails() == "") {
    $body .= indication($cont->get('loginhome_filloutbankdetails'));
}else if ($notGottenReimbursed) {
    $body .= indication($cont->get('loginhome_reimbursement'));
} else  if ($thisuser->getPicture() == ""){
    $body .= indication($cont->get('loginhome_putpicture'));
} else if ($thisuser->getName() == "" || $thisuser->getFamname() == "") {
    $body .= indication($cont->get('loginhome_filloutname'));
}


if($nb > 0){
	$mysettings = loadSettings($_SESSION['userid']);

    if (!(isset($_REQUEST['ev']))) 
        $_REQUEST['ev'] = $userevents[0]->getId();


    //singular or plural?
    $event_summaries .= ($nb > 1) ? $cont->get('loginhome_yourevents') : $cont->get('loginhome_yourevent');

	//display some information and links for this events
    $event_summaries .= '<ul>';
	foreach ($userevents as $ev){
        /* @var $ev Event */

        $eventid = $ev->getId();
        $eventid_array[] = $eventid;
        $eventid_string .= $eventid + ', ';

        $gottenReimbursed = $ev->userHasBeenReimbursed($_SESSION['userid']);
        if ($gottenReimbursed) {
            $nb = $nb - 1;
            continue;
        }

        // first participating and active event:
        if (!isset($_SESSION['currevent']))
            $_SESSION['currevent'] = $ev->getId();

        $event_summaries .= '<li class="evlist"><b>'.$ev->getName().': ';
        $queryfield = ($ev->getBetOn() == 'results') ? 'score_h' : 'score';
        $rawdata = $db->query("SELECT ".$queryfield."
                                FROM ".PFIX."_event_".$eventid. "
                                WHERE ".$queryfield." IS NOT NULL ORDER BY time");
        if (sizeof($rawdata) > 0){
            
            $ranking = new Ranking($event);
			$info = $ranking->getRankingDetails();
			$points = $money = $rank = 0;
			$query = "SELECT * FROM ".PFIX."_event_".$eventid." WHERE ".$queryfield." IS NULL ORDER BY time ASC;";
			$finishedraw=$db->query($query);
			$gainlang = $cont->get('ranking_provisorygain');
			if (sizeof($finishedraw) == 0){
				$over = true;
				$gainlang = $cont->get('ranking_totalgain');
			}
			$event_summaries .= $cont->get('ranking_rank').': <b>'.$info['rank'][$_SESSION['userid']].'</b>, '
				.$cont->get('ranking_points').': <b>'.$info['points'][$_SESSION['userid']].'</b>, '
				.$gainlang.': <b>'.($info['money'][$_SESSION['userid']]+$info['jackpots'][$info['rank'][$_SESSION['userid']]]).' '.$ev->getCurrency().'</b>';
		}
                
                            
                $bdp_matches = $event->getBetsContainer()->getBets('withoutresult');                        
                /*@var $open_bets array(Bet) */
                $open_bets = array();      
                $bet_comparator = "";
                foreach($bdp_matches as $bet) {
                    /* @var $bet Bet */
                    $userid = $thisuser->getId();
                    if($bet->isEmptyBet($userid)) {
                        if ($bet_comparator == "") {
                            $bet_comparator = date( "z-Y", $bet->getDueDate());
                        } 
                        if ($bet_comparator == date( "z-Y", $bet->getDueDate())) {
                            array_push($open_bets, $bet);
                        } else {
                            break;
                        }
                    }                    
                }
                if (sizeof($open_bets) > 0) {
                    $event_summaries .= '<br/>' . sizeof($open_bets) . " open bets, due until " . $open_bets[0]->getRemainingTime();
                }
                
		
		$event_summaries .= '<br/>';
			$event_summaries .= ' </b><a href="?menu=mytips&ev='.$eventid.'">'.$cont->get('mytips_title').'</a> ||';
			$event_summaries .= ' <a href="?menu=overview&ev='.$eventid.'">'.$cont->get('overview_title').'</a>
					(<a href="?menu=overview&ev='.$eventid.'&u='.$_SESSION['userid'].'">'.$cont->get('overview_onlyme').'</a>)  || ';
			$event_summaries .= ' <a href="?menu=comments&ev='.$eventid.'">'.$cont->get('comments_title').'</a>';
			if (isAdmin()) $event_summaries .= ' || <a href="?menu=admin&submenu=events&ssubmenu=settings&ev='.$eventid.'">'.$cont->get('admin_events_settings_title').'</a>';
			if (isAdmin()) $event_summaries .= ' || <a href="?menu=admin&submenu=events&ssubmenu=results&ev='.$eventid.'#now">'.$cont->get('admin_events_results_title').'</a>';

		$event_summaries .= '</li>';
	}
	$event_summaries .= '</ul>';

	if ($mysettings['home_comments'] > 0){
		$latest_comments .= $cont->get('loginhome_newcomments').'<p/>';
	
		//get user names to display them
		$users_raw = $db->query("SELECT id, login FROM ".PFIX."_users;");
		foreach ($users_raw as $u){
			$user[$u['id']] = $u['login'];
		}

		$querystring = "SELECT * FROM ".PFIX."_comments WHERE event = ".array_pop($eventid_array)." ";
		foreach ($eventid_array as $e) $querystring .= "OR event = ".$e." ";
		$querystring .= "ORDER BY time DESC LIMIT ".$mysettings['home_comments'].";";
		$commentdata = $db->query($querystring);
	
		$latest_comments .= '<div class="comment">';
		foreach ($commentdata as $cmt){
		//tilteline
		$latest_comments .= '<b>';
		$latest_comments .= '<div class="cmttitler">'.$cont->get('general_by').' <a href="?menu=participants&showuser=' . $cmt['user'] . '">'.$user[$cmt['user']].'</a></div>';
		$latest_comments .= '<div class="cmttitlel">'.$cmt['title'].'</div>';
		$latest_comments .= '</b>';
		//comment	
		$latest_comments .= '<div class="cmttext">'.substr($cmt['text'], 0, 50).'... '
			.'<a href="?menu=comments&ev='.$cmt['event'].'#'.$cmt['id'].'">'.$cont->get('general_read').'</a></div>';
		}
		$latest_comments .= '</div>';
	
	}

    if ($nb > 0) {
        $body .= $event_summaries.$latest_comments;
    } else {
        $show_comments_later = true;
    }

}else{

    $body .= $cont->get('loginhome_content').'<p />';
}

if ($nb == 0)
    $showEventsToRegister = true;

if ($showEventsToRegister) {

    //tell the user that he's not registered to any event
	$body .= $cont->get('loginhome_noevent');
    $active_events = $events_test->getActiveEvents();
    $nb = sizeof($active_events);

    if($nb > 0){
        #$body .= '<p>'.$cont->get('home_events').'<br>';
        $body .= '<ul>';
        foreach ($active_events as $event){

            /* @var $event Event */
            $evid = $event->getId();
            $body .= '<li>'.$event->getName();
            $flcnt = $event->generateEventInfo();
            foreach($flcnt as $sid => $cnt)
                $body .= makeFloatingLayer($event->getName(), $cnt, 1, $evid.'_'.$sid);

            $body .= '<a href="javascript: showFloatingLayer(\''.$evid.'_stake\')" title="'.$cont->get('general_show_info').'"> Info </a>';
            $body .= ' || '.substitute($cont->get('loginhome_goparticipate'),array("<a href='?menu=myprofile'>".$cont->get('myprofile_title')."</a>"));
            $body .= ' || <a href="?menu=overview&ev='.$evid.'">'.$cont->get('overview_title').'</a>';
            $body .= '</li>';
        }
        $body .= '</ul><br/>';
    }
}

if ($show_comments_later) {
    $body .= $latest_comments;
}

?>
