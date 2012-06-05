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
global $db, $events;

//get the settings of the user
$mysettings = loadSettings($_SESSION['userid']);


//=====> updating
if(isset($_REQUEST['ac']) && isset($_POST)){

	$data = $_POST;



	if($_REQUEST['sec'] == 'profilesettings'){ 
		//update the language & set the language (in session) to the chosen one
		$db->query("UPDATE ".PFIX."_users SET lang = '".$data['lsel']."' WHERE id='".$_SESSION['userid']."'");
		$_SESSION['dlang'] = $_SESSION['setlangcookie'] = $data['lsel'];

		//update email in case its seems to be a valid one and was entered two times
		if($data['email1'] == $data['email2'] && stristr($data['email1'], '@') != FALSE){
			$db->query("UPDATE ".PFIX."_users SET email = '".$data['email2']."' WHERE id='".$_SESSION['userid']."'");
		}

		//update # comments in loginhome
		if ($data['home_comments'] != $mysettings['home_comments'])
			$db->query("UPDATE ".PFIX."_users SET home_comments = '".$data['home_comments']."' WHERE id='".$_SESSION['userid']."'");

		//udpate style
		if ($data['style'] != $mysettings['style']){
			$db->query("UPDATE ".PFIX."_users SET style = '".$data['style']."' WHERE id='".$_SESSION['userid']."'");
			$_SESSION['setstylecookie'] = $data['style'];
		}
	}

	if($_REQUEST['sec'] == 'persdetails'){ 
		//update name and  family name (and make sure they have to)
		if($data['name'] != "" && $data['famname'] != ""){
			$db->query("UPDATE ".PFIX."_users SET name = '".$data['name']."', famname = '".$data['famname']."' WHERE id='".$_SESSION['userid']."'");
		}else{
			redirect($rlink.'error=names');
			exit;
		}
		
		//update bank account details
		$db->query("UPDATE ".PFIX."_users SET account_type = '".$data['account_type']."', 
				account_holder = '".$data['account_holder']."', 
				account_details = '".$data['account_details']."' 
				WHERE id='".$_SESSION['userid']."';");
	}

	/*update the events of user (dat from the hiddenfields, 
		which were set by the javascript function*/
	for ($x = 0; $x < $events['u']['nb'] ; $x++){
		if ($data['hiddenfield_'.$events['u'][$x]] != 0){
			if($data['hiddenfield_'.$events['u'][$x]] == 1){
				//go wait 
				//echo 'trying to make: '.phpManageUser($_SESSION['userid'], 'w', $events['u'][$x]);
                $query=phpManageUser($_SESSION['userid'], 'w', $events['u'][$x]);
				$db->query($query);
				$txt = substitute($lang['myprofile_settings_notifyparticipatetxt'],
						Array($mysettings['login'],
						$events['u']['e'.$events['u'][$x]]['name']));
				notify($lang['myprofile_settings_notifyparticipate'],$txt);
			}else{
				//retire
				//echo 'trying to make: '.phpManageUser($_SESSION['userid'], 'w', $events['u'][$x]);
				$db->query(phpManageUser($_SESSION['userid'], 'r', $events['u'][$x]));
				$txt = substitute($lang['myprofile_settings_notifywithdrawtxt'],
						Array($mysettings['login'],
						$events['u']['e'.$events['u'][$x]]['name']));
				notify($lang['myprofile_settings_notifywithdraw'],$txt);
			}
		}
	}
	
	//go back
	redirect($rlink);

}else{

	//make the select input & get the events of user
	$select = makeLangSelect($mysettings['lang']);
	$uevents = loadUserEvents();

	//preparing hidden fields for form
	for($x = 0; $x < $events['u']['nb']; $x++){
		$hiddenfields .= '<input type="hidden" name="hiddenfield_'.$events['u'][$x].'" id="hf_'.$events['u'][$x].'" value="0">';
	}
	//preparing events for form
	//=> approved
	if(isset($uevents['approved'])){
		$eventsform .= '<div class="title">'.$lang['myprofile_settings_approved'].'</div>';
		$evs = explode(':', $uevents['approved']);
		array_pop($evs);
		foreach ($evs as $id){
			$eventsform .= '<div class="input">'.$events['u']['e'.$id]['name']
				.'<a href="javascript: showFloatingLayer(\''.$id.'_stake\')" title="'.$lang['general_show_info'].'"> (i) </a> '
				.' ( <a href="javascript: showFloatingLayer(\''.$id.'\')">'.$lang['myprofile_settings_staketopay'].'</a> )</div>';
			//make info floating layer for this event
			$flcnt = generateEventInfo($id);
			foreach($flcnt as $sid => $cnt)
				echo makeFloatingLayer($events['u']['e'.$id]['name'], $cnt, 1, $id.'_'.$sid);
			//make bank account info floating layer for this event
			   $matchnb =  $db->query("SELECT COUNT(id) AS nb FROM zock_event_".$id.";");
			   $subarray1 = array ( $matchnb[0]['nb'],
					       $events['u']['e'.$id]['stake'].' '.$events['u']['e'.$id]['currency'],
					      $matchnb[0]['nb']*$events['u']['e'.$id]['stake'].' '.$events['u']['e'.$id]['currency']);
			   $subarray2 = array ($events['u']['e'.$id]['name'], $mysettings['login']);
			   $subarray3 = array ($settings['email']);
			   
			   switch($evs[$id]['stake_mode']){
				case 'none':
				     $flcnt = substitute($lang['myprofile_settings_eventinfo'], $subarray1);
				     break;
				case 'fix':
				     $flcnt = substitute($lang['myprofile_settings_eventinfofix'], $subarray1);
				     break;
				case 'permatch':
				     $flcnt = substitute($lang['myprofile_settings_eventinfo'], $subarray1);
				     break;
				     
			   }
			      $flcnt .= '<p/>';
			      $flcnt .= '<b>'.$lang['general_bank_account'].'</b><br/><br/>';
			      $flcnt .= $settings['account_type'].': '.$settings['account_details'];
			      $flcnt .= '.<br/>'.nl2br($settings['account_holder']);
					$flcnt .= '<br/><br/>'.substitute($lang['myprofile_settings_wiringcomment'], $subarray2);
					$flcnt .= '<br/><br/>'.substitute($lang['myprofile_settings_email_notify'], $subarray3);
			   echo makeFloatingLayer($events['u']['e'.$id]['name'], $flcnt, 1, $id); 	
		}
	}
	//=>open 
	if(isset($uevents['open'])){
		$eventsform .= '<div class="title">'.$lang['myprofile_settings_open'].'</div>';
		$evs = explode(':', $uevents['open']);
		array_pop($evs);
		foreach ($evs as $id){
			$eventsform .= '<div class="input"><font id="ue_'.$id.'">'.$events['u']['e'.$id]['name'].'</font>'
				.'<a href="javascript: manageEvent(\'p\', \''.$id.'\', \''.$lang['myprofile_settings_participatereally'].'\')"> '.$lang['myprofile_settings_participate'].'</a> / '
				.'<a href="javascript: showFloatingLayer(\''.$id.'_stake\')" title="'.$lang['general_show_info'].'"> (i) </a> '
				.'('.$lang['admin_events_deadline'].' '.date('d.m.Y', $events['u']['e'.$id]['deadline']).')</div>';
			//make info floating layer for this event
			$flcnt = generateEventInfo($id);
			foreach($flcnt as $sid => $cnt)
				echo makeFloatingLayer($events['u']['e'.$id]['name'], $cnt, 1, $id.'_'.$sid);
		}
	}
	//waiting
	if(isset($uevents['waiting'])){
		$eventsform .= '<div class="title">'.$lang['myprofile_settings_waiting'].'</div>';
		$evs = explode(':', $uevents['waiting']);
		array_pop($evs);
		foreach ($evs as $id){
			$eventsform .= '<div class="input"><font id="ue_'.$id.'">'.$events['u']['e'.$id]['name'].'</font>'
				.'<a href="javascript: manageEvent(\'r\', \''.$id.'\', \''.$lang['myprofile_settings_withdrawreally'].'\')"> '.$lang['myprofile_settings_retire'].'</a> / '
				.'<a href="javascript: showFloatingLayer(\''.$id.'_stake\')" title="'.$lang['general_show_info'].'"> (i) </a> '
				.' ( <a href="javascript: showFloatingLayer(\''.$id.'\')">'.$lang['myprofile_settings_staketopay'].'</a> )</div>';
			//make info floating layer for this event
			$flcnt = generateEventInfo($id);
			foreach($flcnt as $sid => $cnt)
				echo makeFloatingLayer($events['u']['e'.$id]['name'], $cnt, 1, $id.'_'.$sid);

			//make bank account info floating layer for this event
			   $matchnb =  $db->query("SELECT COUNT(id) AS nb FROM zock_event_".$id.";");
			   $subarray1 = array ( $matchnb[0]['nb'],
					       $events['u']['e'.$id]['stake'].' '.$events['u']['e'.$id]['currency'],
					      $matchnb[0]['nb']*$events['u']['e'.$id]['stake'].' '.$events['u']['e'.$id]['currency']);
					$subarray2 = array ($events['u']['e'.$id]['name'], $mysettings['login']);
					$subarray3 = array ($settings['email']);
			   $flcnt = substitute($lang['myprofile_settings_eventinfo'], $subarray1);
			   $flcnt .= '<p/>';
			   $flcnt .= '<b>'.$lang['general_bank_account'].'</b><br/><br/>';
			   $flcnt .= $settings['account_type'].': '.$settings['account_details'];
			   $flcnt .= '.<br/>'.nl2br($settings['account_holder']);
					$flcnt .= '<br/><br/>'.substitute($lang['myprofile_settings_wiringcomment'], $subarray2);
					$flcnt .= '<br/><br/>'.substitute($lang['myprofile_settings_email_notify'], $subarray3);
			   echo makeFloatingLayer($events['u']['e'.$id]['name'], $flcnt, 1, $id); 	
		}
	}
	//=>denied
	if(isset($uevents['denied'])){
		$eventsform .= '<div class="title">'.$lang['myprofile_settings_denied'].'</div>';
		$evs = explode(':', $uevents['denied']);
		array_pop($evs);
		foreach ($evs as $id){
			$eventsform .= '<div class="input">'.$events['u']['e'.$id]['name'].'</div>';
		}
	}

	//error handling
	if (isset($_REQUEST['error'])) echo errorMsg($_REQUEST['error']).'<p>';

	echo $lang['myprofile_settings_content'];

	//the form
	echo '<div class="showformleft">
		<div class="sectionleft"> '.strtoupper($lang['admin_events_title']).'
			<form name="bettinggames" action="'.$link.'ac=save&sec=bettinggames" method="POST">
			'.$hiddenfields.'
			'.$eventsform.'
			</form>
		</div>
		 <div class="sectionleft"> '.strtoupper($lang['myprofile_settings_profilesettings']).'
			<form name="profilesettings" action="'.$link.'ac=save&sec=profilesettings" method="POST">

			<div class="title"># '.$lang['comments_title'].'</div>
			<div class="explanation">'.substitute($lang['myprofile_settings_commentnumber'], $lang['home_title']).'</div>
			<div class="input"><input name="home_comments" size="10" value="'.$mysettings['home_comments'].'"/></div>';

			//Style
			$styles = preg_split('/:/', $settings['style_forusers']);
			array_pop($styles);
			if(sizeof($styles)>1){
				echo '<div class="title">'.$lang['admin_settings_style'].'</div>';
				echo '<div class="input">';
					echo '<select name="style" >';
						$x=0;
						foreach ($styles as $s){
							$x++;
							if($x==1) $standard = ' (standard)';
							else $standard = '';
							if($s == $settings['style']) $sel='selected="sielected"';
							else $sel="";
							$i = getStyleInfo($s);
							echo '<option '.$sel.' value="'.$s.'">'.$i['name'].$standard.'</option>';
						}
					echo '</select>';
				echo '</div>';
			}

			echo '<div class="title">'.$lang['general_language'].'</div>
			<div class="explanation">'.$lang['myprofile_settings_preferredlanguage'].'</div>
			<div class="input">'.$select.'</div>

			<div class="title"><td class="title">'.$lang['register_email'].':</div>
			<div class="explanation">'.$lang['myprofile_settings_changeemail'].'</div>
			<div class="input"><input name="email1" size="30" value="'.$mysettings['email'].'"></div>
			<div class="input"><input name="email2" size="30" value=""></div>

			<div class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"></div>

			</form>
		</div>

	</div>
	<div class="showformright">

		<div class="sectionright"> '.strtoupper($lang['myprofile_settings_persdetails']).'
			<form name="persdetails" action="'.$link.'ac=save&sec=persdetails" method="POST">

			<div class="title">'.$lang['general_name'].' '.$lang['general_and'].' '.$lang['general_famname'].'</div>
			<div class="input"><input name="name" size="30" value="'.$mysettings['name'].'"></div>
			<div class="input"><input name="famname" size="30" value="'.$mysettings['famname'].'"></div>

			<div class="title">'.$lang['general_bank_account'].'</div>
			<div class="explanation">'.$lang['myprofile_settings_bank_account_intro'].'<br/>'.$lang['admin_settings_bank_account_text1'].'</div>
			<div class="input"><input name="account_type" size="30" value="'.$mysettings['account_type'].'"/></div>
			<div class="explanation">'.$lang['admin_settings_bank_account_text2'].'</div>
			<div class="input"><input name="account_details" size="30" value="'.$mysettings['account_details'].'"/></div>
			<div class="explanation">'.$lang['admin_settings_bank_account_text3'].'</div>
			<div class="input"><textarea name="account_holder" rows="4" cols="30">'.$mysettings['account_holder'].'</textarea></div>
		
			<div class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"></div>

			</form>
		</div>
	</div>';
	echo '</p><div class="clearfloat"></div>';
}
?>
