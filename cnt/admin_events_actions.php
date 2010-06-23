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


/* in this file are all the actions which are invoked by the section admin/events
	--> there are no actual sites in this file, but a lot of performance
	the reason for this is the fact that in the admin section, there are a lot of 
	forms which require a lot of testing. so we concentrated it in this one file*/

/*evacs in this file:
/	save
/	activate
/	saveactive
/	savematches
/	saveresults
/	changecompetitor
/	installevent
/	addemptymatches
/	arrangematchdays	
*/




//============= save an edited (inactive) event or create a new event (same procedure)

if($_REQUEST['evac'] == 'save'){
//verfying
	//name

	if($_POST['name'] == "") $err['name'] = 1;
	else $err['name'] = 0;


	//date
	$time = explode('.', $_POST['deadline']);
	if (checkdate($time[1],$time[0],$time[2])){
		$deadline =  mktime(0,0,0,$time[1],$time[0],$time[2]);
		$err['deadline'] = 0;
	}else{
		$err['deadline'] = 1;
	}



//reaction/conclusion

	//if there's an error...
	if(in_array(1, $err)){
		$_SESSION['err'] = $err;
		$_SESSION['post'] = $_POST;
		//return to formular
		if($_POST['formname']=='phase1') redirect($rlink.'evac=addnew');
		else redirect($rlink.$_POST['form']);
	}else{
	//saveit!
		echo $lang['general_updating'].'<br>';
		$data = $_POST;

		//conversion in UNIX-TIME
		$data['deadline'] =  mktime(0,0,0,$time[1],$time[0],$time[2]);
		if($data['stake_mode']!='permatch') {
			$data['match_nb'] = '';
			$data['stake_back'] = "no";
		}


		if($data['stake_mode']=='none'){
			$data['currency'] = $data['stake'] = $data['round'] = '';
		}

		$data['bet_until'] = $data['betuntil_nb'].':'.$data['betuntil_time'].':'.$data['betuntil_before'];

		//if(!isset($data['correctbox'])) $data['p_correct'] = "NULL";
		if(!isset($data['diffbox'])) $data['p_diff'] = "NULL";
		if(!isset($data['almostbox'])) $data['p_almost'] = "NULL";
		//if(!isset($data['wrongbox'])) $data['p_wrong'] = "NULL";

		if($data['jp_fraction_or_fix'] == 'fix'){
			$data['jp_fraction'] = '';
		}else{
			$data['jp_fix'] = '';
			$data['jp_fraction'] = $data['jp_fraction']/100;
		}

		if($data['jp_distr_algorithm']=='lin')
			$data['jp_distr_exp_value'] = $data['jp_distr_fix_shares'] = '';
		elseif($data['jp_distr_algorithm']=='exp')
			$data['jp_distr_fix_shares'] = '';
		else
			$data['jp_distr_exp_value'] = '';

		if($data['bet_on'] == 'results')
			$data['score_input_type'] = 'results';
	
		if($data['nextstep']==1)
			$data['active'] = 0;
		else
			$data['active'] = -1;



		//entry in the db (first the edit & then the new version)
		if($_POST['formname']=='phase2'){
			$query = "UPDATE ".PFIX."_events 
					SET name='".$data['name']."',
					deadline='".$data['deadline']."',
					currency='".$data['currency']."',
					stake_mode='".$data['stake_mode']."',
					match_nb='".$data['match_nb']."',
					stake='".$data['stake']."',
					stake_back='".$data['stake_back']."',
					round='".$data['round']."',
					bet_on='".$data['bet_on']."',
					score_input_type='".$data['score_input_type']."',
					bet_until='".$data['bet_until']."',
					p_correct=".$data['p_correct'].",
					p_diff=".$data['p_diff'].",
					p_almost=".$data['p_almost'].",
					p_wrong=".$data['p_wrong'].",
					jp_fraction_or_fix='".$data['jp_fraction_or_fix']."',
					jp_fraction='".$data['jp_fraction']."',
					jp_fix='".$data['jp_fix']."',
					jp_distr_algorithm='".$data['jp_distr_algorithm']."',
					jp_distr_exp_value='".$data['jp_distr_exp_value']."',
					jp_distr_fix_shares='".$data['jp_distr_fix_shares']."',
					active ='".$data['active']."',
					ko_matches='".$data['ko_matches']."',
					enable_tie='".$data['enable_tie']."',
					ap_score='".$data['ap_score']."' 
					WHERE id = '".$data['id']."'";


			if($data['nextstep']==1){
				//only activate if referer-site was the edit section 
				//(to prevent activation in case of a site call from the history data)
				if(stristr($_SERVER['HTTP_REFERER'], 'ssubmenu=settings&ev='.$data['id'])){

					if($data['score_input_type'] == 'results'){
					//prepare queries for updating the events tabel & creating a new table for the activated event
						$query2 = "CREATE TABLE ".PFIX."_event_".$data['id']." ("
							."id INT NOT NULL AUTO_INCREMENT,"
							."time DOUBLE NOT NULL,"
							."matchday TINYTEXT,"
							."matchday_id INT NOT NULL DEFAULT '999999',"
							."komatch INT(1) NOT NULL DEFAULT 0,"
							."home TINYTEXT,"
							."visitor TINYTEXT,"
							."score_h INT DEFAULT NULL,"
							."score_v INT DEFAULT NULL,"
							."score_special TINYTEXT DEFAULT NULL,"
							."jackpot FLOAT DEFAULT NULL,"
							."PRIMARY KEY (id)"
							.")";
					}else{
						$query2 = "CREATE TABLE ".PFIX."_event_".$data['id']." ("
							."id INT NOT NULL AUTO_INCREMENT,"
							."time DOUBLE NOT NULL,"
							."matchday TINYTEXT,"
							."matchday_id INT NOT NULL DEFAULT '999999',"
							."komatch INT(1) NOT NULL DEFAULT 0,"
							."home TINYTEXT,"
							."visitor TINYTEXT,"
							."score INT DEFAULT NULL,"
							."score_special TINYTEXT DEFAULT NULL,"
							."jackpot FLOAT DEFAULT NULL,"
							."PRIMARY KEY (id)"
							.")";
					}

					//do the queries
					if($db->query($query2)){
						echo '<br/>newtable: '.$lang['general_savedok'];
						if($data['stake_mode'] == 'permatch'){
							$data['eve']=$data['id'];
							$data['emptymatches']=$data['match_nb'];
							$_REQUEST['notincluded']=1;
							$_REQUEST['evac']='addemptymatches';
						}
					}else{
						echo '<p />query->'.$query2;
						echo '<br/>newtable: '.$lang['general_savednotok'];
					}
				}
			}

		//save phase3 event
		}elseif($_POST['formname']=='phase3'){
			$query = "UPDATE ".PFIX."_events 
					SET name='".$data['name']."',
					deadline='".$data['deadline']."',
					public='".$data['public']."'
					WHERE id = '".$data['id']."'";

		//new event save
		}elseif($data['formname']=='phase1'){
			$query = "INSERT INTO ".PFIX."_events 
					(name,
					deadline)
					VALUES
					('".$data['name']."',
					'".$data['deadline']."')";
		}

		//information for the user
		if($db->query($query)){
			if($data['formname']=='phase1'){
				$ids = $db->query("SELECT id FROM ".PFIX."_events 
						ORDER BY id;");
				$last = array_pop($ids);
				redirect($rlink.$_POST['form'].$last['id'], 3);
			}else{
				echo '<br/>eventsettings: '.$lang['general_savedok'].'<br>';
				echo $lang['general_redirect'];
				redirect($rlink.$_POST['form'], 3);
			}
		}else{
			echo '<br/>eventsettings: '.$lang['general_savednotok'];
			echo '<p>query->'.$query;
		}
	}

//============= activate an event
}elseif($_REQUEST['evac'] == 'activate'){

	$data = $_REQUEST;
	//only activate if referer-site was the edit section (to prevent activation in case of a site call from the history data)
	if(stristr($_SERVER['HTTP_REFERER'], 'ssubmenu=settings&ev='.$data['ev'])){
		$query = "UPDATE ".PFIX."_events 
				SET active = '1' 
				WHERE id = '".$data['ev']."';";
		if($db->query($query)){
			echo $lang['general_savedok'];
			redirect($_SERVER['HTTP_REFERER'],3,1);
		}else{
			echo $lang['general_savednotok'].'<br/>';
			echo $query;
			redirect($_SERVER['HTTP_REFERER'],5,1);
		}
	}else{
		redirect($_SERVER['HTTP_REFERER'],0,1);
	}


//========== save edited, active event
}elseif($_REQUEST['evac'] == 'saveactive'){
	global $events;
	$lang['general_updating'];
	$data = $_POST;

	//check if there were chagnes in the "personnel" of an event
	$nbusers = $db->row_count("SELECT id FROM ".PFIX."_users");
	for ($x = 1; $x<=$nbusers; $x++){
		if ($data['hiddenfield_'.$x] != 0 || isset($data['u_'.$x])){
			//something happened with the user $x
			if($data['hiddenfield_'.$x] == 1 || isset($data['u_'.$x])){
				//approve
				if($events['u']['e'.$data['id']]['bet_on']=='results'){
					$eventquery = "ALTER TABLE ".PFIX."_event_".$data['id']."
							ADD ".$x."_h INT DEFAULT NULL,
							ADD ".$x."_v INT DEFAULT NULL,
							ADD ".$x."_points INT DEFAULT NULL,
							ADD ".$x."_money FLOAT DEFAULT NULL,
							ADD ".$x."_ranking INT DEFAULT NULL;";
				}else{
					$eventquery = "ALTER TABLE ".PFIX."_event_".$data['id']."
							ADD ".$x."_toto INT(1) DEFAULT NULL,
							ADD ".$x."_points INT DEFAULT NULL,
							ADD ".$x."_money FLOAT DEFAULT NULL,
							ADD ".$x."_ranking INT DEFAULT NULL;";

				}
				if( $db->query($eventquery)
					&& $db->query(phpManageUser($x, 'a', $data['id']))){
					echo $lang['general_saveok'].'<br />';
					echo $lang['general_redirect'];
					//send the user a mail to notify him of his approval
					if ($settings['functionalSMTP'] == 'true'){
						$uinfo = loadSettings($x);
						$ulang = languageSelector($uinfo['lang']);
						$from = $settings['email'];
						$to = $uinfo['email'];
						$subarray1 = array($events['u']['e'.$data['id']]['name']);
						$subarray2 = array($uinfo['name'], $events['u']['e'.$data['id']]['name']);
						$subject = substitute($ulang['admin_events_approved_subject'], $subarray1);
						$text = substitute($ulang['admin_events_approved_message'], $subarray2);
						$mail = initMail();
						$mail->AddReplyTo($ainfo['email'], $ainfo['name']." ".$ainfo['famname']);
						$mail->From = $my_smtp['from']; 
						$mail->FromName =  $ulang['general_bettingOffice']." ".$settings['name'];
						$mail->Subject = $subject;
						$mail->Body = $text;
						$mail->AddAddress($to, $uinfo['name']." ".$uinfo['famname']);
						$mail->Send();	
					}
		
				}else{
					echo $lang['general_savednotok'].'<br />';
					echo $eventquery;
				}
			}else{
				//deny
				$db->query(phpManageUser($x, 'd', $data['id']));
			}
		}
	}
	//the rest of the settings don't need checks and are updated here (only condition: settings-formular!"
	if(!isset($data['adduserform'])){	
		if($db->query("UPDATE ".PFIX."_events SET name = '".$data['name']."', public = '".$data['public']."' WHERE id ='".$data['id']."'")){
			echo $lang['general_savedok'].'<br>';
		}else{
			echo $lang['general_savednotok'].'<br>';
		}
	}
	echo $lang['general_redirect']; 
	redirect($rlink.'ssubmenu=settings&ev='.$data['id'], 3);

//========== save matches
}elseif($_REQUEST['evac'] == 'savematches'){
	echo $lang['general_updating'].'<br />';
	
	$item=0;
	
	/*this is quite complicated..so i'm going to explain it :)
	we have to go through every $_post - the $item variable is the counter for the added new matches*/
	
	foreach($_POST as $key => $in){

		/*for each time1,we go get the value of time2 and then we do a lot of 
			splitting to get the different time elements & the id of the 
			match to which the time-element in question belongs to.*/
			
		if (ereg('time1', $key) && ($item < $_POST['adds'] || $_POST['adds'] == 0)){
			$w = (ereg('new', $key)) ? 'new' : ''; //is this just an edited or a new match? => $w
			if ($w == 'new' && $_POST['adds'] == 0) continue; 	//there are no new matches, skip this iteration
			if ($w == 'new') ++$item; 	//count the new matches
			$x = explode('_', $key);	//to get the id of the match
			$y = explode('.', $_POST[$w.'time1_'.$x[1]]);	//to get the date (d.m.Y)
			$z = explode(':', $_POST[$w.'time2_'.$x[1]]);	//to get the time (H:i)
			$unixtime[$w.$x[1]] = @mktime($z[0], $z[1], 0, $y[1], $y[0], $y[2]);
			
			/*check if 1. the date is correct, 
			2. the time corresponds with the format HH:ii.
			3. the time is not in the past or let it pass if it was a readonly match (nothing edited)*/

			if(	checkdate((int)$y[1], (int)$y[0], (int)$y[2])		//1.
				&& ereg('([0-9][0-9]):([0-9][0-9])', $_POST[$w.'time2_'.$x[1]]) //2.
				&& (	time() < $unixtime[$w.$x[1]] 			//3.
					|| $_POST['ro_'.$x[1]] == 'readonly') ){	//3.

				/*the $chosen array is for the edited matches that aren't new, 
				this separation is due to the different queries the two types of matches need*/
				if($w != 'new') $chosen[] = $x[1];
				
			}else{	
				/*well, the match didn't fit the requirements,
				so we don't need it's time anymore and point it out
				by inserting it into an err[] array*/

				unset($unixtime[$w.$x[1]]);
				$err[] = $w.$x[1];
			} 		
		}
	}
	if (isset($err)){
		// don't change a thing in the database because there's an error
		$_SESSION['err'] = $err;
		$_SESSION['post'] = $_POST;
		//clean the link
		redirect( ereg_replace('(evac=savematches&which=[0-9]+)&', '',$rlink.$link_query.'ssubmenu=matches&'.$_POST['query']), 0);
	}else{
		/*update the rest of the matches*/
		//print_r($chosen);
		if(!isset($chosen)) $chosen = array(); //empty array to prevent error
		foreach($chosen as $x){

			//matchday_id-management
			unset($matchday_id);
			$mdraw = $db->query("SELECT DISTINCT matchday, matchday_id 
					FROM ".PFIX."_event_".$_POST['event']." 
					WHERE matchday!='--';");
			foreach($mdraw as $row) $md[$row['matchday']] = $row['matchday_id'];
			if($_POST['matchday_'.$x]!="--"){
				$matchday_id = (isset($md[$_POST['matchday_'.$x]])) ? 
					$md[$_POST['matchday_'.$x]] : (@max($md)+1);
			}else{
				$matchday_id = '999999';
			}


			$query_changes = "UPDATE ".PFIX."_event_".$_POST['event']."
						SET time = '".$unixtime[$x]."',
						matchday = '".$_POST['matchday_'.$x]."',
						matchday_id = '".$matchday_id."',
						home = '".$_POST['home_'.$x]."',
						visitor = '".$_POST['visitor_'.$x]."',
						komatch = '".$_POST['komatch_'.$x]."'
						WHERE id = '".$x."';";
			$db->query($query_changes);
		}
//		print_r($_POST);
		/*insert new matches we put in the $chosen array before*/
		for($x=1; $x <= $_POST['adds']; $x++){

			//matchday_id-management
			unset($matchday_id);
			$mdraw = $db->query("SELECT DISTINCT matchday, matchday_id 
					FROM ".PFIX."_event_".$_POST['event']." 
					WHERE matchday!='--';");
			foreach($mdraw as $row) $md[$row['matchday']] = $row['matchday_id'];
			if($_POST['newmatchday_'.$x]!="--"){
				$matchday_id = (isset($md[$_POST['newmatchday_'.$x]])) ? 
					$md[$_POST['newmatchday_'.$x]] : (@max($md)+1);
			}else{
				$matchday_id = '999999';
			}

			$query_new = "INSERT INTO ".PFIX."_event_".$_POST['event']."
					(time, matchday, matchday_id, home, visitor)
					VALUES ('".$unixtime['new'.$x]."',
						'".$_POST['newmatchday_'.$x]."',
						'".$matchday_id."',
						'".$_POST['newhome_'.$x]."',
						'".$_POST['newvisitor_'.$x]."');";
			$db->query($query_new);
		}
		
	$echo['general_redirect'];
	redirect($_SERVER["HTTP_REFERER"], 3, 1);
}

//========== save results

}elseif($_REQUEST['evac'] == 'saveresults'){
	global $events;
	echo $lang['general_updating'].'<br>';
	$evdat = $events['u']['e'.$_POST['event']];
	//check the edited results on errors
	$ok = Array();	
	$err = Array();	
	$idar = explode(':', $_POST['ids']);
	foreach($idar as $id){
		if ($_POST['ro_'.$id] == "false"){
			if($evdat['score_input_type']=='results'){
				//check if the etnries were corect => $ok, else => $err
				if (is_numeric($_POST['score_h_'.$id]) && is_numeric($_POST['score_v_'.$id])
					&& $_POST['score_h_'.$id]!='' && $_POST['score_v_'.$id]!=''){
					$ok[] = $id;
				}else{
					$err[] = $id;
				}
				//estimate a winner and loser for possible replacing in the tournament (if cup system)
				if ($_POST['score_h_'.$id] > $_POST['score_v_'.$id]) $winner[$id] = 'h';
				if ($_POST['score_h_'.$id] < $_POST['score_v_'.$id]) $winner[$id] = 'v';

			}elseif($evdat['score_input_type']=='toto'){
				if ($_POST['toto_'.$id] == "")
					$err[] = $id;
				else
					$ok[] = $id;
				//estimate a winner and loser for possible replacing in the tournament (if cup system)
				if ($_POST['toto_'.$id] == 1) $winner[$id] = 'h';
				if ($_POST['toto_'.$id] == 2) $winner[$id] = 'v';
			}


		}
	}
	if (isset($err) && sizeof($err)>0){
	//go back on error
		$_SESSION['err'] = $err;
		$_SESSION['post'] = $_POST;
		redirect( ereg_replace('(evac=saveresults&which=[0-9]+)&', '',$rlink.$link_query.'ssubmenu=results&'.$_POST['query']), 0);
	}else{
		
		//if a result is updated, that was before already other updated matches, the
		//ranks have to be recalculated for all follwing
		//=> this first section estimates if a match was not last

		//number of updates
		$updatesNb = sizeof($ok);
		//all data
		$data = $db->query("SELECT * FROM ".PFIX."_event_".$_POST['event']." ORDER BY time ASC;");
		//only data with results
		if($evdat['score_input_type']=='results'){
			$data2 = $db->query("SELECT * FROM ".PFIX."_event_".$_POST['event']." 
				WHERE score_h  IS NOT NULL ORDER BY time ASC;");
		}else{
			$data2 = $db->query("SELECT * FROM ".PFIX."_event_".$_POST['event']." 
				WHERE score  IS NOT NULL ORDER BY time ASC;");
		}

		$alreadyupdated = array();
		foreach ($data2 as $row){
			$alreadyupdated[$row['id']] = $row['id'];
		}

		//gradually delete from $alreadyupdated until empty (the id's found in $ok before
		//$alreadyupdated is empty, are matches inbetween already updated ones
		foreach ($data as $row){
			if(in_array($row['id'], $alreadyupdated)) array_shift($alreadyupdated);
			if(sizeof($alreadyupdated) == 0) break;
			if(in_array($row['id'], $ok)) $notlast[] = $row['id'];
		}
		$up = 0; //for counting the updates invoked from the user



		//write results for changed ones
		foreach($ok as $x){
			$up++; //count updates invoked by user
			$gonew=1; //mark that this one's a new entry

			while($gonew || $updatefollowings){

				$gonew=0; //unmark (for next loop)

				/*calculate points and money distribution for each match and update*/
			
				if($evdat['score_input_type']=='results'){
					$a= $_POST['score_h_'.$x];
					$b= $_POST['score_v_'.$x];
					$set = " SET score_h = '".$a."',
						score_v = '".$b."',";
				}else{
					$a = $_POST['toto_'.$x];
					$b = 0;
					$set = " SET score = '".$a."', "; 
				}
				$pam = calculatePointsAndMoney($_POST['event'], $x, $a, $b);
				$rank = calculateRanking($_POST['event'], $x, $pam);
				//query preparation
				//=>1st part
				$query_changes = "UPDATE ".PFIX."_event_".$_POST['event']
							.$set.
							"score_special = '".$_POST['special_'.$x]."'";
				//=> jackpot				
				$query_changes .= ", jackpot = '".$pam['jackpot']."'";
				
				//=>points
				foreach($pam['points'] as $user => $points) 
					$query_changes .= ", ".$user."_points = '".$points."'";

				//=>money
				foreach($pam['money'] as $user => $money) 
					$query_changes .= ", ".$user."_money = '".$money."' ";
				
				//=>ranks				
				foreach($rank as $user => $r)
					$query_changes .= ", ".$user."_ranking = '".$r."' ";

				//=>which row(match-id dependent)
				$query_changes .= "WHERE id = '".$x."';";
				$query_changes.'<br /><br />';

				//finally update
				$db->query($query_changes);


				//if all by user invoked updates have been made, recalulcate follwings (if any)
				if($up == $updatesNb && isset($notlast)){
					$updatefollowings = $firstfollowing = 1;
					$from = array_shift($notlast);
					unset($notlast); //prevent entering this if again
					if($evdat['score_input_type']=='results'){
						$data2 = $db->query("SELECT id, score_h, score_v, score_special 
								FROM ".PFIX."_event_".$_POST['event']." 
								WHERE score_h  IS NOT NULL ORDER BY time ASC;");
					}else{
						$data2 = $db->query("SELECT id, score, score_special 
								FROM ".PFIX."_event_".$_POST['event']." 
								WHERE score  IS NOT NULL ORDER BY time ASC;");
					}
					$sizedata2 = sizeof($data2);
				}
				if($updatefollowings){
					if ($firstfollowing){
						$counter = 0;
						foreach($data2 as $row){
							if($row['id'] == $from){
								$firstfollowing = 0;
								$firstcounter = $counter+1;
							}
							$counter++;
						}
					}else{
						$firstcounter++;
						if ($firstcounter == $sizedata2) $updatefollowings = 0; //if all have been updated
					}
					$x = $data2[$firstcounter]['id'];
					//cheat data into $_POST
					$_POST['score_h_'.$x] = $data2[$firstcounter]['score_h'];
					$_POST['score_v_'.$x] = $data2[$firstcounter]['score_v'];
					$_POST['special_'.$x] = $data2[$firstcounter]['score_special'];
				}
			} //end of while-loop

			if (isset($winner)){

				//get names for winner and losers
				foreach ($data as $row) {
					if (isset($winner[$row['id']])) {
						$winner[$row['id']] = ($winner[$row['id']] == 'h') ? $row['home'] : $row['visitor'];
						$loser[$row['id']] = ($winner[$row['id']] == 'v') ? $row['home'] : $row['visitor'];
					}
				}
				foreach ($winner as $id => $name){		
					foreach($data as $row){
						if ($row['home'] == '$W_'.$id){
								$db->query("UPDATE ".PFIX."_event_".$_POST['event']." SET home = '".$winner[$id]."' WHERE home = '\$W_".$id."'");
						}
						if ($row['visitor'] == '$W_'.$id){
								$db->query("UPDATE ".PFIX."_event_".$_POST['event']." SET visitor = '".$winner[$id]."' WHERE visitor = '\$W_".$id."'");
						}
						if ($row['home'] == '$L_'.$id){
								$db->query("UPDATE ".PFIX."_event_".$_POST['event']." SET home = '".$loser[$id]."' WHERE home = '\$L_".$id."'");
						}
						if ($row['visitor'] == '$L_'.$id){
								$db->query("UPDATE ".PFIX."_event_".$_POST['event']." SET visitor = '".$loser[$id]."' WHERE visitor = '\$L_".$id."'");
						}
					}
					
				}
			}


		}//end of else (no-err)
		echo $lang['general_redirect'];
//		redirect( ereg_replace('(evac=saveresults&which=[0-9]+)&', '',$rlink.$link_query.'ssubmenu=results&'.$_POST['query']), 3);
	}
}

function calculatePointsAndMoney ($event, $match, $score_a, $score_b){
	global $events, $db, $my_smtp;
	
	//prepare data for a lot of calculating
	
	  //=> which and how many users for this event?
	$evUsers = explode(':', $events['u']['e'.$event]['a']);
	array_pop ($evUsers);
	$nb = sizeof($evUsers);

	 //=>all data for this event
	$evData = $events['u']['e'.$event];
	 //=>define which success yields how many points.. (to be adjustable in a later version)
	$correct = $evData['p_correct'];
	$diff = $evData['p_diff'];
	$almost = $evData['p_almost'];
	$wrong = $evData['p_wrong'];
	  
	  //=>all data for this match
	$data = $db->query("SELECT * FROM ".PFIX."_event_".$event." WHERE id='".$match."';");
	foreach ($data[0] as $label => $info){
		$maData[$label] = $info;
	}	
	unset ($data);
	
	
	
	//how many tipped CORRECT (1), DIFF/ALMOST (0) correct, WRONG (-1)
	  	//+ creating array with success value ($good, indicated in brackets) for each user
		//+ creating array with pionts for each user
	$nbCorrect = $nbDiff = $nbAlmost = $nbWrong = 0;
	foreach($evUsers as $p){
		if($evData['bet_on']=='results' && $evData['score_input_type']=='results'){
			$a = $score_a;
			$b = $score_b;
			$c = ($maData[$p.'_h'] == '') ? '' : $maData[$p.'_h'];
			$d = ($maData[$p.'_v'] == '') ? '' : $maData[$p.'_v'];
		}elseif($evData['bet_on']=='toto' && $evData['score_input_type']=='results'){
			$a = $score_a;
			$b = $score_b;
			$c = $maData[$p.'_toto'];
			$d = 'toto';
		}else{
			$a = $score_a;
			$b = $maData[$p.'_toto'];
			$c = 'toto';
			$d = 'toto';
		}
		//correct:
		if(isCorrect($correct, $a, $b, $c,$d)){
			$nbCorrect++;
			$success[$p] = 1;
			$points[$p] = $correct;
		//diff:
		}elseif(isDiff($diff,$a, $b, $c,$d)){
			$nbAlmost++;
			$success[$p] = 0;
			$points[$p] = $diff;
		//alsmost:
		}elseif(isAlmost($almost,$a, $b, $c,$d)){
			$nbAlmost++;
			$success[$p] = 0;
			$points[$p] = $almost;
		//wrong:
		}elseif(isWrong($wrong,$a, $b, $c,$d)){
			$nbWrong++;
			$success[$p] = -1;
			$points[$p] = $wrong;
		}
	}

/*
//======DEBUG
$nb=80;
$nbCorrect = 3;
$evData['round'] = 0.05;
*/
	if($evData['stake_mode']=='permatch'){
		//money business
		//=>how much gets everybody and is going into the jackpot?
		$factor = (1/$evData['round']);
		$totalstake = $nb*$evData['stake'];
		$money = array();
		if($nbCorrect>0){
			$exact = floor(($factor*$totalstake)/$nbCorrect)/$factor;	
			$floored = $totalstake-($exact*$nbCorrect);
			foreach($evUsers as $p)
				$money[$p] = ($success[$p] == 1) ? $exact : '0';
		}elseif($evData['stake_back']=='yes'){
			foreach($evUsers as $p)
				$money[$p] = ($success[$p] == 0) ? $evData['stake'] : '0';		
		}else{
			foreach($evUsers as $p)
				$money[$p] = '0';
		}
		$jackpot = $totalstake-array_sum($money);
	}else{
		foreach($evUsers as $p)
			$money[$p] = '0';
		$jackpot = 0;
	}
	//return all the important info collected & calculated in an array!
	
	return array( 	'money' => $money, 'points' => $points, 'jackpot' => $jackpot);
}


function calculateRanking($event, $match, $pam){
	global $events, $db;	
	
	//get the data for the event
	$data = $db->query("SELECT * FROM ".PFIX."_event_".$event." ORDER BY time ASC;");

	//choose the points made in this very match
	$points = $pam['points'];

	//add all achieved points...
	foreach($data as $row){
		//...until this match is reached
		if($row['id'] == $match) break;
		foreach($row as $label => $info){
			if(substr($label, -7) == '_points') $points[substr($label, 0, -7)] += $info;
		}
	}
	//sort array descending by points

	arsort($points);
	$counter = 1;
	$ranker = 1;
	foreach ($points as $id => $pt){
		$points_p[] = $pt;
		$points_u[] = $id;
	}
	foreach ($points_p as $index => $pt){
		$rank[$points_u[$index]] = $ranker;
		$counter++;
//echo $points_p[$counter-1]. ' : '.$points_p[$counter-2].'<br/>';
		if ($points_p[$counter-1] != $points_p[$counter-2]) $ranker = $counter;
	}
//print_r($rank);
	return $rank;
}	

//========== changecompetitor

if($_REQUEST['evac'] == 'changecompetitor'){
$data = $_POST;

//home
$sql1 = "UPDATE ".PFIX."_event_".$data['eve']." 
		SET home = '".$data['changeto']."' 
		WHERE home = '".$data['tochange']."'; ";
///visitor
$sql2 = "UPDATE ".PFIX."_event_".$data['eve']." 
		SET visitor = '".$data['changeto']."' 
		WHERE visitor = '".$data['tochange']."'; ";
$query1 = $db->query($sql1);
$query2 = $db->query($sql2);

//We suppose that if one's has been written successfully, the other one is also written
if ($query1 || $query2){
		echo $lang['general_savedok'].'<br />';
		echo $lang['general_redirect'];
		redirect( $rlink.'ssubmenu=matches&ev='.$data['eve'], 3);
}else{

		echo $lang['general_savednotok'].'<br />';
		echo $lang['general_redirect'];
		redirect( $rlink.'ssubmenu=matches&ev='.$data['eve'], 3);
}

//========== installevent
}elseif($_REQUEST['evac'] == 'installevent'){
	global $events;

	$data = $_POST;
	$srcfile = $_FILES['eventup']['tmp_name'];
	$handle = @fopen($srcfile, 'r');
	while (!feof($handle)){
		$read = fgets($handle, 4096);
		if (substr($read, 0,1) == '('){
			$contents.=$read;
			$lineNb++;
		}
	}
	fclose($handle);

	$rawdata = $db->query("SELECT id FROM ".PFIX."_event_".$data['eve'].";");
	$matches = sizeof($rawdata);

	if ($events['i']['e'.$data['eve']]['stake_mode']=='permatch' &&
		$events['i']['e'.$data['eve']]['match_nb'] == $lineNb ||
		$events['i']['e'.$data['eve']]['stake_mode']!='permatch')
	{
		if ($matches>0){
			//delete all entries
			$db->query("DELETE FROM ".PFIX."_event_".$data['eve']." WHERE id>0 ;");
			$db->query(" ALTER TABLE ".PFIX."_event_".$data['eve']." AUTO_INCREMENT =1;");
		}

	}else{
		$err[1] = 'incorrectmatchnb';
	}

	$sql  = "INSERT INTO ".PFIX."_event_".$data['eve']." (`id`, `time`, `matchday`, `home`, `visitor`, `score_h`, `score_v`, `score_special`, `jackpot`) VALUES ".$contents; 

	if(!isset($err)){
		if($db->query($sql)){
			echo $lang['general_savedok'].'<br>';
			echo $lang['general_redirect'];
			redirect($rlink.'ssubmenu=matches&ev='.$data['eve'], 3);

		}else{
			echo $lang['general_savednotok'];
		}
	}else{
		$_SESSION['err'] = $err;
		redirect($rlink.'ssubmenu=matches&ev='.$data['eve'], 0);
	}
//========== addemptymatches 
}elseif($_REQUEST['evac'] == 'addemptymatches'){
	
	if($_REQUEST['notincluded']!=1) 
		$data = $_POST;
	$time = mktime(12, 0, 0, 1, 18, date('Y', time()) + 10);
	$matchday = '--';
	$komatch = ($data['ko_matches'] == 'only') ? 1 : 0;
	$query = "INSERT INTO ".PFIX."_event_".$data['eve']." (`time`, `matchday`, `home`, `visitor`, `komatch`) VALUES (".$time.", '".$matchday."', 'home', 'visitor', ".$komatch.");";
	if(is_numeric($data['emptymatches']))
		while($counter++<$data['emptymatches'])
			$db->query($query);
		
	if($_REQUEST['notincluded']!=1){ 
		echo $lang['general_saving'].'<br/>';
		echo $lang['general_redirect'];
		redirect($_SERVER["HTTP_REFERER"], 3, 1);
	}

//========== arrangematchdays
}elseif($_REQUEST['evac'] == 'arrangematchdays'){
	echo $lang['general_updating'];
	$data=$_POST;
	for($i=1;$i<=$data['md_nb'];$i++){
		$query = "UPDATE ".PFIX."_event_".$data['eve']." 
				SET matchday_id = '".$data['mdid_'.$i]."' 
				WHERE matchday = '".$data['md_'.$i]."';";
		$db->query($query);
	}
	redirect($_SERVER["HTTP_REFERER"], 3, 1);

}
