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



echo '<h2>'.$lang['mytips_title'].'</h2>';


//========== show matches & results
global $db, $settings, $events, $events_test;


//event handling ;) => estimate if user is registerd to events
$nb =  UserEventNumber();
$userevents = loadUserEvents();
if($nb < 1){
	//no event
	echo $lang['mytips_participatefirst'];
	
}elseif($nb == 1){
	//one event
	$thisevent = preg_replace('/([0-9]+):$/', '\\1', $userevents['approved']);
}elseif($nb > 1){
	//multiple events
	//a vmenu to navigate between events
	createVerticalMenu(NULL, 'ueventlist');
	createVerticalMenu(NULL, 'mmopen');
	createVerticalMenu(NULL, 'mmclose');
	//the session variable currevent must either a public event or the user participates. It can be in the session
	//after having looked at a public event in the overview section
	(isset($_SESSION['currevent']) && userParticipates($_SESSION['currevent'], $_SESSION['userid'])) ? 
			$thisevent = $_SESSION['currevent'] : $thisevent = preg_replace('/.*:([0-9]+):$/', '\\1', $userevents['approved']);
}
//$_REQUEST['ev'] overrules the insight of the event handling :)
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent;
//update the current event variable in Session
$_SESSION['currevent'] = $_REQUEST['ev'];


//when a curious user modiefies the url...
if(!userParticipates($_REQUEST['ev']) && $nb > 0){
	//if the user is registered to an event and tries to view the comments of another event
	errorPage('notinevent');
	exit;
}

$evdat = $events['u']['e'.$_REQUEST['ev']];
echo '<h3>'.$evdat['name'].'</h3>';


