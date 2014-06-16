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

global $db, $settings, $events, $events_test;

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

	echo '<div class="appearance">';
	if(isset($_REQUEST['showuser'])){
		if (userParticipates($_REQUEST['ev'], $_REQUEST['showuser']) == TRUE){
			$details = $db->query("SELECT login, picture, name, famname, text FROM ".PFIX."_users WHERE id='".$_REQUEST['showuser']."';");
			$imgsrc = 'data/user_img/'.$details[0]['picture'];
			@$imgsize = getimagesize($imgsrc);
			if($imgsize[0] > 230){
				$origwidth = $imgsize[0];
				$imgsize[0] = 230;
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
                    $pointsData[] = $points;
					$money += $row[$_REQUEST['showuser'].'_money'];
					$rank = ($row[$_REQUEST['showuser'].'_ranking'] != NULL) ? $row[$_REQUEST['showuser'].'_ranking'] : $rank;
                    $ranksData[] = $rank;
				}
				$wrong = sizeof($rawdata) - $almost - $correct - $diff;
				$gamestandings = $lang['ranking_rank'].': <b>'.$rank.'</b><br/> '
						.$lang['ranking_points'].': <b>'.$points.'</b><br/> '
						.$lang['ranking_gain'].': <b>'.$money.' '.$events['p']['e'.$_REQUEST['ev']]['currency'].'</b><p/>'
						.$lang['participants_correcttips'].': <b>'.$correct.'</b><br/>'
						.$lang['ranking_almosttips'].': <b>'.$diff.'</b><br/>'
						.$lang['participants_closetips'].': <b>'.$almost.'</b><br/>'
						.$lang['participants_wrongtips'].': <b>'.$wrong.'</b><br/>';

				echo '<table align="center" width="100%">';
				echo '<tr><td colspan="2"><b>'.$details[0]['login'].'</b> aka '.$details[0]['name'].' '.$details['0']['famname'].'<p /></td></tr>';
				echo '<tr><td><img title="'.$details[0]['login'].'" src="'.$imgsrc.'" alt="'.$lang['myprofile_appearance_nopicture'].'" width="'.$imgsize['0'].'px" height="'.$imgsize[1].'px">';
				echo '<br/><font class="piccomment">'.$details[0]['text'].'</font></td>';
				echo '<td class="participantdeitails">'.$gamestandings.'<p/>
					<a href="'.$link.'&menu=overview&col='.$_REQUEST['showuser'].'">'.
					$lang['mytips_tips'].'</a></td></tr>';
                echo '<tr><td><br></td><td></td></tr>';
                echo '<tr><td colspan=2>
                    <b><a href="javascript: doRanksGraph()"> ' . $lang['ranking_rank'] . '</a>
                    <a href="javascript: doPointsGraph()"> ' . $lang['ranking_points'] . ' </a> </b>
		    add: <input style="font-size:9px;" size="10" id="addUser" class="input autouser"/>
                    </td></tr>';
                echo '<tr><td colspan=2>';
                    echo '<div id="chartdiv" height="300px" width="500px">';

                    echo 'babab';
                    
                    echo '</div>';

                echo '</td></tr>';
				echo '<tr><td colspan="2"><p /><a href="'.$link.'ev='.$_REQUEST['ev'].'">'.$lang['general_goback'].'</a></td></tr></table>';
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


	
}else{
	echo $lang['participants_nousers'];
}

$evUsers = (explode(':', $events['u']['e'.$_REQUEST['ev']]['a']));
array_pop($evUsers);

foreach ($userarray as $i => $u) {
	if (in_array($i, $evUsers))
		$autouser .= '"'.$u.'", ';
} 
foreach ($userarray as $i => $u) {
	if (in_array($i, $evUsers))
		$javascriptArray .= "userToId[\"$u\"]=$i; ";
} 
$newurlbit = (isset($_REQUEST['add'])) ? '' : '&add=';


    echo "
    <script type=\"text/javascript\">
        $(document).ready(function() {
            $(\"input.autouser\").autocomplete({
                source: [".$autouser."]
            });
	});
	$( \"input.autouser\" ).autocomplete({
	   close: function(event, ui) {  
            addUserToGraph(); }
	});


      function addUserToGraph() {
		var ustr = document.getElementById('addUser').value;
        if (ustr == '') { return; }
		var urlbit = '$newurlbit';
		var userToId = Array();
		$javascriptArray
        var usid = userToId[ustr];
        if (usid == undefined) { return; }
		document.location = document.location + urlbit + usid + ':';
	}	
    </script>";

global $p,$r,$u;

#echo '<div id="chartdiv">asdfawfwef</div>';
$r[] = $ranksData;
$u = array($details[0]['login']);
$p[] = $pointsData;

if ($_SESSION['userid'] != $_REQUEST['showuser']) {
	addToPlot('you',$_SESSION['userid']);
}
if (isset($_REQUEST['add'])) {
	$add = preg_split('/:/',$_REQUEST['add']);
	array_pop($add);
	foreach ($add as $id)
		addToPlot($userarray[$id],$id);
}
writeGraphScript($p,$r,$u,eventUserNumber($_REQUEST['ev']));

function addToPlot($user, $userid) {

	global $p,$r,$u,$db;
    $pUser = 0;
	$rawdata = $db->query("SELECT ".$userid."_points, ".$userid."_ranking, ".$userid."_money FROM ".PFIX."_event_".$_REQUEST['ev']. " WHERE score_h IS NOT NULL ORDER BY time");
				foreach ($rawdata as $row){
                    $pUser = $pUser + $row[$userid."_points"];
                    $pointsUser[] = $pUser;
                    $ranksUser[] = $row[$userid."_ranking"];
                }
	$p[] = $pointsUser;
	$r[] = $ranksUser;
	$u[] = $user;
}

function plotSeries($series) {
    $str = '';
    foreach ($series as $s) { 
        $str.= '[';
        $i = 1;
        foreach ($s as $v) {
            $str.= "[$i,$v],";
            $i++;
        }
        $str.= '],';
    }
    return $str;
}

function writeGraphScript ($points, $ranks, $users, $rankmax) {

    
    global $lang;
    $series = '';
    foreach ($users as $u)
        $series .= "{label:'".$u."'},";

    // points
    $pointsGraph = "
    function doPointsGraph() {
            document.getElementById('chartdiv').innerHTML = '';
            $.jqplot('chartdiv',  [ ". plotSeries($points) ." ],
                {  title:'". $lang['ranking_points']."',
                   series:[".$series."],
                   legend:{show:true,location:'nw'}
                });
    }
    ";

    //echo $pointsGraph;
    //ranks

    $rankticks = '';
    for ($i=$rankmax; $i>0; $i-- ) 
           $rankticks.= "[$i,$i],"; 
    $ranksGraph = "
    function doRanksGraph() {
            document.getElementById('chartdiv').innerHTML = '';
            $.jqplot('chartdiv',  [ ". plotSeries($ranks) ." ],
                {  title:'". $lang['ranking_rank']."',
                   series:[".$series."],
                   legend:{show:true,location:'nw'},
                   axes:{
                        yaxis:{ ticks:[".$rankticks."] }
                   },
                   highlighter: {
                        show: true,
                        sizeAdjust: 7.5
                   },
                   cursor: {
                        show: false
                   }
                });
    }
    ";

    if ($_REQUEST['points'] != 'ranks') {
        $displaygraph = 'doPointsGraph();';
    } else {
        $displaygraph = 'doRanksGraph();';
    }

    echo "
        <script type=\"text/javascript\">
        $pointsGraph

        $ranksGraph

        $displaygraph;
        </script>
    
    "; 
}
