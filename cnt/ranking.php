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


//this file has a simluation function built in: remove one slash in all //*simulation*/ lines

echo '<h2>'.$lang['ranking_title'].'</h2>';

global $db, $settings, $events;

//event handling ;) => estimate if user is registerd to events & load the events
$nb =  UserEventNumber();
$userevents = loadUserEvents();

if($nb < 1){
	//no events
	echo $lang['loginhome_noevent'];
	
}elseif($nb == 1){
	//one event
	$thisevent = ereg_replace('([0-9]+):$', '\\1', $userevents['approved']);
	
}elseif($nb > 1){
	//multible events
	//=> 2buttons (one hidden) and a vmenu
	createVerticalMenu(NULL, 'peventlist');
	createVerticalMenu(NULL, 'mmopen');
	createVerticalMenu(NULL, 'mmclose');
	//the session variable currevent must either a public event or the user participates. It can be in the session
	//after having looked at a public event in the overview section
	(isset($_SESSION['currevent']) && userParticipates($_SESSION['currevent'], $_SESSION['userid'])) ? 
			$thisevent = $_SESSION['currevent'] : $thisevent = ereg_replace('.*:([0-9]+):$', '\\1', $userevents['approved']);
}


//$_REQUEST['ev'] overrules the insight of the event handling :)
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent;
//update the current event variable in Session
$_SESSION['currevent'] = $_REQUEST['ev'];