if($nb >= 1 && !(isset($_REQUEST['mtac']))){
	//show all of it!

	// error handling....
	if (isset($_SESSION['err'])){
		$err = $_SESSION['err'];
		unset($_SESSION['err']);
		$data = $_SESSION['post'];
		unset($_SESSION['post']);
		echo '<p />'.errorMsg('filledform');
		foreach ($err as $id){
			$wrongs[$id] = 'error';
		}
	}

	//filtering
	if (isset($_REQUEST['filter'])){
		$f = preg_split('/:/', $_REQUEST['filter']);
		switch ($f[0]){
			case 'team':
				$f_team = 'selected';
				break;
			case 'home';
				$f_home = 'selected';
				break;
			case 'visitor';
				$f_visitor = 'selected';
				break;
			case 'matchday';
				$f_matchday = 'selected';
				break;
		}
	}

    $event = $events_test->getEventById($_REQUEST['ev']);
    $results = $event->getResults();
    $bdp_matches = $results->getBetsAndResults($_REQUEST['filter'],$_REQUEST['orderby']);
	$bdp_rows =  sizeof($bdp_matches);
	
	//$mnb stands for Match NumBer, is necessary to limit the amount of matches displayed
	$mnb = (isset($_REQUEST['mnb'])) ? $_REQUEST['mnb'] : 1;

	if($bdp_matches == NULL && !isset($_REQUEST['filter'])){

		//well, there aren't any matches
		echo $lang['general_nomatches'];
		echo ' ('.$events['u']['e'.$_REQUEST['ev']]['name'].')';
	}else{
		if($bdp_rows == 0 && isset($_REQUEST['filter'])){
			//no results with this filter
			echo errorMsg('filter_emptyresults');
		}



		//filterform
		$filterurl = preg_replace('/(filter=)[a-zA-Z0-9:]+[&]/i', '', $link_query); 
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

		//the form (begins already here)
		echo '<form action="'.$link.'mtac=savetips" method="POST" name="matches">';
		
		$tipplus = '( 1 /';
		if(!($evdat['ko_matches']=='only' && $evdat['enable_tie']=='no')){
			$tipplus .= ' X /';
			$colspan = 3;
		}else{
			$colspan = 2;
		}
		$tipplus .= ' 2 )';

		//content
		echo '<table class="showmatches" id="showresults">';
		echo '<tr class=title>
			<td class=title><a href="'.$link.orderIt('time', $orderby, $link_query).'"> '.$lang['admin_events_time'].'</a></td>
			<td class=title><a href="'.$link.orderIt('matchday_id', $orderby, $link_query).'"> '.$lang['admin_events_matchday'].'</a></td>
			<td class=title><a href="'.$link.orderIt('home', $orderby, $link_query).'"> '.$lang['admin_events_home'].'</a></td>
			<td class=title><a href="'.$link.orderIt('visitor', $orderby, $link_query).'"> '.$lang['admin_events_visitor'].'</a></td>
			<td class=title>'.$lang['admin_events_score'].'</td>';
			if($evdat['bet_on']=='results'){
				echo '<td class=title>'.$lang['mytips_tip'].'</td>';
			}else{
				echo '<td class=title colspan="'.$colspan.'">'.$lang['mytips_tip'].' '.$tipplus.'</td>';
			}
			echo '<td class=title>'.$lang['mytips_sametip'].'</td>
			<td class=title>'.$lang['mytips_tendency'].'</td>

			</tr>';
	
	//estimate page to display if nothing else specified


		if (!isset($_REQUEST['orderby']) && !isset($_REQUEST['mnb'])){
			$closestGame = closestGame($_REQUEST['ev'], time()+abs(betUntil(0, $_REQUEST['ev'])));
			$page = ($closestGame%$settings['formlines'] == 0)  ? 
				$closestGame/$settings['formlines'] - 1  : 
				floor($closestGame/$settings['formlines']);
			$mnb = $page * $settings['formlines'] + 1;
		}

		//if filter is set, watch out that mnb is not too high
		if($bdp_rows < $mnb) $mnb = 1; 	

		foreach($bdp_matches as $nb => $m){
			
			$start = $mnb;
			$limit = $mnb + $settings['formlines'];
			
			if ($nb+1 >= $start && $nb+1 < $limit){
			
				$lines++;
				$ids .= $m['id'].':';	
				$id = $m['id'];
	
				//further error handling
				//=>decide if the data in the forms should come from db or error the $_post array
				if (isset($wrongs)){
					if(isset($wrongs[$m['id']])) $id =  '<font class=error>-></font>';
					$score_h = $data['score_h_'.$m['id']];
					if ($score_h == 'NULL') $score_h = '';
					$score_v = $data['score_v_'.$m['id']];
					if ($score_v == 'NULL') $score_v = '';
				}else{
					$score_h = $m[$_SESSION['userid'].'_h'];
					$score_v = $m[$_SESSION['userid'].'_v'];
					$checked[$m[$_SESSION['userid'].'_toto']] = 'checked="checked"';
					$toto = $m[$_SESSION['userid'].'_toto'];
				}

				//still editable or not??
				$betuntil = betUntil($m['time'],$_REQUEST['ev']);
				$now = time();
				if ($betuntil<$now){
					//no, not editable
					$robool = "true";
					$ro = 'class="readonly" readonly="readonly"';
				}else{
					//yes, it is
					$robool = "false";
					$ro = 'class=""';
					$dis = 'class=""';
				}
				
				// same tips?
				$sametip = 0;
				$playasofevent = explode(':', $events['u']['e'.$_REQUEST['ev']]['a']);
				array_pop($playasofevent);
				if($evdat['bet_on']=='results'){
					foreach ($playasofevent as $p){
						if ($m[$p.'_h'] == $score_h && $m[$p.'_v'] == $score_v)
							$sametip++;
					}
				}else{
					foreach ($playasofevent as $p){
						if ($m[$p.'_toto'] == $toto)
							$sametip++;
					}
				}
				$sametip -= 1;

				// tendency

				$toto0 = 0;
				$toto1 = 0;
				$toto2 = 0;
				$totoX = 0;

				foreach ($playasofevent as $p){
					if($evdat['bet_on']=='results'){
						if($m[$p.'_h'] == '' || $m[$p.'_v'] == ''){
							++$toto0;
						}else if($m[$p.'_h'] > $m[$p.'_v']){
							   ++$toto1;		}
						else if($m[$p.'_h'] < $m[$p.'_v']){
						   ++$toto2;
						}else{
						   ++$totoX;
						}
					}elseif($evdat['bet_on']=='toto'){
						if ($toto == 0) ++$toto0;
						elseif ($toto == 1) ++$toto1;
						elseif ($toto == 2) ++$toto2;
						elseif ($toto == 3) ++$totoX;
					}
				}
				$toto_all= count($playasofevent) - $toto0;

				if($toto1>0){$toto_trend1=round($toto1/$toto_all*100);}else{$toto_trend1=0;}
				if($totoX>0){ $toto_trendX=round($totoX/$toto_all*100);}else{$toto_trendX=0;}
      				if($toto2>0){ $toto_trend2=round($toto2/$toto_all*100);}else{$toto_trend2=0;}


				$time1 = date('d.m.Y', $m['time']);
				$time2 = date('H:i', $m['time']);
				$matchday = $m['matchday'];
				$home = $m['home'];
				$visitor = $m['visitor'];	
				if($evdat['score_input_type'] == 'results'){
					$result = $m['score_h'].' : '.$m['score_v'];
				}else{
					$result = $m['score'];
					if($result == 3) $result = 'X';
				}
	
				//the form can continue here
				echo '<tr>
					<td class="input">'.weekday($m['time'],1).', '.$time1.' '.$lang['general_time_at'].' '.$time2.'</td>
					<td class="input">'.$matchday.'</td>
					<td class="input">'.$home.'</td>
					<td class="input">'.$visitor.'</td>
					<td class="input">'.$result.'</td>';
					if($evdat['bet_on']=='results'){
						echo '<td class="input"><nobr><input id="h_'.$m['id'].'" 
									'.$ro.'
									name="score_h_'.$m['id'].'" 
									size="2" value="'.$score_h.'"> : '
								.'<input id="v_'.$m['id'].'" 
									'.$ro.'
									name="score_v_'.$m['id'].'" 
									size="2" value="'.$score_v.'"></nobr></td>';
					}elseif($evdat['bet_on']=='toto'){
						if($robool=='true'){
							echo '<td class="input" colspan="'.$colspan.'">'.$toto.'</td>';
						}else{
							echo '<td class="input">';
								echo '<input class="'.$dis.'" type="radio" value="1" '.$checked['1'].' name="toto_'.$m['id'].'">';
							echo '</td>';
							if(!($evdat['ko_matches']=='only' && $evdat['enable_tie']=='no')){
								echo '<td class="input">';
									if($m['komatch'] && $evdat['enable_tie']!='yes')
										echo '--';
									else
										echo '<input class="'.$dis.'" type="radio" value="3" '.$checked['3'].' name="toto_'.$m['id'].'">';
								echo '</td>';
							}
							echo '<td class="input">';
								echo '<input class="'.$dis.'" type="radio" value="2" '.$checked['2'].' name="toto_'.$m['id'].'">';
							echo '</td>';
						}
					}
					echo '<td class="input">'.$sametip.'</td>
					<td class="input">'.$toto_trend1.' : '.$toto_trendX.' : '.$toto_trend2.'</td>
					</tr>';
				echo '<input id="ro_'.$m['id'].'" name="ro_'.$m['id'].'" type="hidden" value="'.$robool.'">';
				echo '<input id="komatch_'.$m['id'].'" name="komatch_'.$m['id'].'" type="hidden" value="'.$m['komatch'].'">';
				unset($checked);
			}
		}


		echo '<input name="query" type="hidden" value="'.$link_query.'">';
		echo '<input name="event" type="hidden" value="'.$_REQUEST['ev'].'">';
		echo '<input name="ids" type="hidden" value="'.$ids.'">';
		echo '<input name="mnb" type="hidden" value="'.$mnb.'">';
		echo '<input name="orderby" type="hidden" value="'.$_REQUEST['orderby'].'"';
		echo '<tr class="submit"><td class="submit" colspan="4"><input type="submit" value="'.$lang['general_savechanges'].'"></td></tr>';
		echo '</table>';
		//the form finishes here
		echo '</form>';
		echo '<p />';



		//skip pages
		if (!(isset($err))){
			$queryfilter = preg_replace( '/mnb=([0-9]+)([& ])/', '', $link_query);
			if($mnb > 1){
				$gonb = $mnb-$settings['formlines'];
				if ($gonb < 1) $gonb = 1;
				echo '<a href="'.$link.$queryfilter.'mnb='.$gonb.'">'.$lang['general_goback'].'</a> | ';
			}
	
			echo $lang['general_page'];
			for($x=1 ; $x <= $bdp_rows; $x += $settings['formlines']){
				$y++;
				if ($x!=$mnb){
					echo '  <a href="'.$link.$queryfilter.'mnb='.$x.'">'.$y.'</a>';
				}else{
					echo '  '.$y;
				}
			}
	

			if($mnb + $settings['formlines'] < $bdp_rows){
				$gonb = $mnb+$settings['formlines'];
				if ($gonb > $bdp_rows) $gonb = $bdp_rows;
				echo ' | <a href="'.$link.$queryfilter.'mnb='.$gonb.'">'.$lang['general_goforward'].'</a>';
			}
		}
	}

//========== save tips
}elseif($_REQUEST['mtac'] == 'savetips'){

	echo $lang['general_updating'].'<br>'.$lang['general_redirect'];

	//make array with ids to update
	$idar = explode(':', $_POST['ids']);
	$ok = Array();
	$err = Array();	
	foreach($idar as $id){
		
		//if it wasn't editable, it's not worth updating it
		if ($_POST['ro_'.$id] == "false"){
			
			if($evdat['bet_on']=='results'){
			

				//pepare for check => delicate with NULL & zero
				if ($_POST['score_h_'.$id] == "") $_POST['score_h_'.$id] = "NULL";
				if ($_POST['score_v_'.$id] == "") $_POST['score_v_'.$id] = "NULL";


				//check if the etnries were corect
				if ( ( $_POST['score_h_'.$id] == "NULL" && $_POST['score_v_'.$id] == "NULL" )
						|| ( is_numeric($_POST['score_h_'.$id]) && is_numeric($_POST['score_v_'.$id]) ) ){
					$ok[] = $id;
				}else{
					$err[] = $id;
				}
			}elseif($evdat['bet_on']=='toto'){
				if ($_POST['toto_'.$id] == "") $_POST['toto_'.$id] = "NULL";
				$ok[] = $id;
			}
			
		}
	}
	
	if (isset($err) && sizeof($err)>0){
		$_SESSION['err'] = $err;
		$_SESSION['post'] = $_POST;
		//go back without updating but with a lot of information
		redirect( preg_replace('/(&mtac=savetips)/', '',$rlink.$link_query.$_POST['query']), 0);
		
	}else{
		//update	
		foreach($ok as $x){
			
			if($evdat['bet_on']=='results'){
				//no apostrophes for scores, because  'NULL' => 0
				$query_changes = " UPDATE ".PFIX."_event_".$_POST['event']."
							SET ".$_SESSION['userid']."_h = ".$_POST['score_h_'.$x].",
							".$_SESSION['userid']."_v = ".$_POST['score_v_'.$x]."
							WHERE id = '".$x."';";
			}elseif($evdat['bet_on']=='toto'){
				//no apostrophes for scores, because  'NULL' => 0
				$query_changes = " UPDATE ".PFIX."_event_".$_POST['event']."
							SET ".$_SESSION['userid']."_toto = ".$_POST['toto_'.$x]."
							WHERE id = '".$x."';";
				
			}
			if($db->query($query_changes)){
				echo $lang['general_savedok'];
				redirect( preg_replace('/(&mtac=savetips)/', '',$rlink.$link_query.$_POST['query']), 3);
			}else{
				redirect( preg_replace('/(&mtac=savetips)/', '',$rlink.$link_query.$_POST['query']), 2);
			}
		}
		
	}



}

?>

