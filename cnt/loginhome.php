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
?>
<?
echo '<h2>'.$lang['loginhome_title'].'</h2>';
echo $lang['loginhome_content'].'<p />';

global $events, $db;

//get the number of events, in which the user participates
$nb = UserEventNumber();


if($nb > 0){

	//singular or plural?
	echo ($nb > 1) ? $lang['loginhome_yourevents'] : $lang['loginhome_yourevent'];

	//which events?
	$ueventsarray = loadUserEvents();
	$uevents = explode(':', $ueventsarray['approved']);
	array_pop($uevents);

	$mysettings = loadSettings($_SESSION['userid']);
	
	//display some information and links for this events
	echo '<ul>';
	foreach ($uevents as $ev){
		echo '<li class="evlist"><b>'.$events['u']['e'.$ev]['name'].': ';
		$queryfield = ($events['u']['e'.$ev]['score_input_type'] == 'results') ? 'score_h' : 'score';
		$rawdata = $db->query("SELECT ".$queryfield."
					FROM ".PFIX."_event_".$ev. " 
					WHERE ".$queryfield." IS NOT NULL ORDER BY time");
		if (sizeof($rawdata) > 0){
			$info = rankingCalculate($ev);
			$points = $money = $rank = 0;
			$query = "SELECT * FROM ".PFIX."_event_".$ev." WHERE ".$queryfield." IS NULL ORDER BY time ASC;";
			$finishedraw=$db->query($query);
			$gainlang = $lang['ranking_provisorygain'];
			if (sizeof($finishedraw) == 0){
				$over = true;
				$gainlang = $lang['ranking_totalgain'];
			}
			echo $lang['ranking_rank'].': <b>'.$info['rank'][$_SESSION['userid']].'</b>, '
				.$lang['ranking_points'].': <b>'.$info['points'][$_SESSION['userid']].'</b>, '
				.$gainlang.': <b>'.($info['money'][$_SESSION['userid']]+$info['jackpots'][$info['rank'][$_SESSION['userid']]]).' '.$events['p']['e'.$ev]['currency'].'</b>';
		}
		
		echo '<br/>';
			echo ' </b><a href="?menu=mytips&ev='.$ev.'">'.$lang['mytips_title'].'</a> ||';
			echo ' <a href="?menu=overview&ev='.$ev.'">'.$lang['overview_title'].'</a> 
					(<a href="?menu=overview&ev='.$ev.'&u='.$_SESSION['userid'].'">'.$lang['overview_onlyme'].'</a>)  || ';
			echo ' <a href="?menu=comments&ev='.$ev.'">'.$lang['comments_title'].'</a>';
			if ($_SESSION['admin']) echo ' || <a href="?menu=admin&submenu=events&ssubmenu=results&ev='.$ev.'#now">'.$lang['admin_events_results_title'].'</a>';

		echo '</li>';
	}
	echo '</ul>';

	if ($mysettings['home_comments'] > 0){
		echo $lang['loginhome_newcomments'].'<p/>';
	
		//get user names to display them
		$users_raw = $db->query("SELECT id, login FROM ".PFIX."_users;");
		foreach ($users_raw as $u){
			$user[$u['id']] = $u['login'];
		}
		$tempuevents = $uevents;
		$querystring = "SELECT * FROM ".PFIX."_comments WHERE event = ".array_pop($tempuevents)." ";
		foreach ($tempuevents as $ev) $querystring .= "OR event = ".$ev." ";
		$querystring .= "ORDER BY time DESC LIMIT ".$mysettings['home_comments'].";";
		$commentdata = $db->query($querystring);
	
		echo '<div class="comment">';
		foreach ($commentdata as $cmt){
		//tilteline
		echo '<b>';
		echo '<div class="cmttitler">'.$lang['general_by'].' '.$user[$cmt['user']].'</div>';
		echo '<div class="cmttitlel">'.$cmt['title'].'</div>';
		echo '</b>';
		//comment	
		echo '<div class="cmttext">'.substr($cmt['text'], 0, 50).'... '
			.'<a href="?menu=comments&ev='.$cmt['event'].'#'.$cmt['id'].'">'.$lang['general_read'].'</a></div>';
		}
		echo '</div>';
	
	}	
}else{

	//tell the user that he's not registered to any event
	echo $lang['loginhome_noevent'];

    $nb = ActiveEventNumber();
    if($nb > 0){
        #echo '<p>'.$lang['home_events'].'<br>';
        echo '<ul>';
        foreach ($events['p'] as $key => $ev){
            if (is_string($key))
                continue;
            if(is_array($events['p']['e'.$ev])){
                echo '<li>'.$events['p']['e'.$ev]['name'];
                $flcnt = generateEventInfo($ev);
                foreach($flcnt as $sid => $cnt)
                    echo makeFloatingLayer($events['p']['e'.$ev]['name'], $cnt, 1, $ev.'_'.$sid);
                echo '<a href="javascript: showFloatingLayer(\''.$ev.'_stake\')" title="'.$lang['general_show_info'].'"> Info </a>||';
                echo ' <a href="?menu=overview&ev='.$ev.'">'.$lang['overview_title'].'</a> || ';
                echo substitute($lang['loginhome_goparticipate'],array("<a href='?menu=myprofile'>".$lang['myprofile_title']."</a>"));
                echo '</li>';
            }
        }
        echo '</ul>';
    }

}
?>
