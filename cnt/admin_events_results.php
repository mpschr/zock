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



//========== show  results
global $db, $settings, $events;
$bdp_style = 'style_'.$settings['style'];


// is it an inactive event?
if (!(isset($events['u']['e'.$_REQUEST['ev']]))){
	echo '<h3>'.$events['i']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_results_title'].'</h3>';
	if($events['i']['e'.$_REQUEST['ev']]['active']<0) infoBarEventCreation(2);
	else infoBarEventCreation(3);
	echo $lang['admin_events_activatefirst'];
}else{


	//show all of it!
	
	$evdat = $events['u']['e'.$_REQUEST['ev']];
	echo '<h3>'.$events['u']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_results_title'].'</h3>';

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
		$idarray = explode(':', $data['ids']);
	}

	//get the info by what it content should be ordered
	$orderby = (isset($_REQUEST['orderby'])) ? explode(':', $_REQUEST['orderby']) : explode(':', 'time:ASC');

	//get the info by applying the insight of $orderby
//	$query = "SELECT id, time, matchday, home, visitor, score_v, score_h, score_special
	$query = "SELECT * 
				FROM ".PFIX."_event_".$_REQUEST['ev'].
				" ORDER BY ".$orderby[0]." ".$orderby[1].";";
	$bdp_matches =  $db->query($query);
	$bdp_rows =  $db->row_count($query);
	
	//$mnb stands for Mantch NumBer, is necessary to limit the amount of matches displayed
	$mnb = (isset($_REQUEST['mnb'])) ? $_REQUEST['mnb'] : 1;

	
	if($bdp_matches == NULL){
			//well, there's nothing to display
			echo $lang['general_nomatches'];
	}else{


		$tipplus = '( 1 /';
		if(!($evdat['ko_matches']=='only' && $evdat['enable_tie']=='no')){
			$tipplus .= ' X /';
			$colspan = 3;
		}else{
			$colspan = 2;
		}
		$tipplus .= ' 2 )';


		//the form
		echo '<form action="?menu=admin&submenu=events&'.'evac=saveresults&which='.$mnb.'" method="POST" name="matches">';
		echo '<table class="showmatches" id="showresults">';
		echo '<tr class=title>
			<td class=title><a href="'.$link.orderIt('id', $orderby, $link_query).'"> '.$lang['general_id'].'</a></td>
			<td class=title><a href="'.$link.orderIt('time', $orderby, $link_query).'"> '.$lang['admin_events_time'].'</a></td>
			<td class=title><a href="'.$link.orderIt('matchday', $orderby, $link_query).'"> '.$lang['admin_events_matchday'].'</a></td>
			<td class=title><a href="'.$link.orderIt('home', $orderby, $link_query).'"> '.$lang['admin_events_home'].'</a></td>
			<td class=title><a href="'.$link.orderIt('visitor', $orderby, $link_query).'"> '.$lang['admin_events_visitor'].'</a></td>';
			if($evdat['score_input_type']=='results'){
				echo '<td class=title>'.$lang['admin_events_score'].'</td>';
			}else{
				echo '<td class=title colspan="'.$colspan.'">'.$lang['admin_events_score'].' '.$tipplus.'</td>';
			}
			echo '<td class=title>'.$lang['admin_events_special'].'<td>
			</tr>';

		if (!isset($_REQUEST['orderby']) && !isset($_REQUEST['mnb'])){
			$closestGame = closestGame($_REQUEST['ev'], time());
			$page = floor($closestGame/$settings['formlines']);
			$mnb = $page * $settings['formlines'] + 1;
			echo $mnb;
		}
		
		
		foreach($bdp_matches as $nb => $m){
		
			$start = $mnb;
			$limit = $mnb + $settings['formlines'];
			
			if ($nb+1 >= $start && $nb+1 < $limit){
			
				$lines++;
				$ids .= $m['id'].':';
				
				//further error handling
				$id = $m['id'];
				$imgsrc = 'src/'.$bdp_style.'/img/edit.png';

				//decide if the data in the forms should come from db or error the $_post array
				if (isset($wrongs) && $data['ro_'.$m['id']] == 'false'){
					//get the data the user entered and invoked an error
					$score_h = $data['score_h_'.$m['id']];
					$score_v = $data['score_v_'.$m['id']];
					$special = $data['special_'.$m['id']];
					$imgsrc = 'src/'.$bdp_style.'/img/edit_cancel.png';
					if(isset($wrongs[$m['id']])){
						$id =  '<font class=error>-></font>';
						$robool = "false";
						$ro = 'class=""';
					}
				}else{
					$score_h = $m['score_h'];
					$score_v = $m['score_v'];
					$special = $m['score_special'];
					//readonly per default
					$robool = "true";
					$ro = 'class="readonly" readonly="readonly"';
					$dis = 'class="readonly" disabled="disabled"';
					$checked[$m['score']] = 'checked="checked"';
				}
				$time1 = date('d.m.Y', $m['time']);
				$time2 = date('H:i', $m['time']);
				$matchday = $m['matchday'];
				$home = $m['home'];
				$visitor = $m['visitor'];
	
				echo '<tr>
					<td class="input"> '.$id.'</td>
					<td class="input">'.$time1.' '.$lang['general_time_at'].' '.$time2.'</td>
					<td class="input">'.$matchday.'</td>
					<td class="input">'.$home.'</td>
					<td class="input">'.$visitor.'</td>';
/*					echo '<td class="input"><input id="h_'.$m['id'].'" 
								'.$ro.'
								name="score_h_'.$m['id'].'" 
								size="2" value="'.$score_h.'"> : '
							.'<input id="v_'.$m['id'].'" 
								'.$ro.'
								name="score_v_'.$m['id'].'" 
								size="2" value="'.$score_v.'"></td>*/
					if($evdat['score_input_type']=='results'){
						echo '<td class="input"><input id="h_'.$m['id'].'" 
									'.$ro.'
									name="score_h_'.$m['id'].'" 
									size="2" value="'.$score_h.'"> : '
								.'<input id="v_'.$m['id'].'" 
									'.$ro.'
									name="score_v_'.$m['id'].'" 
									size="2" value="'.$score_v.'"></td>';
					}elseif($evdat['score_input_type']=='toto'){
						echo '<td class="input">';
						echo '<input '.$dis.' id="s1_'.$m['id'].'" type="radio" value="1" '.$checked['1'].' name="toto_'.$m['id'].'">';
						echo '</td>';
						if(!($evdat['ko_matches']=='only' && $evdat['enable_tie']=='no')){
							echo '<td class="input">';
							if($m['komatch'] && $evdat['enable_tie']!='yes')
								echo '<font id="sX_'.$m['id'].'">--</font>';
							else
								echo '<input '.$dis.' id="sX_'.$m['id'].'" type="radio" value="3" '.$checked['3'].' name="toto_'.$m['id'].'">';
							echo '</td>';
						}else{
							//dummy
							echo '<font id="sX_'.$m['id'].'"></font>';
						}
						echo '<td class="input">';
						echo '<input '.$dis.' id="s2_'.$m['id'].'" type="radio" value="2" '.$checked['2'].' name="toto_'.$m['id'].'">';
						echo '</td>';
					}



					echo '<td class="input"><input id="special_'.$m['id'].'" 
								'.$ro.' 
								name="special_'.$m['id'].'" 
								size="3" value="'.$special.'"></td>';
					echo '<td class="input"><a href="javascript: 
							editResult(\''.$m['id'].'\', \''.$lines.'\')">
							<img id="im_'.$m['id'].'" src="'.$imgsrc.'" 
							alt="'.$lang['general_edit'].'" title="'.$lang['general_edit'].'" /></a></td>';
					echo '</tr>';
				echo '<input id="ro_'.$m['id'].'" name="ro_'.$m['id'].'" type="hidden" value="'.$robool.'">';
				echo '<input id="komatch_'.$m['id'].'" name="komatch_'.$m['id'].'" type="hidden" value="'.$m['komatch'].'">';
				unset($checked);
			}
		}


	}


	echo '<input name="query" type="hidden" value="'.$link_query.'">';
	echo '<input name="event" type="hidden" value="'.$_REQUEST['ev'].'">';
	echo '<input name="ids" type="hidden" value="'.$ids.'">';
//	for javascript to read out infos
	echo '<input name="score_input_type" id="score_input_type" type="hidden" value="'.$evdat['score_input_type'].'" />';
	echo '<input name="style" id="style" type="hidden" value="'.$bdp_style.'" />';
	echo '<input name="edit" id="edit" type="hidden" value="'.$lang['general_edit'].'" />';
	echo '<input name="cancel" id="cancel" type="hidden" value="'.$lang['general_cancel'].'" />';
	echo '<tr class="submit"><td></td><td class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"></td></tr>';
	echo '</table>';
	echo '</form>';
	echo '<p />';



	//skip pages
	if (!(isset($err))){
		$filter = preg_replace( '/mnb=([0-9]+)([& ])/', '',$link_query);
		if($mnb > 1){
			$gonb = $mnb-$settings['formlines'];
			if ($gonb < 1) $gonb = 1;
			echo '<a href="'.$link.$filter.'mnb='.$gonb.'">'.$lang['general_goback'].'</a> | ';
		}

		echo $lang['general_page'];
		for($x=1 ; $x <= $bdp_rows; $x += $settings['formlines']){
			$y++;
			if ($x!=$mnb){
				echo '  <a href="'.$link.$filter.'mnb='.$x.'">'.$y.'</a>';
			}else{
				echo '  '.$y;
			}
		}


		if($mnb + $settings['formlines'] < $bdp_rows){
			$gonb = $mnb+$settings['formlines'];
			if ($gonb > $bdp_rows) $gonb = $bdp_rows;
			echo ' | <a href="'.$link.$filter.'mnb='.$gonb.'">'.$lang['general_goforward'].'</a>';
		}
	}
}

?>




