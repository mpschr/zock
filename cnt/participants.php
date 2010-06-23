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

echo '<h2>'.$lang['participants_title'].'</h2>';

global $db, $settings, $events;

$nb =  UserEventNumber();
$userevents = loadUserEvents();
if($nb < 1){
	// no events
	echo $lang['loginhome_noevent'];
	
}elseif($nb == 1){
	//one event, one possibility..
	$thisevent = ereg_replace('([0-9]+):$', '\\1', $userevents['approved']);
	
}elseif($nb > 1){
	//multiple events
	//a vmenu to navigate between events
	createVerticalMenu(NULL, 'ueventlist');
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
	//if the user is registered to an event and tries to view the comments of another event
	errorPage('notinevent');
	exit;
}




// get the users & their names
$usersraw = $db->query("SELECT id, login FROM ".PFIX."_users");
foreach ($usersraw as $u) $userarray[$u['id']] = $u['login'];

//which and how many users
$nb2 = eventUserNumber($_REQUEST['ev']); 


if($nb2 != NULL && $nb != NULL){
//showit all
	echo '<h3>'.$events['u']['e'.$_REQUEST['ev']]['name'].'</h3>';
	echo $lang['participants_content'];
	echo '<div class="appearance">';
	if(isset($_REQUEST['showuser'])){
		if (userParticipates($_REQUEST['ev'], $_REQUEST['showuser']) == TRUE){
			$details = $db->query("SELECT login, picture, name, famname, text FROM ".PFIX."_users WHERE id='".$_REQUEST['showuser']."';");
			$imgsrc = 'data/user_img/'.$details[0]['picture'];
			@$imgsize = getimagesize($imgsrc);
			if($imgsize[0] > 270){
				$origwidth = $imgsize[0];
				$imgsize[0] = 270;
				$change = $imgsize[0]/$origwidth;
				$imgsize[1] = $imgsize[1]*$change;
			}
				
				$correct = $almost = $diff = $wrong = 0;
				$rawdata = $db->query("SELECT score_h, score_v, ".$_REQUEST['showuser']."_h, ".$_REQUEST['showuser']."_v, ".$_REQUEST['showuser']."_points, ".$_REQUEST['showuser']."_ranking, ".$_REQUEST['showuser']."_money FROM ".PFIX."_event_".$_REQUEST['ev']. " WHERE score_h IS NOT NULL ORDER BY time");
				foreach ($rawdata as $row){
					if ($row['score_h'] == $row[$_REQUEST['showuser'].'_h'] && $row['score_v'] == $row[$_REQUEST['showuser'].'_v'])
						$correct++;
                    else if ($row['score_h'] - $row['score_v'] == $row[$_REQUEST['showuser'].'_h'] - $row[$_REQUEST['showuser'].'_v'])
                        $diff++;
					elseif ($row['score_h'] >  $row['score_v'] && $row[$_REQUEST['showuser'].'_h'] > $row[$_REQUEST['showuser'].'_v'] ||
						$row['score_h'] < $row['score_v'] && $row[$_REQUEST['showuser'].'_h'] < $row[$_REQUEST['showuser'].'_v'] ||
						$row['score_h'] ==  $row['score_v'] && $row[$_REQUEST['showuser'].'_h'] == $row[$_REQUEST['showuser'].'_v'] && $row['score_h'] != NULL)
						$almost++;
					$points += $row[$_REQUEST['showuser'].'_points'];
					$pointscurve .= $points.':';
					$money += $row[$_REQUEST['showuser'].'_money'];
					$rank = ($row[$_REQUEST['showuser'].'_ranking'] != NULL) ? $row[$_REQUEST['showuser'].'_ranking'] : $rank;
				}
                $pointscurve .= ';'.$pointscurve.';';
				$wrong = sizeof($rawdata) - $almost - $correct - $diff;
				$gamestandings = $lang['ranking_rank'].': <b>'.$rank.'</b><br/> '
						.$lang['ranking_points'].': <b>'.$points.'</b><br/> '
						.$lang['ranking_gain'].': <b>'.$money.' '.$events['p']['e'.$_REQUEST['ev']]['currency'].'</b><p/>'
						.$lang['participants_correcttips'].': <b>'.$correct.'</b><br/>'
						.$lang['participants_difftips'].': <b>'.$diff.'</b><br/>'
						.$lang['participants_closetips'].': <b>'.$almost.'</b><br/>'
						.$lang['participants_wrongtips'].': <b>'.$wrong.'</b><br/>';

				echo '<table align="center">';
				echo '<tr><td colspan="2"><b>'.$details[0]['login'].'</b> aka '.$details[0]['name'].' '.$details['0']['famname'].'<p /></td></tr>';
				echo '<tr><td><img title="'.$details[0]['login'].'" src="'.$imgsrc.'" alt="'.$lang['myprofile_appearance_nopicture'].'" width="'.$imgsize['0'].'px" height="'.$imgsize[1].'px">';
				echo '<br/><font class="piccomment">'.$details[0]['text'].'</font></td>';
				echo '<td class="participantdetails">'.$gamestandings.'<p/><a href="'.$link.'&menu=overview&u='.$_REQUEST['showuser'].'">'.$lang['mytips_tips'].'</a></td></tr>';
				echo '<tr><td colspan="2"><p /><a href="'.$link.'ev='.$_REQUEST['ev'].'">'.$lang['general_goback'].'</a></td></tr></table>';
				echo '<object data="cnt/participantsSVG.php?u='.$details[0]['login'].'&curves='.$pointscurve.'&title=Points&description=Points" 
					width="450" height="250" type="image/svg+xml" />';
		}else{
		//if the requested user doesn't participate in the given event
			echo errorMsg('doesnotparticipate');
		}
	}else{
		$evUsers = (explode(':', $events['u']['e'.$_REQUEST['ev']]['a']));
		array_pop($evUsers);
		$up = 1;
		echo '<table width=100%>';
		foreach($evUsers as $u){
			$details = $db->query("SELECT login, picture, name, famname, text FROM ".PFIX."_users WHERE id='".$u."';");
			$idx = strrpos($details[0]['picture'],'.');
			$fext = substr($details[0]['picture'],$idx);
			$fn = substr($details[0]['picture'],0,$idx);
			$imgsrc = './data/user_img/'.$fn.'@thumb'.$fext;
			if($up%3 == 1) echo '<tr>';
			echo '<td width="33%"><a href="'.$link.$link_query.'showuser='.$u.'"><img src="'.$imgsrc.'"/><br/>'.$userarray[$u].'</a><br /></td>';
			if($up%3 == 0) echo '</tr>';
			$up++;
		}
		$empty = $up%3;
		for ($i = 1; $i<=$empty; $i++){
			echo '<td width="33%"> </td>';
			if ($i==$empty) echo '</tr>';
		}
		echo '</table>';
	}
	echo '</div>';
	
}else/*{
	echo $lang['participants_nousers'];
}*/
?>
