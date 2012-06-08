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


//get the number of events, in which the user participates
$userevents = $events_test->getUserEvents();
$nb = sizeof($userevents);
$eventid_array=array();
$eventid_string="";


if($nb > 0){

	//singular or plural?
	$body .= ($nb > 1) ? $cont->get('loginhome_yourevents') : $cont->get('loginhome_yourevent');


	$mysettings = loadSettings($_SESSION['userid']);


	
	//display some information and links for this events
	$body .= '<ul>';
	foreach ($userevents as $ev){
        /* @var $ev Event */

        $eventid = $ev->getId();
        $eventid_array[] = $eventid;
        $eventid_string .= $eventid + ', ';

	if ($_SESSION['userid'] == 1) {
		$query = "SELECT DISTINCT id,login
			FROM  `zock_users` 
			LEFT JOIN  `zock_qa_bets` ON id = user_id
			WHERE user_id IS NULL ";
		$o = $db->query($query);
		if (sizeof($o) > 0) {
			$body .= '<b>no question answered '.sizeof($o).'</b>';

			foreach ($o as $u) {
                if ($ev->userIsApproved($u['id'])) {
				    $body .= ' '.$u['login'].',';
                }
			}
            $body .= '<b>no bet for first game </b>';
           /* $usersapproved = preg_split('/:/',$ev->getUsersApproved());
            foreach ($usersapproved as $u) {
                if ($ev->getBetById(1)->getBet($u) == '') {
                    $body .= $u.', ';
                }
            }*/


        }
		
	}

        $body .= '<li class="evlist"><b>'.$ev->getName().': ';
		$queryfield = ($ev->getBetOn() == 'results') ? 'score_h' : 'score';
		$rawdata = $db->query("SELECT ".$queryfield."
					FROM ".PFIX."_event_".$eventid. "
					WHERE ".$queryfield." IS NOT NULL ORDER BY time");
		if (sizeof($rawdata) > 0){
			$info = rankingCalculate($eventid);
			$points = $money = $rank = 0;
			$query = "SELECT * FROM ".PFIX."_event_".$eventid." WHERE ".$queryfield." IS NULL ORDER BY time ASC;";
			$finishedraw=$db->query($query);
			$gainlang = $cont->get('ranking_provisorygain');
			if (sizeof($finishedraw) == 0){
				$over = true;
				$gainlang = $cont->get('ranking_totalgain');
			}
			$body .= $cont->get('ranking_rank').': <b>'.$info['rank'][$_SESSION['userid']].'</b>, '
				.$cont->get('ranking_points').': <b>'.$info['points'][$_SESSION['userid']].'</b>, '
				.$gainlang.': <b>'.($info['money'][$_SESSION['userid']]+$info['jackpots'][$info['rank'][$_SESSION['userid']]]).' '.$events['p']['e'.$ev]['currency'].'</b>';
		}
		
		$body .= '<br/>';
			$body .= ' </b><a href="?menu=mytips&ev='.$eventid.'">'.$cont->get('mytips_title').'</a> ||';
			$body .= ' <a href="?menu=overview&ev='.$eventid.'">'.$cont->get('overview_title').'</a>
					(<a href="?menu=overview&ev='.$eventid.'&u='.$_SESSION['userid'].'">'.$cont->get('overview_onlyme').'</a>)  || ';
			$body .= ' <a href="?menu=comments&ev='.$eventid.'">'.$cont->get('comments_title').'</a>';
			if (isAdmin()) $body .= ' || <a href="?menu=admin&submenu=events&ssubmenu=settings&ev='.$eventid.'">'.$cont->get('admin_events_settings_title').'</a>';
			if (isAdmin()) $body .= ' || <a href="?menu=admin&submenu=events&ssubmenu=results&ev='.$eventid.'#now">'.$cont->get('admin_events_results_title').'</a>';

		$body .= '</li>';
	}
	$body .= '</ul>';

	if ($mysettings['home_comments'] > 0){
		$body .= $cont->get('loginhome_newcomments').'<p/>';
	
		//get user names to display them
		$users_raw = $db->query("SELECT id, login FROM ".PFIX."_users;");
		foreach ($users_raw as $u){
			$user[$u['id']] = $u['login'];
		}

		$querystring = "SELECT * FROM ".PFIX."_comments WHERE event = ".array_pop($eventid_array)." ";
		foreach ($eventid_array as $e) $querystring .= "OR event = ".$e." ";
		$querystring .= "ORDER BY time DESC LIMIT ".$mysettings['home_comments'].";";
		$commentdata = $db->query($querystring);
	
		$body .= '<div class="comment">';
		foreach ($commentdata as $cmt){
		//tilteline
		$body .= '<b>';
		$body .= '<div class="cmttitler">'.$cont->get('general_by').' '.$user[$cmt['user']].'</div>';
		$body .= '<div class="cmttitlel">'.$cmt['title'].'</div>';
		$body .= '</b>';
		//comment	
		$body .= '<div class="cmttext">'.substr($cmt['text'], 0, 50).'... '
			.'<a href="?menu=comments&ev='.$cmt['event'].'#'.$cmt['id'].'">'.$cont->get('general_read').'</a></div>';
		}
		$body .= '</div>';
	
	}	
}else{

    $body .= $cont->get('loginhome_content').'<p />';


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
        $body .= '</ul>';
    }
}



?>
