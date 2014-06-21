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

global $cont, $events_test, $db, $lang, $events;

$userevents = $events_test->getUserEvents();
$nb = sizeof($userevents);
/* @var $thisevent Event */
$thisevent = null;

if ($nb < 1) {
    //no event
    $body .= $lang['mytips_participatefirst'];

} elseif ($nb == 1) {
    //one event
    $thisevent = $userevents[0];
} elseif ($nb > 1) {

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
//$_REQUEST['ev'] overrules $_SESSION['currevent']
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent->getId();
//update the current event variable in Session
$_SESSION['currevent'] = $_REQUEST['ev'];


//when a curious user modiefies the url...
if (!userParticipates($_REQUEST['ev']) && $nb > 0) {
    //if the user is registered to an event and tries to view the comments of another event
    errorPage('notinevent');
    exit;
}
//=============== acutal content

//select from db ordered by time and only matches with results!
//*simulation*/ /*

$bets = $thisevent->getBetsContainer()->getBets();
$betNb = sizeof($bets);


//if there are no matches yet for this event
if ($betNb == 0) {
    $body .= $lang['general_nomatches'];
} else {

    ?>
<script type="text/javascript">
    function showUntil(url, what) {
        var val;
        if (what == "matchday_id") {
            val = document.getElementById("matchday_id").value;
            document.location = url + "showuntil=" + val;
        } else if (what == "dates") {
            val = document.getElementById("dates").value;
        } else if (what == "matches") {
            val = document.getElementById("matches").value;
        }
        document.location = url + "showuntil=" + val;
    }
</script>
<?


    $cleanurl = preg_replace('/(showuntil=)[a-zA-Z0-9:_]+[&]/i', '', $link_query);
    $cleanurl = $link . $cleanurl;

    $body .= $events_test->createEventsTabs($userevents);
    $queryfield = ($events['u']['e' . $_REQUEST['ev']]['score_input_type'] == 'results') ? 'score_h' : 'score';

    ////////FILTER BEGIN
    $body .= '<div class="row"><div class="span2 offset1">';
    $body .= $lang['ranking_showrankinguntil'].'</div>';
    $body .= '<form id="filterform" class="align-middle" style="display:inline;">';
    $body .= '<select id="matchday_id" onChange="javascript: showUntil(\'' . $cleanurl . '\', \'matchday_id\')">';
    $query = "SELECT DISTINCT matchday, matchday_id
						FROM " . PFIX . "_event_" . $_REQUEST['ev'] . "
						WHERE " . $queryfield . " IS NOT NULL ORDER BY matchday_id ASC;";

    $mdids = $db->query($query);
    $body .= '<option value="none"></option>';
    $counter = 0;
    foreach ($mdids as $row) {
        $counter++;
        if ($_REQUEST['showuntil'] == 'matchday_id:' . $row['matchday_id']) {
            $type = $lang['admin_events_matchday'];
            $selected = 'selected';
            if (isset($mdids[$counter - 2]))
                $ante = '<a href="' . $cleanurl . 'showuntil=matchday_id:'
                    . $mdids[$counter - 2]['matchday_id'] . '">'
                    . $lang['general_goback'] . '</a>';
            if (isset($mdids[$counter]))
                $post = '<a href="' . $cleanurl . 'showuntil=matchday_id:'
                    . $mdids[$counter]['matchday_id'] . '">'
                    . $lang['general_goforward'] . '</a>';
        } else {
            $selected = '';
        }
        $body .= '<option value="matchday_id:' . $row['matchday_id'] . '" ' . $selected . '>' . $row['matchday'] . '</option>';
    }
    $body .= '</select>';
    $body .= ' / <b>' . $lang['general_date'] . '</b> ';
    $body .= '<select id="dates" onChange="javascript: showUntil(\'' . $cleanurl . '\', \'dates\')">';
    $body .= '<option value="none"></option>';
    $dates = $db->query("SELECT DISTINCT FROM_UNIXTIME(time, '%d.%m.%Y') AS date,
						FROM_UNIXTIME(time, '%Y%m%d') AS vdate  
						FROM " . PFIX . "_event_" . $_REQUEST['ev'] . "
						WHERE " . $queryfield . " IS NOT NULL ORDER BY time,matchday_id,matchday ASC;");
    $counter = 0;
    foreach ($dates as $row) {
        $counter++;
        if ($_REQUEST['showuntil'] == 'date:' . $row['vdate']) {
            $type = $lang['general_date'];
            $selected = 'selected';
            if (isset($dates[$counter - 2]))
                $ante = '<a href="' . $cleanurl . 'showuntil=date:'
                    . $dates[$counter - 2]['vdate'] . '">'
                    . $lang['general_goback'] . '</a>';
            if (isset($dates[$counter]))
                $post = '<a href="' . $cleanurl . 'showuntil=date:'
                    . $dates[$counter]['vdate'] . '">'
                    . $lang['general_goforward'] . '</a>';
        } else {
            $selected = '';
        }
        $body .= '<option value="date:' . $row['vdate'] . '" ' . $selected . '>' . $row['date'] . '</option>';
    }
    $body .= '</select>';
    $body .= ' / <b>' . $lang['general_match'] . '</b> ';
    $body .= '<select id="matches" onChange="javascript: showUntil(\'' . $cleanurl . '\', \'matches\')">';
    $matches = $db->query("SELECT DISTINCT id
						FROM " . PFIX . "_event_" . $_REQUEST['ev'] . "
						WHERE " . $queryfield . " IS NOT NULL ORDER BY time,matchday_id,matchday ASC;");
    $body .= '<option value="none"></option>';
    for ($i = 1; $i <= sizeof($matches); $i++) {
        if ($_REQUEST['showuntil'] == 'match:' . $i) {
            $type = $lang['general_match'];
            $selected = 'selected';
            if (isset($matches[$i - 2]))
                $ante = '<a href="' . $cleanurl . 'showuntil=match:'
                    . ($i - 1) . '">'
                    . $lang['general_goback'] . '</a>';
            if (isset($matches[$i]))
                $post = '<a href="' . $cleanurl . 'showuntil=match:'
                    . ($i + 1) . '">'
                    . $lang['general_goforward'] . '</a>';
        } else {
            $selected = '';
        }
        $body .= '<option value="match:' . $i . '" ' . $selected . '>' . $i . '</option>';
    }
    $body .= '</select>';
    $body .= '</form>';
    $body .= '</div>'; //row
    ////////FILTER END

    if (isset($ante) && isset($post))
        $steplinks = $ante . ' | ' . $post;
    else
        $steplinks = $ante . $post;
    $body .= '<br/><br/>';

    $ranking = new Ranking($thisevent);
    $until = "";

    if (isset($_REQUEST['showuntil'])) {
        //$info = rankingCalculate($_REQUEST['ev'], $_REQUEST['showuntil']);
        $until = $_REQUEST['showuntil'];
        $addtosorturl = '&showuntil=' . $_REQUEST['showuntil'];
    }
    $info = $ranking->getRankingDetails($until);


    //get info for tooltips
    //recenttips
    $query = "SELECT * FROM " . PFIX . "_event_" . $_REQUEST['ev'] . " WHERE " . $queryfield . " IS NOT NULL ORDER BY time,matchday_id,matchday ASC;";
    $rawdata = $db->query($query);
    $showrecent = 5;
    while ($showrecent-- != 0) {
        $recenttips[] = array_pop($rawdata);
    }
    //nexttips
    $query = "SELECT * FROM " . PFIX . "_event_" . $_REQUEST['ev'] . " WHERE " . $queryfield . " IS NULL ORDER BY time,matchday_id,matchday ASC;";
    $rawdata = $db->query($query);
    foreach ($rawdata as $row) {
        if (betUntil($row['time'], $_REQUEST['ev']) < time()) $nexttips[] = $row;
    }


    //event is over!
    if ($info['pastmatches'] == $info['totalmatches'])
        $thisevent->setFinished(true);

    if ($thisevent->getFinished()) {
        $over = true;
        if (!isset($_REQUEST['sort']))
            $_REQUEST['sort'] = 'provgain';
        $lang['ranking_provisorygain'] = $lang['ranking_totalgain'];
    }

    $evinfo = $events['u']['e'.$_REQUEST['ev']];
    $difftrue = ($thisevent->getPDiff() == NULL) ? false : true;
    $almosttrue = ($thisevent->getPAlmost() == NULL) ? false : true;



    //get usernames in array
    $usersraw = $db->query("SELECT id, login, picture FROM " . PFIX . "_users");
    $userarray = array();
    $users = new UserCollection($_REQUEST['ev']);
    foreach($users->getEventUsers($thisevent) as $u) {
        /* @var $u User */
        $userarray[$u->getId()] = $u->getLogin();
        $userpic = $u->getPicture();
        $picture[$u->getId()] = ($userpic == "") ? 'default-pig.jpg' : $userpic;
    }

    $cl_gain = $cl_almost = $cl_totgain = $cl_correct = $cl_diff = $cl_almost = $cl_wrong = $cl_points = 'visible-desktop';

    switch ($_REQUEST['sort']) {
        case 'gain':
            $listsource = $info['money'];
            arsort($listsource);
            $cl_gain = 'highlighted';
            break;
        case 'provgain':
            $evUsers = (explode(':', $events['u']['e' . $_REQUEST['ev']]['a']));
            array_pop($evUsers);
            foreach ($evUsers as $id) {
                $listsource[$id] = $info['jackpots'][$info['rank'][$id]] + $info['money'][$id];
            }
            arsort($listsource);
            $cl_totgain = 'highlighted';
            break;
        case 'correct':
            $listsource = $info['correct'];
            arsort($listsource);
            $cl_correct = 'highlighted';
            break;
        case 'diff':
            $listsource = $info['diff'];
            arsort($listsource);
            $cl_diff = 'highlighted';
            break;
        case 'almost':
            $listsource = $info['almost'];
            arsort($listsource);
            $cl_almost = 'highlighted';
            break;
        case 'wrong':
            $listsource = $info['wrong'];
            arsort($listsource);
            $cl_wrong = 'highlighted';
            break;
        default:
            $listsource = $info['rank'];
            asort($listsource);
            $cl_points = 'highlighted';
            $cur_rank = 0;
            foreach ($listsource as $u => $r) {
                if ($cur_rank == $info['rank'][$u]) {
                    $info['rank'][$u] = '"';
                } else {
                    $cur_rank = $info['rank'][$u];
                }
            }
            break;
    }

    

    $body .= '<script type="text/javascript">
                             function unmask(id) {
                                var obj = $(\'#\'+id);
                                obj.fadeIn();
                             }
                             function mask(id) {
                                var obj = $(\'#\'+id);
                                obj.fadeOut();
                             }
          </script>';



    $body .= '<div class="row">
                <div class="span10 offset1">';

    $body .= '<canvas id="rankingcanvas" width="480" height="200" style="border:1px solid #c3c3c3;">
	Your browser does not support the HTML5 canvas tag.
	</canvas><br/>';
    $body .= '</div></div>';
    

    if (isset($type)) $body .= '  ' . $type . ': ' . $steplinks . '<br/>';
    $body .= substitute($lang['ranking_showingxoutofx'], Array($info['pastmatches'], $info['totalmatches']));


    $body .= '<div class="tabbable tabs-top">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#rankingtext" data-toggle="tab">'.$cont->get('ranking_title').'</a></li>
                 <li><a href="#rankingplot" data-toggle="tab">Plot</a></li>
            </ul>';

    $body .= '<div class="tab-content">
                <div class="tab-pane active" id="rankingtext">';
    $body .= '<div class="accordion" id="ranking-accordion">';

    $body .= '<div class="row title">
			<div class="span1 column">' . $lang['ranking_rank'] . '</div>
			<div class="span2 column">' . $lang['general_who'] . '</div>
			<div class="span1 column"><a href="' . $link . 'sort=points' . $addtosorturl . '">' . $lang['ranking_points'] . '</a></div>';
    if ($evinfo['stake_mode'] == 'permatch') {
        $body .= '<div class="span1 column"><a href="' . $link . 'sort=gain' . $addtosorturl . '">' . $lang['ranking_gain'] . '</a></div>';
        $body .= '<div class="span1 column"><a href="' . $link . 'sort=jackpotshare' . $addtosorturl . '">' . $lang['ranking_jackpotshare'] . '</a></div>';
    }
    if ($evinfo['stake_mode'] != 'none') {
            $body .= '<div class="span1 column"><a href="' . $link . 'sort=provgain' . $addtosorturl . '">' . $lang['ranking_provisorygain'] . '</a></div>';
    }
	$body .=	'<div class="span1 column"><a href="' . $link . 'sort=correct' . $addtosorturl . '">' . $lang['ranking_correcttips'] . '</a></div>';
    //if ($difftrue) $body .= '<div class="span1 column"><a href="' . $link . 'sort=diff' . $addtosorturl . '">' . $lang['ranking_difftips'] . '</a></div>';
    //if ($almosttrue) $body .= '<div class="span1 column"><a href="' . $link . 'sort=almost' . $addtosorturl . '">' . $lang['ranking_almosttips'] . '</a></div>';
    $body .= '<div class="span1 column "><a href="' . $link . 'sort=wrong' . $addtosorturl . '">' . $lang['ranking_wrongtips'] . '</a></div>
		</div>';

    $rankids = array("-",3,2,1);
    foreach ($listsource as $u => $r) {


        /** @var $user User */
        $user = $users->getUserById($u);

        //ranking


        $rankid = ' id="rank'.array_pop($rankids).'" ';

        //making tooltip
        $userrankingdetails = '<div class="row">';
        $rts = array_reverse($recenttips);
        $userrankingdetails .= 
          '<div class="span2"> '
            . '<div class="thumb-hover"  onMouseOver="unmask(\'mask'.$u.'\')" onMouseOut="mask(\'mask'.$u.'\')">'
                . '<a href="?menu=participants&showuser='.$user->getId().'">
                        <img ' . $rankid . 'class="rankingimage" src="./data/user_img/' . $user->getPicture() . '"  />'
                        . '<div id="mask'.$u.'" class="mask"> <span> '.$user->getFullName().' </span> </div>
                   </a>
               </div>'
          . '</div>';
        $userrankingdetails .= '<div class="span3"><small>';
        foreach ($rts as $rt) {
            $userrankingdetails .= '<br/>' . $rt['home'] . ' - ' . $rt['visitor'] . ': <span class="ow_' . getResultCSSClass($evinfo, $rt['score_h'], $rt['score_v'], $rt[$u . '_h'], $rt[$u . '_v']) . '">'
                . $rt[$u . '_h'] . ':' . $rt[$u . '_v'] . '</span>';
        }

        $userrankingdetails .= '<hr width="50px" style="margin: 5px;"/>';

        if (sizeof($nexttips) != 0) {
            foreach ($nexttips as $nt)
                $userrankingdetails .= '<br/>' . $nt['home'] . ' - ' . $nt['visitor'] . ': ' . $nt[$u . '_h'] . ':' . $nt[$u . '_v'];
        } elseif ($over) {
            $userrankingdetails .= $lang['general_bettinggameover'];
        } else {
            $userrankingdetails .= $lang['ranking_waitfortips'];
        }
        $userrankingdetails .= '</small>';
        $userrankingdetails .= '</div></div>';


        //alternative rankings
        if ($_REQUEST['sort'] != 'points' &&
            isset($_REQUEST['sort']) &&
            $_REQUEST['sort'] != 'jackpotshare'
        ) {

            $rankcounter++;
            $lastval = $thisval;
            $thisval = $r;
            $secondaryrank = ($lastval == $thisval) ? '"' : $rankcounter;
        }
        $rankrepresentation = (isset($secondaryrank)) ?
            '<b>' . $secondaryrank . '</b>   (' . $info['rank'][$u] . ')' :
            '<b>' . $info['rank'][$u] . '</b>';
        unset($secondaryrank);

        $body .= '<div class="accordion-group">
	            <div class="accordion-heading">';
        //the ranking table

        //href=
        $body .= '
  		  <a class="accordeon-toggle" data-toggle="collapse" data-parent="#ranking-accordeon" href="#collapse' . $u . '"
  		    style="text-decoration:none; color:black">
          <div class="row">
				<div class=" span1"> Rang ' . $rankrepresentation . '	</div>';
        $body .= '<div class="span2" ><b>'. $userarray[$u] .'</b></div>';
        $body .= '<div class=" span1 ' . $cl_points . '">' . $info['points'][$u] . '</div>';
        if ($info['rank'][$u] != '"') {
            $thisranksjackpot = $info['jackpots'][$info['rank'][$u]];
            if ($thisranksjackpot == '') {
                $thisranksjackpot = '-';
            }
        }
        if ($evinfo['stake_mode'] == 'permatch') {
            $body .= '<div class=" span1 ' . $cl_gain . '">' . $info['money'][$u] . '</div>';
            $body .= '<div class=" span1">' . $thisranksjackpot . '</div>';
        }
        if ($evinfo['stake_mode'] != 'none') {
            $body .= '<div class=" span1 ' . $cl_totgain . '">' . ($thisranksjackpot + $info['money'][$u]) . '</div>';
        }
        $body .= '<div class=" span1 ' . $cl_correct . '">' . $info['correct'][$u] . '</div>';
        //if ($difftrue) $body .= '<div class=" span1 ' . $cl_diff . '">' . $info['diff'][$u] . '</div>';
        //if ($almosttrue) $body .= '<div class=" span1 ' . $cl_almost . '">' . $info['almost'][$u] . '</div>';
        $body .= '<div class=" span1 ' . $cl_wrong . ' ">' . $info['wrong'][$u] . '</div>';

        $body .= '</div></a></div>';

        $body .= '<div id = "collapse' . $u . '" class="accordion-body in collapse">';
        $body .= $userrankingdetails;
        $body .= '</div>';
        $body .= '</div><!-- collapsable group close -->';

    }

    //summary of points and money

    //even if multiple users are on the same rank
    //there is only one entry for the rank in $info['jackpots']
    //=> throw the missing money in!
    foreach ($info['jackpots'] as $r => $s) {
        if ($info['r_quant'][$r] > 1) $info['jackpots'][$r . '_missing'] = ($info['r_quant'][$r] - 1) * $s;
    }

    $body .= '<div class="row ow_summary">
			<div class="span3">' . $lang['overview_summary'] . '</div>';
    if ($evinfo['stake_mode'] == 'permatch') {

        $jackpotsum = array_sum($info['jackpots']);
        if ($thisevent->getExtraStake()>0) {
            $extrastake = $thisevent->getExtraStake()*sizeof($users->getEventUsers($thisevent));
            $jackpotsum = $jackpotsum.' ('.($jackpotsum-$extrastake).' + '.$extrastake.')';
        }


        $body .= '<div class="span1">' . array_sum($info['money']) . '</div>
				<div class="span1">' .$jackpotsum . '</div>';
    }
    $body .= '<div class="span1">' . (array_sum($info['money']) + array_sum($info['jackpots'])) . '</div>
			<div class="span1">' . array_sum($info['correct']) . '</div>';
    $body .= '<div class="span1">' . array_sum($info['wrong']) . '</div>';
    $body .= '</div>';


    $body .= '</div></div>
                <div class="tab-pane" id="rankingplot">';

    $plotter = new Plotter($thisevent);
    $rankOrder = array_keys($ranking->getRanking($until));
    $langDict = array('points' => $cont->get('ranking_points'));


    $body .= "<div class='row'><div class='span7'>
            <span id='chartPointsInfo'>click chart</span>
            <div id='chartPoints'></div></div></div>";


    $body .= "
        <div id='chartPointsScript'>
        <script type=\"text/javascript\">
        function paintRankingPlot() {
            ".$plotter->rankingBarPlot($rankOrder,'chartPoints',$langDict,$until)."
            }
        </script>
        </div>";


    $body .=  '</div></div></div>';


//*simulation*/
     $body .= '
	<script>

$("a[data-toggle=\"tab\"]").on("shown", function (e) {
    if(e.target.href.indexOf("#rankingplot")>-1) {
        paintRankingPlot();
    }
})


	var c=document.getElementById("rankingcanvas");
	var ctx=c.getContext("2d");
	ctx.fillStyle="#000000";
	ctx.fillRect(10,140,150,60);
	ctx.fillRect(160,100,150,100);
	ctx.fillRect(310,160,150,40);


	ctx.fillStyle="#ffffff";
	ctx.font="30px Arial";
	ctx.fillText("2",70,180);
	ctx.fillText("1",220,160);
	ctx.fillText("3",370,190);

        var img2 = new Image();
	src = document.getElementById("rank2").src;
	img2.src = src.split(".jpg")[0] + "@thumb.jpg";
        img2.onload = function() {
		ctx.drawImage(img2,40,60);
	        ctx.fillStyle="#ffffff";
	        ctx.font="30px Arial";
	        ctx.fillText("2",70,180);
        }

        var img1 = new Image();
	src = document.getElementById("rank1").src;
	img1.src = src.split(".jpg")[0] + "@thumb.jpg";
        img1.onload = function() {
		ctx.drawImage(img1,190,20);
	        ctx.fillStyle="#ffffff";
	        ctx.font="30px Arial";
	        ctx.fillText("1",220,160);
        }

        var img3 = new Image();
	src = document.getElementById("rank3").src;
	img3.src = src.split(".jpg")[0] + "@thumb.jpg";
        img3.onload = function() {
		ctx.drawImage(img3,320,80);
	        ctx.fillStyle="#ffffff";
	        ctx.font="30px Arial";
	        ctx.fillText("3",370,190);
        }
        
	</script>';
     
}
?>
