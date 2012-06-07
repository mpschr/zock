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
global $db;


if(isset($_REQUEST['ssubmenu'])){
	$nv='notvisible';
	//make a little vertical menu for less mouse moving
	echo createVerticalMenu('events');
    echo  createVerticalMenu(NULL, 'mmopen');
    echo createVerticalMenu(NULL, 'mmclose');
	?>
	<script type="text/javascript">
		function showEventList(){
			var c = document.getElementById("eventlistdiv").getAttribute("class");
			if(c=="notvisible"){
				document.getElementById("eventlistdiv").setAttribute("class", "");
			}else{
				document.getElementById("eventlistdiv").setAttribute("class", "notvisible");
			}
		}
	</script>
	<?
	echo '<a href="javascript: showEventList()">'.$lang['admin_events_showeventlist'].'</a>';
}

//======== the list of events (top of page)
echo '<div id="eventlistdiv" class="'.$nv.'">';
$events_read = $db->query("SELECT * FROM ".PFIX."_events");
echo '<ul>'
	.'<li><a href="'.$link.'evac=addnew">'.$lang['admin_events_addnew'].'</a>'
	.'<li>'.$lang['admin_events_edit']
	.'<ul>';
foreach($events_read as $e){
	$cleanlink = preg_replace('/submenu=(.*)&/', '', $link);
	echo '<li>'.$e['name'].' (<a href="'.$link.'ssubmenu=settings&ev='.$e['id'].'">'.$lang['admin_events_settings_title'].'</a> || '
		.'<a href="'.$link.'ssubmenu=matches&ev='.$e['id'].'">'.$lang['admin_events_matches_title'].'</a> || '
		.'<a href="'.$link.'ssubmenu=results&ev='.$e['id'].'">'.$lang['admin_events_results_title'].'</a>)';

}
echo 	'</ul></ul>';

if(isset($_REQUEST['ssubmenu'])) echo '<hr>';


//========== add new event
if ($_REQUEST['evac'] == 'addnew'){

	//this is error handling.. adjusting classes
	$err['name'] = $err['deadline'] = $err['currency'] = $err['stake'] = $err['round'] = 'title';

	//if there was acuatally an error in the filled form, do as follow:
	if(isset($_SESSION['err'])){
		$err = $_SESSION['err'];
		$post = $_SESSION['post'];
		unset ($_SESSION['err']);
		unset ($_SESSION['post']);
		foreach ($err as $i => $e){
			$err[$i] = ($e) ? 'error'  : 'title';
		}
		//display error message if an error occurred
		if (in_array('error', $err)) echo '<font class="error">'.$lang['error_filledform'].'</font>';
	}	


	infoBarEventCreation(1);
	// the form for the creation of a new event
	echo '<form name="addnew" action="'.$link.'evac=save" method="POST">'
		.'<input type="hidden" name="form" value="ssubmenu=settings&ev=">'
		.'<input type="hidden" name="formname" value="phase1">'
		.'<table class="showform">
			<tr>
				<td class="'.$err['name'].'">'.$lang['admin_events_name'].'</td>
				<td></td>
			</tr><tr>
				<td class="input"><input name="name" size=10 value="'.$post['name'].'"</td>
				<td>'.$lang['general_eg'].': Euro08</td>
			</tr><tr>
				<td class="'.$err['deadline'].'">'.$lang['admin_events_deadline'].'</td>
				<td></td>
			</tr><tr>
				<td class="input"><input name="deadline" size=10 value="'.$post['deadline'].'"></td>
				<td>'.$lang['general_eg'].': 18.05.2009</td>
			</tr><tr>
				<td class="submit"><input type="submit" value="'.$lang['general_save'].'"></td>
				<td></td>
			</tr>
		</table>
	</form>';

//========== save new or changed,inactive event


/*all the form-checks and database updates ov the admin_events section
	are concentrated in this file*/

}elseif (isset($_REQUEST['evac'])){
	include_once('cnt/admin_events_actions.php');
}

echo '</div>'; //close div from top 

//========== everything else (in ssubmenus)
//	=> in specific files

if(isset($_REQUEST['ssubmenu']) && $_REQUEST['evac']!='save'){
	include_once('cnt/admin_events_'.$_REQUEST['ssubmenu'].'.php');	
}
?>
