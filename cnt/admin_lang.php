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

//list the different possilbe actions in the lang section
echo 	'<ul>'
	.'<li><a href="index.php?menu=admin&submenu=lang&langac=addnew">'.$lang['admin_lang_addnew'].'</a><br>'
	.'<li>'.$lang['admin_lang_editbelow'].'<br>'
 	.'<ul>'
 	.'<li><a href="index.php?menu=admin&submenu=lang&langac=listall">'.$lang['admin_lang_listall'].'</a><br>'
	.'<li><a href="index.php?menu=admin&submenu=lang&langac=listalluncompleted">'.$lang['admin_lang_listalluncompleted'].'</a>'
	.'<li><a href="index.php?menu=admin&submenu=lang&langac=listlanguncompleted">'.$lang['admin_lang_listlanguncompleted'].'</a><p>'
	.'</ul></ul><hr>';


//========== edit an element
if ($_REQUEST['langac'] == 'edit'){
	$editrow = $db->query('SELECT * FROM '.PFIX.'_lang WHERE label="'.$_REQUEST['el'].'"');
	$langs = array_keys($editrow[0]);
	echo '<form name="edit" method="POST" action="?menu=admin&submenu=lang&langac=saveedit"><b>'
		.'<table><tr><th>'
		.'Label: </th><th><input name="label" value='.$_REQUEST['el'].' size=50 readonly></th></tr>';
	for ($x=1;$x < sizeof($langs);$x++){
		echo '<tr><th>'.$langs[$x].': </th>';
		echo '<th><textarea name="'.$langs[$x].'" cols="50" rows="3">'.$editrow[0][$langs[$x]].'</textarea></th></tr>';
	}
	echo '<tr><th></th><th><input type="submit" value="'.$lang['general_save'].'"></th></tr>';
	echo '</table></form></b>';





	langNavigate($lang, $link, $_REQUEST['el']);
//========== save edited elements
}else if ($_REQUEST['langac'] == 'saveedit'){

print_r($_POST);
	$langs = array_keys($_POST);
	$update_query = "UPDATE ".PFIX."_lang SET label='".$_POST['label']."'";
	for ($x=1; $x<sizeof($langs); $x++)
		$update_query .= ", ".$langs[$x]."='".$_POST[$langs[$x]]."'";
	$update_query .= " WHERE label='".$_POST['label']."'";
	if($db->query($update_query)){
		echo $lang['general_savedok'];
	}else{
		echo $lang['general_savednotok'];
		echo '<p>'.$update_query;
	}
	langNavigate($lang, $link, $_POST['label']);
	
	//enable steping through empty elements
	$langall = $db->query('SELECT * FROM '.PFIX.'_lang ORDER BY label ASC');
	$x=0;
	$s = sizeof($langall);
	foreach ($langall as $el){
		if ($el['label'] == $_POST['label']){
			$saved = $x;
			break;
		}
		$x++;
	}
	for($up = $x+1, $down = $x-1; ($up < $s && $up > 0) || ($down > 0);){
		if($up > 0 && $langall[$up][$_SESSION['dlang']] == NULL){
			$next_empty = $langall[$up]['label'];
			$up = -1;
		}
		if($down > 0 && $langall[$down][$_SESSION['dlang']] == NULL){
			$previous_empty = $langall[$down]['label'];
			$down = -1;
		}
		if($up > 0) $up++;
		if($down > 0) $down--;
	}

	if (isset($previous_empty)){
		echo ' || <a href="'.$link.'langac=edit&el='.$previous_empty.'">'.$lang['admin_lang_previousempty'].'</a>';
	}
	if (isset($next_empty)){
		echo ' || <a href="'.$link.'langac=edit&el='.$next_empty.'">'.$lang['admin_lang_nextempty'].'</a>';
	}


//========== add a new element
}else if ($_REQUEST['langac'] == 'addnew'){

	$editrow = $db->query('SELECT * FROM '.PFIX.'_lang LIMIT 0,1');
	$langs = array_keys($editrow[0]);
	echo '<form name="addnew" method="POST" action="?menu=admin&submenu=lang&langac=saveadd"><b>'
		.'<table>'
		.'<tr><th>'
		.'Label: </th><th><input name="label" value="" size=50><p></th></tr>';
	for ($x=1;$x < sizeof($langs);$x++){
		echo '<tr><th>'.$langs[$x].': </th>';
		echo '<th><textarea name="'.$langs[$x].'" cols="50" rows="3"></textarea><p></th></tr>';
	}
	echo '<tr><th></th><th><input type="submit" value="'.$lang['general_save'].'"></th></tr>';
	echo '</table></form></b>';
	echo '<p><a href="index.php?menu=admin&submenu=lang">'.$lang['general_goback'].'</a>';
	unset($_POST);

//========== save a new element
}else if ($_REQUEST['langac'] == 'saveadd'){

	$langs = array_keys($_POST);
	$part[1] = "INSERT INTO ".PFIX."_lang (label";
	$part[2] = "VALUES ('".$_POST['label']."'";
	for ($x=1; $x<sizeof($langs); $x++){
		$part[1] .= ", ".$langs[$x];
		$part[2] .= ", '".$_POST[$langs[$x]]."'";
	}
	$update_query .= $part[1].") ".$part[2].");";
	if($db->query($update_query)){
		echo $lang['general_savedok'];
	}else{
		echo $lang['general_savednotok'];
		echo '<p>'.$update_query;
	}
	echo '<p><a href="index.php?menu=admin&submenu=lang">'.$lang['general_goback'].'</a>';
	echo ' || <a href="index.php?menu=admin&submenu=lang&langac=addnew">'.$lang['admin_lang_addnew'].'</a>';

//========== normal view (lists & options)
}else{ 

	///display the different list types on request
	$langall = $db->query('SELECT * FROM '.PFIX.'_lang ORDER BY label ASC');
	
	//=>list all uncompleted (one lang's empty at least)
	if($_REQUEST['langac'] == 'listalluncompleted'){
		foreach ($langall as $row){
			$hasNull = FALSE;
			foreach($row as $el){
				if ($el == NULL) $hasNull = TRUE;
			}
			if ($hasNull) echo '<a href="index.php?menu=admin&submenu=lang&langac=edit&el='.$row['label'].'">'.$row['label'].'</a><br />';
		}
		
	//=>list all uncompleted of acutal language
	}elseif($_REQUEST['langac'] == 'listlanguncompleted'){
		foreach ($langall as $row){
			if ($row[$_SESSION['dlang']] == NULL)
				echo '<a href="index.php?menu=admin&submenu=lang&langac=edit&el='.$row['label'].'">'.$row['label'].'</a><br />';
		}

	//=>the whole list
	}else{
		foreach ($langall as $row){
			echo '<nobr><a href="index.php?menu=admin&submenu=lang&langac=edit&el='.$row['label'].'">'.$row['label'].'</a>';
			echo ' ('.$row[$_SESSION['dlang']].')<br></nobr>';
		}

	}
}

function langNavigate($lang, $link, $curr_el){

	ksort($lang);
	
	$lastel = end ($lang);
	reset ($lang);
	$firstel = current($lang);
	while ( next($lang) != $lastel){
		if(key($lang) == $curr_el){
			if ($firstel != current($lang)) {
				prev($lang);
				$el_before = key($lang);
				next($lang);
			}
			if ($lastel != current($lang)){
				next($lang);
			       	$el_after = key($lang);
			}
			break;
		}
	}

	echo '<p/>';
	if (isset($el_before)) echo '<a href="'.$link.'&langac=edit&el='.$el_before.'">'.$lang['general_goback'].'</a>';
	echo ' || ';
	echo '<a href="index.php?menu=admin&submenu=lang">'.$lang['general_cancel'].'</a>';
	echo ' || ';
	if (isset($el_after)) echo '<a href="'.$link.'&langac=edit&el='.$el_after.'">'.$lang['general_goforward'].'</a>';
}

?>
