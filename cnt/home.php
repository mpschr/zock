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
global $events_test;
global $events;
global $cont;

echo '<h2>'.$cont->get('home_title').'</h2>';

$active_events = $events_test->getActiveEvents();


//welcome the user a little bit
echo   $cont->get('home_welcome').' '.$cont->get('home_content').'<p />';

//display the public, active events
$nb = ActiveEventNumber();
if($nb > 0){
	echo '<p>'.$cont->get('home_events').'<br>';
	echo '<ul>';
	foreach ($active_events as $event){

        /* @var $event Event */
        $evid = $event->getId();
        echo '<li>'.$event->getName();

        $flcnt = $event->generateEventInfo();
        foreach($flcnt as $sid => $cnt)
            echo makeFloatingLayer($event->getName(), $cnt, 1, $evid.'_'.$sid);

        echo '<a href="javascript: showFloatingLayer(\''.$evid.'_stake\')" title="'.$cont->get('general_show_info').'"> Info </a>||';
        echo ' <a href="?menu=overview&ev='.$evid.'">'.$cont->get('overview_title').'</a>';
        echo '</li>';
	}
	echo '</ul>';
}else{
	//if there aren't any public events active, say it.
	echo $cont->get('home_noevents').'<p />';
}
echo $cont->get('home_fun');
?>
