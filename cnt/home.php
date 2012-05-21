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
?>
<?
global $events;

//$lang=languageSelector();
echo '<h2>'.$lang['home_title'].'</h2>';

//welcome the user a little bit
echo $lang['general_hi'].'<p>'.$lang['home_welcome'].' '.$lang['home_content'].'<p />';

//diplay the public, active events
$nb = ActiveEventNumber();
if($nb > 0){
	echo '<p>'.$lang['home_events'].'<br>';
	echo '<ul>';
	foreach ($events['p'] as $key => $ev){
        if (is_string($key))
            continue;
		if(is_array($events['p']['e'.$ev])){
			echo '<li>'.$events['p']['e'.$ev]['name'];
			$flcnt = generateEventInfo($ev);
			foreach($flcnt as $sid => $cnt)
	            echo makeFloatingLayer($events['p']['e'.$ev]['name'], $cnt, 1, $ev.'_'.$sid);
            echo '<a href="javascript: showFloatingLayer(\''.$ev.'_stake\')" title="'.$lang['general_show_info'].'"> Info </a>||';
			echo ' <a href="?menu=overview&ev='.$ev.'">'.$lang['overview_title'].'</a>';
			echo '</li>';
		}
	}
	echo '</ul>';
}else{
	//if there aren't any public events active, say it.
	echo $lang['home_noevents'].'<p />';
}
echo $lang['home_fun'];
?>
