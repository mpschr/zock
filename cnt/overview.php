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

echo '<h2>'.$lang['overview_title'].'</h2>';

global $db, $settings, $events;

//event handling ;) => estimate if user is registerd to events & load the events
$nb =  ActiveEventNumber('p');
if($_SESSION['logged']){
	$userevents = loadUserEvents();
	$nb =  UserEventNumber();
}

if($nb < 1){
	//no events
	echo $lang['overview_noevents'];
	
}elseif($nb == 1){
	//one event
	$thisevent = $events['p'][0];
	
}elseif($nb > 1){
	//multible events
	//=> 2buttons (one hidden) and a vmenu
	createVerticalMenu(NULL, 'peventlist');
	createVerticalMenu(NULL, 'mmopen');
	createVerticalMenu(NULL, 'mmclose');
	//the session variable currevent must either a public event or the user participates. It can be in the session
	//after having looked at a public event in the overview section
	if(isset($_SESSION['currevent']) && eventIsPublic($_REQUEST['ev'])){
		$thisevent = $_SESSION['currevent'] = $_REQUEST['ev'];
	}else{
		if (!(isset($_REQUEST['ev'])) && !(isset($_SESSION['currevent']))){
			if($_SESSION['logged']){
				$thisevent = ereg_replace('.*:([0-9]+):$', '\\1', $userevents['approved']);
			}else{
				$thisevent = $_SESSION['currevent'] = $events['p'][$events['p']['nb']-1];
			}
		}else{			
			$thisevent = $_SESSION['currevent'];
		}
	} 
			 
}
//$_REQUEST['ev'] overrules the insight of the event handling :)
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent;
//update the current event variable in Session
if( eventIsPublic($_REQUEST['ev']) || userParticipates($_REQUEST['ev'], $_SESSION['userid'])){

 $_SESSION['currevent'] = $_REQUEST['ev'];

$evData = $events['u']['e'.$_REQUEST['ev']];
echo '<h3>'.$evData['name'].'</h3>';

if($nb >= 1){
	//show all of it
	if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent;
	
	$orderby = (isset($_REQUEST['orderby'])) ? explode(':', $_REQUEST['orderby']) : explode(':', 'time:ASC');


	//filtering
	if (isset($_REQUEST['filter'])){
		$filter = " WHERE ";
		$f = split(':', $_REQUEST['filter']);
		switch ($f[0]){
			case 'team':
				$filter .= "`home` LIKE '%".$f[1]."%' OR `visitor` LIKE '%".$f[1]."%'";
				$f_team = 'selected';
				break;
			case 'home';
				$filter .= "`home` LIKE '%".$f[1]."%'";
				$f_home = 'selected';
				break;
			case 'visitor';
				$filter .= "`visitor` LIKE '%".$f[1]."%'";
				$f_visitor = 'selected';
				break;
			case 'matchday';
				$filter .= "`matchday` LIKE '".$f[1]."'";
				$f_matchday = 'selected';
				break;
		}
	}

	//get all the data!
	if ($orderby[0] == 'matchday_id') $orderplus = ", time ASC";
	$query = "SELECT * FROM ".PFIX."_event_".$_REQUEST['ev'].$filter." ORDER BY ".$orderby[0]." ".$orderby[1].$orderplus.";";
	$bdp_matches =  $db->query($query);
	$bdp_rows =  $db->row_count($query);
	
	//which users participate in this event? => get their names
	$evUsers = (explode(':', $evData['a']));
	$usersraw = $db->query("SELECT id, login, picture FROM ".PFIX."_users");
	foreach ($usersraw as $u){
		$userarray[$u['id']] = $u['login'];
		$idx = strrpos($u['picture'],'.');
		$fext = substr($u['picture'],$idx);
		$fn = substr($u['picture'],0,$idx);
		$picture[$u['id']] = $fn.'@thumb'.$fext;
	}
	array_pop($evUsers);

	//$mnb stands for Match NumBer, is necessary to limit the amount of matches displayed (not yet implemented in overview)	
	$mnb = (isset($_REQUEST['mnb'])) ? $_REQUEST['mnb'] : 1;

	if($bdp_matches == NULL && !isset($_REQUEST['filter'])){
			
			//there are no matches
			echo $lang['general_nomatches'];
			echo ' ('.$evData['name'].')';
			
	}else{
		if($bdp_rows == 0 && isset($_REQUEST['filter'])){
			//no results with this filter
			echo errorMsg('filter_emptyresults');
		}
		
		echo $lang['overview_content'].'<p>';
	
		//filterform
		$filterurl = eregi_replace('(filter=)[a-zA-Z0-9:]+[&]', '', $link_query); 
		$filterurl = $link.$filterurl;
		echo '<form action="javascript: filter(\''.$filterurl.'\')">
			<a href="javascript: showFilter()" >'.$lang['general_filter'].'</a>
			<div id="filterform" class="notvisible" >
				<select id="filter_on" onChange="filterChange()">
					<option value="nofilter"></option>
					<option value="team" '.$f_team.'>'.$lang['general_team'].'</option>
					<option value="home" '.$f_home.'>'.$lang['admin_events_home'].'</option>
					<option value="visitor" '.$f_visitor.'>'.$lang['admin_events_visitor'].'</option>
					<option value="matchday" '.$f_matchday.'>'.$lang['admin_events_matchday'].'</option>
				</select>';
				echo ' <span id="filter_contains">'.$lang['general_contains'].'</span> ';
				echo ' <span id="filter_is" class="notvisible">'.$lang['general_is'].'</span> ';
				echo '<input id="filter_this" value="'.$f[1].'" size="15"/>';
				echo '<a href="javascript: filterUnset()"> x </a>';
				echo ' <input type="submit" value="'.$lang['general_filterverb'].'"/>';
			echo '</div>';	
		echo '</form>';
	

       //user2column
        if (isset($_REQUEST['u'])) $_REQUEST['col'] = user2column($_REQUEST['u'], $_REQUEST['ev']);


echo '<div id="overview"><table>';


		//title row of the table
		echo '<tr class=title>
			<td class=title><a href="'.$link.orderIt('time', $orderby, $link_query).'"> '.$lang['admin_events_time'].'</a></td>
			<td class=title><a href="'.$link.orderIt('matchday_id', $orderby, $link_query).'"> '.$lang['admin_events_matchday'].'</a></td>
			<td class=title><a href="'.$link.orderIt('home', $orderby, $link_query).'"> '.$lang['admin_events_home'].'</a></td>
			<td class=title><a href="'.$link.orderIt('visitor', $orderby, $link_query).'"> '.$lang['admin_events_visitor'].'</a></td>';
			if($evData['score_input_type'] == 'results') 
				echo '<td class=title><a href="'.$link.orderIt('score_h', $orderby, $link_query).'"> '.$lang['admin_events_score'].'</a></td>';
			else
				echo '<td class=title><a href="'.$link.orderIt('score', $orderby, $link_query).'"> '.$lang['admin_events_score'].'</a></td>';
			if($evData['stake_mode']=='permatch')
				echo '<td class=title><a href="'.$link.orderIt('jackpot', $orderby, $link_query).'"> '.$lang['overview_jackpot'].'</a></td>';
			$player_column = 0;
			foreach($evUsers as $id){
				$player_column++;
				if(!isset($_REQUEST['col'])){
					echo '<td class="title"><a href="'.$link.'col='.$player_column.'">'.$userarray[$id].'</a></td>';
				}elseif(isset($_REQUEST['col']) && $player_column == $_REQUEST['col']){
					echo '<td class="title">'.$userarray[$id].'</td><td class="title"><a href="'.$link.'">>>></a>';
				}
			}
		//to count the rows of matches
		$r = 0;

		//is set false if summeries are to be displayed
		$onlyall = true;
		
		foreach ($bdp_matches as $row){
			//increment rows
			$r++;
			
			/*display a summary (either time or matchday, else no summary),
			if the value in question changes (matchday, or day)*/
			$last = $now; 	//for the comparison
			switch($orderby[0]){
				case 'time':
					$now = date('Ymd', (int) $row['time']);
					$onlyall = false;
					break;
				case 'matchday_id':
					$now = $row['matchday_id'];
					$onlyall = false;
					break;
			}
			//the actual summary
			if($last != $now && $r != 1){
				echo '<tr class="ow_summary">';
				$pointsnmoney = array ($lang['ranking_points'], $lang['ranking_gain']);
				echo '<td>'.substitute($lang['overview_summary'], $pointsnmoney).'</td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				if($evData['stake_mode'] == 'permatch') echo '<td class="ow">'.$jackpot.'</td>';
				$jackpot_all += $jackpot;
				$jackpot = 0;
				$player_column = 0; // set player_column 0 again.. 
				foreach ($evUsers as $u){
					$player_column++;
					if( !isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']){
						if($evData['stake_mode']=='permatch') $display_m =  $money[$u].' & ';
						echo '<td>'.$display_m.$points[$u].'</td>';
					}
					$money_all[$u] += $money[$u];
					$money[$u] = 0;
					$points_all[$u] += $points[$u];
					$points[$u] = 0;
				}
				echo '</tr>';
			}elseif($onlyall){
				$jackpot_all = $jackpot;
				foreach ($evUsers as $u){
					$money_all[$u] = $money[$u];
					$points_all[$u] = $points[$u];
				}
			}

			echo '<tr id="tr'.$r.'" onMouseOver="setOverBG(\'tr'.$r.'\', \''.$settings['style'].'\')" 
								onMouseOut="unsetOverBG(\'tr'.$r.'\')" 
								onClick="switchToActivatedBG(\'tr'.$r.'\', \''.$settings['style'].'\')">';
			
			//match details & tips
			$player_column = 0; // set player_column 0 again.. 
								
				$el = $row;
				echo '<td class="ow_date" width="50px">'.weekday($el['time'],1).', '.date('d.m.Y - H:i',$el['time']).'</td>';	
				echo '<td class="ow">'.$el['matchday'].'</td>'; // "normal" td
				// show tips already?
				$showtips = (time()<betUntil($el['time'],$_REQUEST['ev'])) ? true : false;
				echo '<td class="ow_team" width="50px">'.$el['home'].'</td>'; 
				echo '<td class="ow_team" width="50px">'.$el['visitor'].'</td>';
				echo '<td class="ow"><nobr><font class="ow_correct">'; 
					if($evData['score_input_type'] == 'results'){
						if ($el['score_h'] != NULL){ 
							echo $el['score_h'].':';
							$correcttip = 0;
							//TODO: evaluate correcttips with the function isCorrect!!
							foreach($row as $name => $cnt) {
								if ( stristr($name,'_')=='_points' && $cnt==$evData['p_correct']) ++$correcttip;
							}
							echo $el['score_v'].' ';
							echo $el['score_plus'].'</font>('.$correcttip.'x)'; 
						}
					}else{
						if ($el['score'] != NULL){ 
							$scoretoto = $el['score'];
							if ($scoretoto == 3) $scoretoto = 'X';
							echo $scoretoto.' ';
							$correcttip = 0;
							//TODO: evaluate correcttips with the function isCorrect!!
							foreach($row as $name => $cnt) {
								if ( stristr($name,'_')=='_points' && $cnt==$evData['p_correct']) ++$correcttip;
							}
							echo $el['score_plus'].'</font>('.$correcttip.'x)'; 
						}
					}
				echo '</nobr></td>';
				if($evData['stake_mode']=='permatch'){
					echo '<td class="ow">'.$el['jackpot'].'</td>';
					$jackpot += $el['jackpot'];
				}
				foreach ($evUsers as $u){
					$player_column++;
					if( !isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']){
						//if the time of the game has come, then show the tips
						if ($showtips){
							echo '<td class="'.$rclass.'">x';
							if($evData['bet_on'] == 'results') echo ':';
						}else{
							$h = ($evData['score_input_type']=='results') ? $el['score_h'] : $el['score'];
							$v = ($evData['score_input_type']=='results') ? $el['score_v'] : $el[$u.'_toto'];
							$totoornot = ($evData['score_input_type']!='toto') ? $el[$u.'_toto'] : 'toto';
							$bet_h = ($evData['bet_on']=='results') ? $el[$u.'_h'] : $totoornot;
							$bet_v = ($evData['bet_on']=='results') ? $el[$u.'_v'] : 'toto';

							//the user tip can have different formats
							if (isCorrect($evData['p_correct'], $h, $v, $bet_h, $bet_v)) 
								$rclass = 'ow_correct';
							elseif (isDiff($evData['p_diff'], $h, $v, $bet_h, $bet_v)) 
								$rclass = 'ow_diff';
							elseif (isAlmost($evData['p_almost'], $h, $v, $bet_h, $bet_v)) 
								$rclass = 'ow_almost';
							else $rclass = 'ow_wrong';	
							echo '<td onMouseOver="Tip(\''.$userarray[$u].'<br />'
								.'<img  src=&quot;./data/user_img/'.$picture[$u].'&quot; '
								.'alt=&quot;'.$lang['general_nopic'].'&quot;/>\');" '
								.'onMouseOut="UnTip();" class="'.$rclass.'">';
							$toto = $el[$u.'_toto'];
							if ($toto == 3) $toto = 'X';
							echo ($evData['bet_on'] == 'results') ? $el[$u.'_h'].':' : $toto;
						}
					}
					if( !isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']){
						if($evData['bet_on'] == 'results') echo ($showtips) ? 'x' : $el[$u.'_v'];
						echo '</td>';
					}
					//$rclass='ow_wrong';
					$points[$u] += $el[$u.'_points']; //for the summaries
					$money[$u] += $el[$u.'_money']; //for the summaries
				}
		
			echo '</tr>'."\r\n";
		}
		
		//the end summary
		echo '<tr class="ow_summaryall">';
		echo '<td>'.$lang['overview_summaryall'].'</td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		if($evData['stake_mode']=='permatch') echo '<td class="ow">'.$jackpot_all.'</td>';
		$player_column = 0; // set player_column 0 again.. 
		foreach ($evUsers as $u){
			$player_column++;
			if( !isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']){
				if($evData['stake_mode']=='permatch') $display_m = $money_all[$u].' & ';
				echo '<td class="ow">'.$display_m.$points_all[$u].'</td>';
			}
		}
		echo '</tr>';
		
		echo '</table>';
		echo '</div>';
	}

}

}else{
	errorPage('notinevent');
}

?>