//when a curious user modiefies the url...
if(!userParticipates($_REQUEST['ev']) && $nb > 0){
	//if the user is registered to an event and tries to view the rankinrankingg of another event
	errorPage('notinevent');
}elseif($nb>0){
//=============== acutal content

echo '<h3>'.$events['u']['e'.$_REQUEST['ev']]['name'].'</h3>';
	
//select from db ordered by time and only matches with results!
//*simulation*/ /*
$queryfield = ($events['u']['e'.$_REQUEST['ev']]['score_input_type'] == 'results') ? 'score_h' : 'score';
$query = "SELECT * FROM ".PFIX."_event_".$_REQUEST['ev']." WHERE ".$queryfield." IS NOT NULL ORDER BY time ASC;";
$pastmatches =  $db->query($query);
$rows =  $db->row_count($query);
//if there are no matches yet for this event
if ($rows == 0) {
	echo $lang['general_nomatches'];
}else{

	?>
	<script type="text/javascript">
		function showUntil(url,what){
		var val;
			if(what=="matchday_id"){
				val = document.getElementById("matchday_id").value;
				document.location = url + "showuntil="+val;
			}else if(what=="dates"){
				val = document.getElementById("dates").value;
			}else if(what=="matches"){
				val = document.getElementById("matches").value;
			}
		document.location = url + "showuntil="+val;
		}
	</script>
	<?


	$cleanurl = eregi_replace('(showuntil=)[a-zA-Z0-9:_]+[&]', '', $link_query);
	$cleanurl = $link.$cleanurl;
	
	echo $lang['ranking_showrankinguntil'];
	echo '<form style="display:inline;">';
		echo ' <b>'.$lang['admin_events_matchday'].'</b> ';
		echo '<select id="matchday_id" onChange="javascript: showUntil(\''.$cleanurl.'\', \'matchday_id\')">';
			$mdids = $db->query("SELECT DISTINCT matchday, matchday_id 
						FROM ".PFIX."_event_".$_REQUEST['ev']." 
						WHERE ".$queryfield." IS NOT NULL ORDER BY matchday_id ASC;");
			echo '<option value="none"></option>';
			$counter = 0;
			foreach ($mdids as $row) {
				$counter++;
				if($_REQUEST['showuntil'] == 'matchday_id:'.$row['matchday_id']){
					$type = $lang['admin_events_matchday'];
					$selected = 'selected';
					if(isset($mdids[$counter-2]))
						$ante = '<a href="'.$cleanurl.'showuntil=matchday_id:'
							.$mdids[$counter-2]['matchday_id'].'">'
							.$lang['general_goback'].'</a>';
					if(isset($mdids[$counter]))
						$post = '<a href="'.$cleanurl.'showuntil=matchday_id:'
							.$mdids[$counter]['matchday_id'].'">'
							.$lang['general_goforward'].'</a>';
				}else{
					$selected = '';
				}
				echo '<option value="matchday_id:'.$row['matchday_id'].'" '.$selected.'>'.$row['matchday'].'</option>';
				}
		echo '</select>';
		echo ' / <b>'.$lang['general_date'].'</b> ';
		echo '<select id="dates" onChange="javascript: showUntil(\''.$cleanurl.'\', \'dates\')">';
			echo '<option value="none"></option>';
			$dates = $db->query("SELECT DISTINCT FROM_UNIXTIME(time, '%d.%m.%Y') AS date, 
						FROM_UNIXTIME(time, '%Y%m%d') AS vdate  
						FROM ".PFIX."_event_".$_REQUEST['ev']." 
						WHERE ".$queryfield." IS NOT NULL ORDER BY time ASC;");
			$counter = 0;
			foreach ($dates as $row) {
				$counter++;
				if($_REQUEST['showuntil'] == 'date:'.$row['vdate']){
					$type = $lang['general_date'];
					$selected = 'selected';
					if(isset($dates[$counter-2]))
						$ante = '<a href="'.$cleanurl.'showuntil=date:'
							.$dates[$counter-2]['vdate'].'">'
							.$lang['general_goback'].'</a>';
					if(isset($dates[$counter]))
						$post = '<a href="'.$cleanurl.'showuntil=date:'
							.$dates[$counter]['vdate'].'">'
							.$lang['general_goforward'].'</a>';
				}else{
					$selected = '';
				}
				echo '<option value="date:'.$row['vdate'].'" '.$selected.'>'.$row['date'].'</option>';
				}
			echo '</select>';
		echo ' / <b>'.$lang['general_match'].'</b> ';
		echo '<select id="matches" onChange="javascript: showUntil(\''.$cleanurl.'\', \'matches\')">';
			$matches = $db->query("SELECT DISTINCT id 
						FROM ".PFIX."_event_".$_REQUEST['ev']." 
						WHERE ".$queryfield." IS NOT NULL ORDER BY time ASC;");
			echo '<option value="none"></option>';
			for($i=1;$i<=sizeof($matches);$i++){
				if($_REQUEST['showuntil'] == 'match:'.$i){
					$type = $lang['general_match'];
					$selected = 'selected';
					if(isset($matches[$i-2]))
						$ante = '<a href="'.$cleanurl.'showuntil=match:'
							.($i-1).'">'
							.$lang['general_goback'].'</a>';
					if(isset($matches[$i]))
						$post = '<a href="'.$cleanurl.'showuntil=match:'
							.($i+1).'">'
							.$lang['general_goforward'].'</a>';
				}else{
					$selected = '';
				}
				echo '<option value="match:'.$i.'" '.$selected.'>'.$i.'</option>';
			}
			echo '</select>';
	echo '</form>';
	if(isset($ante) && isset($post))
		$steplinks = $ante.' | '.$post;
	else 
		$steplinks = $ante.$post;
	echo '<br/><br/>';

 //SELECT DISTINCT FROM_UNIXTIME( time, '%d.%m.%Y' ) FROM zock_event_9

	if(isset($_REQUEST['showuntil'])){
		$info = rankingCalculate($_REQUEST['ev'], $_REQUEST['showuntil']);
		$addtosorturl = '&showuntil='.$_REQUEST['showuntil'];
	}else{
		$info = rankingCalculate($_REQUEST['ev']);
	}

	//get info for tooltips
        //recenttips
	$query = "SELECT * FROM ".PFIX."_event_".$_REQUEST['ev']." WHERE ".$queryfield." IS NOT NULL ORDER BY time ASC;";
    $rawdata=$db->query($query);
    $showrecent = 5;
    while ($showrecent-- != 0) {
        $recenttips[] = array_pop($rawdata);
    }
        //nexttips
	$query = "SELECT * FROM ".PFIX."_event_".$_REQUEST['ev']." WHERE ".$queryfield." IS NULL ORDER BY time ASC;";
	$rawdata=$db->query($query);
	foreach ($rawdata as $row){
		if (betUntil($row['time'],$_REQUEST['ev']) < time()) $nexttips[] = $row;
	}


	//event is over!
	if ($info["pastmatches"] == $info['totalmatches']){
		$over = true;
		if (!isset($_REQUEST['sort']))
			$_REQUEST['sort'] = 'provgain';
			$lang['ranking_provisorygain'] = $lang['ranking_totalgain'];
	}

	$evinfo = $events['u']['e'.$_REQUEST['ev']];
	$difftrue = ($events['u']['e'.$_REQUEST['ev']]['p_diff'] == NULL) ? false:true;
	$almosttrue = ($events['u']['e'.$_REQUEST['ev']]['p_almost'] == NULL) ? false:true;



	echo '<table class="ranking">';
	echo '<tr><td colspan="0">';
		if(isset($type)) echo '  '.$type.': '.$steplinks.'<br/>';
		echo substitute($lang['ranking_showingxoutofx'], Array($info['pastmatches'], $info['totalmatches']));
	echo '</td></tr>';
	echo '<tr class="title">
			<td class="title">'.$lang['ranking_rank'].'</td>
			<td class="title">'.$lang['general_who'].'</td>
			<td class="title"><a href="'.$link.'sort=points'.$addtosorturl.'">'.$lang['ranking_points'].'</a></td>';
			if ($evinfo['stake_mode']== 'permatch'){
				echo '<td class="title"><a href="'.$link.'sort=gain'.$addtosorturl.'">'.$lang['ranking_gain'].'</a></td>';
				echo '<td class="title"><a href="'.$link.'sort=jackpotshare'.$addtosorturl.'">'.$lang['ranking_jackpotshare'].'</a></td>';
			}
			echo '<td class="title"><a href="'.$link.'sort=provgain'.$addtosorturl.'">'.$lang['ranking_provisorygain'].'</a></td>
			<td class="title"><a href="'.$link.'sort=correct'.$addtosorturl.'">'.$lang['ranking_correcttips'].'</a></td>';
			if($difftrue) echo '<td class="title"><a href="'.$link.'sort=diff'.$addtosorturl.'">'.$lang['ranking_difftips'].'</a></td>';
			if($almosttrue) echo '<td class="title"><a href="'.$link.'sort=almost'.$addtosorturl.'">'.$lang['ranking_almosttips'].'</a></td>';
			echo '<td class="title"><a href="'.$link.'sort=wrong'.$addtosorturl.'">'.$lang['ranking_wrongtips'].'</a></td>
		</tr>';

	//get usernames in array
	$usersraw = $db->query("SELECT id, login, picture FROM ".PFIX."_users");
	foreach ($usersraw as $u){
		$userarray[$u['id']] = $u['login'];
		$idx = strrpos($u['picture'],'.');
		$fext = substr($u['picture'],$idx);
		$fn = substr($u['picture'],0,$idx);
		$picture[$u['id']] = $fn.'@thumb'.$fext;
	}


	switch ($_REQUEST['sort']){
		case 'gain':
			$listsource = $info['money'];
			arsort($listsource);
			$cl_gain = 'class="highlighted"';
			break;
		case 'provgain':
			$evUsers = (explode(':', $events['u']['e'.$_REQUEST['ev']]['a']));
			array_pop($evUsers);
			foreach($evUsers as $id){
				$listsource[$id] = $info['jackpots'][$info['rank'][$id]]+$info['money'][$id];
			}
			arsort($listsource);
			$cl_totgain = 'class="highlighted"';
			break;
		case 'correct':
			$listsource = $info['correct'];
			arsort($listsource);
			$cl_correct = 'class="highlighted"';
			break;
		case 'diff':
			$listsource = $info['diff'];
			arsort($listsource);
			$cl_diff = 'class="highlighted"';
			break;
		case 'almost':
			$listsource = $info['almost'];
			arsort($listsource);
			$cl_almost = 'class="highlighted"';
			break;
		case 'wrong':
			$listsource = $info['wrong'];
			arsort($listsource);
			$cl_wrong = 'class="highlighted"';
			break;
		default: 
			$listsource = $info['rank'];	
			asort($listsource);
			$cl_points = 'class="highlighted"';
			$cur_rank = 0;
			foreach ($listsource as $u => $r){
				if ($cur_rank == $info['rank'][$u]){
					$info['rank'][$u] = '"'; 
				}else{
					$cur_rank = $info['rank'][$u];
				}
			}
			break;
	}



	foreach($listsource as $u => $r){


		//making tooltip
        $tooltip = "";
		$tooltip .= '<u>'.$lang['ranking_recenttips'].'</u>';
        $rts = array_reverse($recenttips);
        foreach($rts as $rt)
            $tooltip .= '<br/>'.$rt['home'].' - '.$rt['visitor'].': '.$rt[$u.'_h'].':'.$rt[$u.'_v'];
	    $tooltip .= '<br />'
			.'<img  src=&quot;./data/user_img/'.$picture[$u].'&quot; '
			.'alt=&quot;'.$lang['general_nopic'].'&quot;/>';
		if (sizeof($nexttips) != 0){
			$tooltip .= '<br/><u>'.$lang['ranking_nexttips'].'</u>';
			foreach($nexttips as $nt)
				$tooltip .= '<br/>'.$nt['home'].' - '.$nt['visitor'].': '.$nt[$u.'_h'].':'.$nt[$u.'_v'];
		}elseif($over){
			$tooltip .= $lang['general_bettinggameover'];
		}else{
			$tooltip .= $lang['ranking_waitfortips'];
		}

		//alternative rankings
		if ($_REQUEST['sort'] != 'points' && 
			isset($_REQUEST['sort']) && 
			$_REQUEST['sort'] != 'jackpotshare'){

			$rankcounter++;
			$lastval = $thisval;
			$thisval = $r; 
			$secondaryrank = ($lastval == $thisval) ? '"' : $rankcounter;
		}
		$rankrepresentation = (isset($secondaryrank)) ? 
					'<b>'.$secondaryrank.'</b>   ('.$info['rank'][$u].')' : 
					'<b>'.$info['rank'][$u].'</b>';
		unset($secondaryrank);
	
		//the ranking table
		echo '<tr>
				<td class="highlighted">'.$rankrepresentation.'</td>
				<td onmouseover="Tip(\''.$tooltip.'\');" onmouseout="UnTip();"><a href="?menu=participants&showuser='.$u.'">'.$userarray[$u].'</a></td>
				<td '.$cl_points.'>'.$info['points'][$u].'</td>';
				if ($info['rank'][$u] != '"') 
					$thisranksjackpot = $info['jackpots'][$info['rank'][$u]];
				if($evinfo['stake_mode']=='permatch'){
					echo '<td '.$cl_gain.'>'.$info['money'][$u].'</td>';
					echo '<td>'.$thisranksjackpot.'</td>';
				}
				if($evinfo['stake_mode'] != 'none') 
					echo '<td '.$cl_totgain.'>'.($thisranksjackpot+$info['money'][$u]).'</td>';
				echo '<td '.$cl_correct.'>'.$info['correct'][$u].'</td>';
				if($difftrue) echo '<td '.$cl_diff.'>'.$info['diff'][$u].'</td>';
				if($almosttrue) echo '<td '.$cl_almost.'>'.$info['almost'][$u].'</td>';
				echo '<td '.$cl_wrong.'>'.$info['wrong'][$u].'</td>
			</tr>';

	}

	//summary of points and money

	//even if multiple users are on the same rank
	//there is only one entry for the rank in $info['jackpots']
	//=> throw the missing money in!
	foreach($info['jackpots'] as $r => $s){
		if ($info['r_quant'][$r] > 1) $info['jackpots'][$r.'_missing'] = ($info['r_quant'][$r]-1) * $s;
	}

	echo '<tr class="ow_summary">
			<td>'.$lang['overview_summary'].'</td>
			<td></td>
			<td></td>';
			if($evinfo['stake_mode']=='permatch'){
				echo '<td>'.array_sum($info['money']).'</td>
				<td>'.array_sum($info['jackpots']).'</td>';
			}
			echo '<td>'.(array_sum($info['money'])+array_sum($info['jackpots'])).'</td>
			<td>'.array_sum($info['correct']).'</td>';
			if($difftrue) echo '<td>'.array_sum($info['diff']).'</td>';
			if($almosttrue) echo '<td>'.array_sum($info['almost']).'</td>';
			echo '<td>'.array_sum($info['wrong']).'</td>
		</tr>';
	echo '</table>';
//*simulation*/ /*

//*simulation*/

}
}//content
?>
