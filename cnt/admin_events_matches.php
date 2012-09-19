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

//this file consists of a lot of forms...


//========== show matches
global $db, $settings, $events;


$inactive = (isset($events['i']['e'.$_REQUEST['ev']])) ? true : false;
// is it an event in phase 1 or 2?
if ($events['i']['e'.$_REQUEST['ev']]['active']==-1){
	$body .=  '<h3>'.$events['i']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_matches_title'].'</h3>';
	infoBarEventCreation('2');
	$body .=  $lang['admin_events_fromphase3'];
}else{
//step 3 or activated
//show all of it!
	
	if ($inactive){
		$body .=  '<h3>'.$events['i']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_matches_title'].'</h3>';
		$ko_matches = $events['i']['e'.$_REQUEST['ev']]['ko_matches'];
		$stake_mode = $events['i']['e'.$_REQUEST['ev']]['stake_mode'];
		infoBarEventCreation('3');
	}else{
		$body .=  '<h3>'.$events['u']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_matches_title'].'</h3>';
		$ko_matches = $events['u']['e'.$_REQUEST['ev']]['ko_matches'];
		$stake_mode = $events['u']['e'.$_REQUEST['ev']]['stake_mode'];
	}
	// error handling....
	if (isset($_SESSION['err'])){
		$err = $_SESSION['err'];
		unset($_SESSION['err']);
		if(isset($_SESSION['post'])) errorMsg('filledform');
		else{
			 $body .=  errorMsg($err[1]);
			unset($err);
		}
		$data = $_SESSION['post'];
		unset($_SESSION['post']);
		$wrongs['dummy'] = 'what';
		foreach ($err as $id){
			$wrongs[$id] = 'error';
		}
	}
	
	//get the info by what it content should be ordered
	$orderby = (isset($_REQUEST['orderby'])) ? explode(':', $_REQUEST['orderby']) : explode(':', 'id:ASC');

	//filtering
	if (isset($_REQUEST['filter'])){
		$filter = " WHERE ";
		$f = preg_split('/:/', $_REQUEST['filter']);
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

	//get the info by applying the insight of $orderby
	if ($orderby[0] == 'matchday_id') $orderplus = ", time ASC";
	$query = "SELECT id, time, matchday, home, visitor, komatch  
				FROM ".PFIX."_event_".$_REQUEST['ev']
				.$filter.
				" ORDER BY ".$orderby[0]." ".$orderby[1].$orderplus.";";
	$bdp_matches =  $db->query($query);
	$bdp_rows =  $db->row_count($query);

	//$mnb stands for Mantch NumBer, is necessary to limit the amount of matches displayed
	$mnb = (isset($_REQUEST['mnb'])) ? $_REQUEST['mnb'] : 1;


	if($bdp_matches == NULL && !isset($data) && !$_REQUEST['filter']){
			//well, there's nothing to display
			$body .=  $lang['admin_events_nomatches'];
			$nomatches = 1;
	
	}


	if(true){
		if($bdp_rows == 0 && isset($_REQUEST['filter'])){
			//no results with this filter
			$body .=  errorMsg('filter_emptyresults');
		}
		if(!isset($data)) $body .=  $lang['admin_events_matches_content'].'<p/>';

		?>
		<script type="text/javascript">
			function showMatchdayIds(){
				document.getElementById("matchdays").style.display = "block";
			}
		</script>
		<?

		//manipulation links
		if (!(isset($err))){
			if($stake_mode!='permatch'){
				$body .=  '<a href="javascript: addNewMatch()">+ '.$lang['admin_events_matches_title'].'</a>';
				$body .=  ' || <a href="javascript: showFloatingLayer(\'2\')">'.$lang['admin_events_addemptymatches'].'</a>';
				$body .=  ' || ';
			}
			//no matches yet -> no competitor change, but install event
			if(!$nomatches){
				$body .=  '<a href="javascript: showFloatingLayer(\'1\')">'.$lang['admin_events_changecompetitor'].'</a>';
				$body .=  ' || ';
			}
			if(!$nomatches){
				$body .=  '<a href="javascript: showFloatingLayer(\'3\')">'.$lang['admin_events_installevent'].'</a>';
				$body .=  ' || ';
			}
		}

		//div for matchid-arranging
		if(!$nomatches && !(isset($err))){
			$query = "SELECT DISTINCT matchday, matchday_id FROM 
				".PFIX."_event_".$_REQUEST['ev'].
				" WHERE matchday  NOT LIKE '--'".
				" ORDER BY matchday_id ASC;";
			$md = $db->query($query);
			if (sizeof($md) > 0){
				//=>another manipulation link:  layer is made after this form!
				$body .=  '<a href="javascript: showMatchdayIds()">'.$lang['admin_events_arrangematchdays'].'</a>';
				$body .=  '<p/>';
				$body .=  '<form name="arrangematches" action="?menu=admin&submenu=events&evac=arrangematchdays" method="POST" >';
				$body .=  '<table id="matchdays" style="display: none;">';

					$col = $row = 0;
					$number = sizeof($md);
					$percolumn = ceil($number/4);
					for ($i=0;$i<$percolumn*4;$i++){
						$col++;
						if (($i+1)%4 == 1){
							$body .=  '<tr>';
							$row++;
						}
						$mdrow = $col*$percolumn-($percolumn-$row)-1;
						if($mdrow<$number){
							if($md[$mdrow]['matchday_id'] == '999999') $md[$mdrow]['matchday_id'] = '';
							$body .=  '<td width="150px">';
							$body .=  '<input size="2" name="md_'.($i+1).'" readonly class="readonly" value="'.$md[$mdrow]['matchday'].'"/>:';
							$body .=  '  <input size="2" name="mdid_'.($i+1).'" value="'.$md[$mdrow]['matchday_id'].'" tabindex="'.($mdrow+1).'"/>';
							$body .=  '</td>';
						}else{
							$body .=  '<td></td>';
						}
						if(($i+1)%4 == 0){
							$body .=  '</tr>';
							$col = 0;
						}
					}
					$body .=  '<input type="hidden" name="md_nb" value="'.$number.'"/>';
					$body .=  '<input type="hidden" name="eve" value="'.$_REQUEST['ev'].'"/>';
					$body .=  '<tr><td colspan="4"><input type="submit" value="'.$lang['general_savechanges'].'"/></td></tr>';
				$body .=  '</table>';
				$body .=  '</form>';
			}
		}

		//filterform
		if(!isset($data)){
			$filterurl = preg_replace('/(filter=)[a-zA-Z0-9:]+[&]/i', '', $link_query); 
			$filterurl = $link.$filterurl;
			$body .=  '<form action="javascript: filter(\''.$filterurl.'\')">
				<a href="javascript: showFilter()" >'.$lang['general_filter'].'</a>
				<div id="filterform" class="notvisible" >
				<select id="filter_on" onChange="filterChange()">
				<option value="nofilter"></option>
				<option value="team" '.$f_team.'>'.$lang['general_team'].'</option>
				<option value="home" '.$f_home.'>'.$lang['admin_events_home'].'</option>
				<option value="visitor" '.$f_visitor.'>'.$lang['admin_events_visitor'].'</option>
				<option value="matchday" '.$f_matchday.'>'.$lang['admin_events_matchday'].'</option>
				</select>';
			$body .=  ' <span id="filter_contains">'.$lang['general_contains'].'</span> ';
			$body .=  ' <span id="filter_is" class="notvisible">'.$lang['general_is'].'</span> ';
			$body .=  '<input id="filter_this" value="'.$f[1].'" size="15"/>';
			$body .=  '<a href="javascript: filterUnset()"> x </a>';
			$body .=  ' <input type="submit" value="'.$lang['general_filterverb'].'"/>';
			$body .=  '</div>';	
			$body .=  '</form>';
		}

	//the form
	$body .=  '<form action="?menu=admin&submenu=events&'.'evac=savematches&which='.$mnb.'" method="POST" name="matches">';
	$body .=  '<table class="showmatches" id="showmatches">';
	$body .=  '<tr class=title>
		<td class=title><a href="'.$link.orderIt('id', $orderby, $link_query).'"> '.$lang['general_id'].'</a></td>
		<td class=title><a href="'.$link.orderIt('time', $orderby, $link_query).'"> '.$lang['admin_events_time'].'</a></td>
		<td class=title><a href="'.$link.orderIt('matchday_id', $orderby, $link_query).'"> '.$lang['admin_events_matchday'].'</a></td>
		<td class=title><a href="'.$link.orderIt('home', $orderby, $link_query).'"> '.$lang['admin_events_home'].'</a></td>
		<td class=title><a href="'.$link.orderIt('visitor', $orderby, $link_query).'"> '.$lang['admin_events_visitor'].'</a></td>';
		if($ko_matches=='yes')	$body .=  '<td class=title>'.$lang['admin_events_komatch'].'</td>';
		$body .=  '</tr>';

		//if filter is set, watch out that mnb is not too high
		if($bdp_rows < $mnb) $mnb = 1; 	

		//show the matches		
		foreach($bdp_matches as $nb => $m){
			
			$start = $mnb;
			$limit = $mnb + $settings['formlines'];
			
			if ($nb+1 >= $start && $nb+1 < $limit){
				//check if match should still be editable
				$now = time();
				$bet_until = betUntil($m['time'], $_REQUEST['ev']);
				unset($sel);
				unset($past);
				if($now>$bet_until){
					$ro  = 'readonly="readonly"';
					$ro2 = 'readonly';
					$past = 1;
				}				
	
				$lines++;
				//further error handling 
				//decide if the data in the forms should come from db or error the $_post array
				if (isset($wrongs[$m['id']])){
					//get the data the user entered and invoked an error
					$id =  '<font class=error>-></font>';
					$time1 = $data['time1_'.$m['id']];
					$time2 = $data['time2_'.$m['id']];
					$matchday = $data['matchday_'.$m['id']];
					$home = $data['home_'.$m['id']];
					$visitor = $data['visitor_'.$m['id']];
					$sel[$m['komatch']] = ' checked="checked" ';
				}else{
					$id = $m['id'];
					$time1 = date('d.m.Y', $m['time']);
					$time2 = date('H:i', $m['time']);
					$matchday = $m['matchday'];
					$home = $m['home'];
					$visitor = $m['visitor'];
					$sel[$m['komatch']] = ' checked="checked" ';
				}

	
				//the form
				$body .=  '<tr>
					<td class="input"> '.$id.'</td>
					<td class="input"><input class=" '.$ro2.' inpupt-small datepicker "
								name="time1_'.$m['id'].'"
								value="'.$time1.'" '.$ro.'>
						'.$lang['general_time_at'].'
							<input class="'.$ro2.' inpupt-small"
								name="time2_'.$m['id'].'"
								value="'.$time2.'" '.$ro.'"></td>
					<td class="input"><input class="'.$ro2.' automatchday inpupt-small"
						name="matchday_'.$m['id'].'"
						value="'.$matchday.'" '.$ro.'"></td>
					<td class="input"><input class="'.$ro2.' autoteam" 
						name="home_'.$m['id'].'" size="15" 
						value="'.$home.'" '.$ro.'"></td>
					<td class="input"><input class="'.$ro2.' autoteam" 
						name="visitor_'.$m['id'].'" size="15" 
						value="'.$visitor.'" '.$ro.'"></td>';
				if($ko_matches=='yes'){
					$body .=  '<td class="input">';
					if(!$past){
						$body .=  '<input type="radio" class="'.$ro2.'" 
						name="komatch_'.$m['id'].'" 
						value="1" '.$sel[1].'">';
					}
					if(!$past || $past && isset($sel[1])) $body .=  $lang['general_yes'];
					if(!$past){
						$body .=  '<input type="radio" class="'.$ro2.'" 
						name="komatch_'.$m['id'].'" 
						value="0" '.$sel[0].'">';
					}
					if(!$past || $past && isset($sel[0])) $body .=  $lang['general_no'].'
					</td>';
				}
				$body .=  '</tr>';
					$body .=  '<input type="hidden" name="ro_'.$m['id'].'" value="'.$ro2.'">';

				//unset these two variables for they won't affect the next row
				unset($ro); unset($ro2);
			}
		}	

		
		//and error handling agein..this time the new added matches
		if (isset($data)){
			for($x=1 ; $x <= $data['adds'] ; $x++){
				$index = (isset($wrongs['new'.$x])) ? '<font class=error>-></font>' : 'new'.$x;
				$body .=  '<tr>
					<td class="input"> '.$index.'</td>
					<td class="input"><input name="newtime1_'.$x.'" class="datepicker" 
							size="10" value="'.$data['newtime1_'.$x].'">
						'.$lang['general_time_at'].'
							<input name="newtime2_'.$x.'" 
							size="4" value="'.$data['newtime2_'.$x].'"></td>
					<td class="input"><input name="newmatchday_'.$x.'" class="automatchday" 
							size="8" value="'.$data['newmatchday_'.$x].'"></td>
					<td class="input"><input name="newhome_'.$x.'" class="autoteam" 
							size="15" value="'.$data['newhome_'.$x].'"></td>
					<td class="input"><input name="newvisitor_'.$x.'" class="autoteam" 
						size="15" value="'.$data['newvisitor_'.$x].'"></td>';
				if($ko_matches=='yes'){
					$sel[$data['newkomatch_'.$x]] = 'checked="checked"';
					$body .=  '<td class="input">';
						$body .=  '<input type="radio"  
						name="komatch_'.$m['id'].'" 
						value="1" '.$sel[1].'>';
						$body .=  $lang['general_yes'];
						$body .=  '<input type="radio"  
						name="komatch_'.$m['id'].'" 
						value="0" '.$sel[0].'>';
						$body .=  $lang['general_no'];
					$body .=  '</td>';
					}
					$body .=  '</tr>';
				}
			}

		}


	//add some nonvisible new matches which can be displayed..limited to 20
	$query = "SELECT MAX(time) AS maxtime
                FROM ".PFIX."_event_".$_REQUEST['ev'].";";
	$latestdate = $db->query($query);
	if(!isset($data)){
		for($x=1;$x<21;$x++){
			$body .=  '<tr id="newtr_'.$x.'" class="notvisible">
				<td class="input">new'.$x.'</td>
				<td class="input"><input name="newtime1_'.$x.'" class="datepicker" value="'.date('d.m.Y', $latestdate[0]['maxtime']).'" size="10">
					'.$lang['general_time_at'].'
					<input name="newtime2_'.$x.'" value="hh:mm" size="4"></td>
					<td class="input"><input name="newmatchday_'.$x.'" class="automatchday" size="8"></td>
					<td class="input"><input name="newhome_'.$x.'" class="autoteam" size="15"></td>
					<td class="input"><input name="newvisitor_'.$x.'" class="autoteam" size="15"></td>';
				if($ko_matches=='yes'){
					$body .=  '<td class="input">';
						$body .=  '<input type="radio"  
						name="newkomatch_'.$x.'" 
						value="1" >';
						$body .=  $lang['general_yes'];
						$body .=  '<input type="radio" 
						name="newkomatch_'.$x.'" 
						value="0" checked="checked">';
						$body .=  $lang['general_no'];
					$body .=  '</td>';
				}
			$body .=  '</tr>';
		}
	}



    //autocomplete team

	$query = "SELECT DISTINCT(team) FROM (
                SELECT home as team FROM ".PFIX."_event_".$_REQUEST['ev']."
                UNION
                SELECT visitor as team FROM ".PFIX."_event_".$_REQUEST['ev']."
              ) as teams
			  ORDER BY team ASC;";
	$teams = $db->query($query);
    foreach ($teams as $t) 
        $autoteam .= '"'.$t['team'].'", ';
    
    //autocomplete matchday 
	$query = "SELECT DISTINCT(matchday) 
                FROM ".PFIX."_event_".$_REQUEST['ev']."
			  ORDER BY matchday ASC;";
	$matchday = $db->query($query);
    foreach ($matchday as $m) 
        $automatchday .= '"'.$m['matchday'].'", ';
    
    //autocomplete script

    $body .=  "
    <script>
        $(document).ready(function() {
            $(\"input.autoteam\").autocomplete({
                source: [".$autoteam."]
            });
            $(\"input.automatchday\").autocomplete({
                source: [".$automatchday."]
            });
        });
       $(\".datepicker\").datepicker({dateFormat: 'dd.mm.yy'}); 
     </script>";




	//this variable is error handling as well
	$addsbefore =  (isset($data)) ? $data['adds'] : '0';

	//hidden information for later check and/or javascript function
	$body .=  '<input name="adds" id="adds" type="hidden" value="'.$addsbefore.'">';
	$body .=  '<input id="enoughadds" type="hidden" value="'.$lang['admin_events_enoughadds'].'">';
	$body .=  '<input name="query" type="hidden" value="'.$link_query.'">';
	$body .=  '<input name="event" type="hidden" value="'.$_REQUEST['ev'].'">';
	$body .=  '<input name="lines" type="hidden" value="'.$lines.'">';
	$body .=  '<tr class="submit"><td></td><td class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"></td></tr>';
	$body .=  '</table>';
	$body .=  '</form>';
	$body .=  '<p />';

		//make the floting Layer for changeing competitor
		if(!$nomatches){
			$flcnt = '<form name="changecompetitor" action="?menu=admin&submenu=events&evac=changecompetitor" method="POST">';
			$flcnt .= '<table><tr>';
			$flcnt .= '<input name="eve" type="hidden" value="'.$_REQUEST['ev'].'">';
			$flcnt .= '.<td>'.$lang['admin_events_competitortochange'].'</td>';
			$flcnt .= '<td><input class="input_fl autoteam" name="tochange" size="15" /></td>';
			$flcnt .= '</tr><tr>';
			$flcnt .= '<td>'.$lang['admin_events_competitorchangeto'].'</td>';
			$flcnt .= '<td><input class="input_fl autoteam" name="changeto" size="15" /></td>';
			$flcnt .= '</td></tr></table><input type="submit" value="'.$lang['general_savechanges'].'"/></form>';
			$body .=  makeFloatingLayer($lang['admin_events_changecompetitor'], $flcnt);

			$flcnt = '<form name="changecompetitor" action="?menu=admin&submenu=events&evac=addemptymatches" method="POST">';
			$flcnt .= '<input name="eve" type="hidden" value="'.$_REQUEST['ev'].'">';
			$flcnt .= '<input name="ko_matches" type="hidden" value="'.$ko_matches.'">';
			$flcnt .= $lang['admin_events_howmanyemptymatches'].'  ';
			$flcnt .= '<input class="input_fl" name="emptymatches" size="15" />';
			$flcnt .= '<input type="submit" value="'.$lang['general_savechanges'].'"/></form>';
			$body .=  makeFloatingLayer($lang['admin_events_addemptymatches'], $flcnt, 1, 2);

		}
		if(!$nomatches){
			$flcnt = '<form name="installevent" action="?menu=admin&submenu=events&evac=installevent" method="POST" enctype="multipart/form-data">';
			$flcnt .= '<table><tr><td>';
			$flcnt .= $lang['admin_events_existantmatchesremoved'];
			$flcnt .= '<input name="eve" type="hidden" value="'.$_REQUEST['ev'].'"></td></tr>';
			$flcnt .= '<tr><td><input name="eventup" size="30" type="file"></td></tr>';
			$flcnt .= '<tr><td>Delimiter:<br/> <input name="delimiter" size="10" type="text"></td></tr>';
			$flcnt .= '</table><input type="submit" value="'.$lang['general_upload'].'"/></form>';
			$body .=  makeFloatingLayer($lang['admin_events_installevent'], $flcnt, 1, 3);
		}


	//skip pages
	if (!(isset($err))){
		$queryfilter = preg_replace( '/&mnb=([0-9]+)/', '',$link_query);
		if($mnb > 1){
			$gonb = $mnb-$settings['formlines'];
			if ($gonb < 1) $gonb = 1;
			$body .=  '<a href="'.$link.$queryfilter.'mnb='.$gonb.'">'.$lang['general_goback'].'</a> | ';
		}

		$body .=  $lang['general_page'];
		for($x=1 ; $x <= $bdp_rows; $x += $settings['formlines']){
			$y++;
			if ($x!=$mnb){
				$body .=  '  <a href="'.$link.$queryfilter.'mnb='.$x.'">'.$y.'</a>';
			}else{
				$body .=  '  '.$y;
			}
		}


		if($mnb + $settings['formlines'] < $bdp_rows){
			$gonb = $mnb+$settings['formlines'];
			if ($gonb > $bdp_rows) $gonb = $bdp_rows;
			$body .=  ' | <a href="'.$link.$queryfilter.'mnb='.$gonb.'">'.$lang['general_goforward'].'</a>';
		}
	}
}
?>




















