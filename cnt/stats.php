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

global $db, $settings, $events, $events_test,$cont;

    $userevents = $events_test->getUserEvents();
    $nb = sizeof($userevents);
    /* @var $thisevent Event */
    $thisevent =  null;

if($nb < 1){
    //no event
    echo $lang['mytips_participatefirst'];

}elseif($nb == 1){
    //one event
    $thisevent = $userevents[0];
}elseif($nb > 1){

    //the it can be in the session
    //after having looked at a public event in the overview section
    if (isset($_SESSION['currevent'])) {
        $currevent = $events_test->getEventById($_SESSION['currevent']);
        $thisevent = ($currevent->userIsApproved($_SESSION['userid'])) ?
            $currevent :
            $userevents[0];
    } else {
        $thisevent = $userevents[0];
    }
}
//$_REQUEST['ev'] overrules the insight of the event handling :)
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent->getId();
//update the current event variable in Session
$_SESSION['currevent'] = $_REQUEST['ev'];

//when a curious user modiefies the url...
if(!$thisevent->userIsApproved($_SESSION['userid']) && $nb > 0){
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
	echo $lang['participants_content'];


    echo $events_test->createEventsTabs($userevents);


    $plotter = new Plotter($thisevent);
    $ranking = new Ranking($thisevent);
    $rankOrder = array_keys($ranking->getRanking());
    $langDict = array('points' => $cont->get('ranking_points'));


    echo "<div class='row'><div class='span7'>
            <span id='chartPointsInfo'>click chart</span>
            <div id='chartPoints'></div></div></div>";


    echo "
        <div id='chartPointsScript'>
        <script type=\"text/javascript\">
        ".$plotter->rankingBarPlot($rankOrder,'chartPoints',$langDict)."
        </script>
        <div>";


    echo '<div class="appearance">';
	if(isset($_REQUEST['showuser'])){
		if (userParticipates($_REQUEST['ev'], $_REQUEST['showuser']) == TRUE){
            //TODO
        }else{
		//if the requested user doesn't participate in the given event
			echo errorMsg('doesnotparticipate');
		}
	}else{


	}
	echo '</div>';


	
}else{
	echo $lang['participants_nousers'];
}
