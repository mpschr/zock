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


$body .= '<h2>'.$lang['mytips_title'].'</h2>';





global $db, $settings, $events, $events_test, $body;



//========== show matches & results

$ids='';

//event handling ;) => estimate if user is registerd to events

$userevents = $events_test->getUserEvents();
$nb = sizeof($userevents);
/* @var $thisevent Event */
$thisevent =  null;

if($nb < 1){
	//no event
	$body .= $lang['mytips_participatefirst'];
	
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
//$_REQUEST['ev'] overrules $_SESSION['currevent']
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent->getId();
//update the current event variable in Session
$_SESSION['currevent'] = $_REQUEST['ev'];


//when a curious user modiefies the url...
if(!userParticipates($_REQUEST['ev']) && $nb > 0){
	//if the user is registered to an event and tries to view the comments of another event
	errorPage('notinevent');
	exit;
}

$body .= $events_test->createEventsTabs($userevents);

$evdat = $events['u']['e'.$_REQUEST['ev']];


if($nb >= 1 && !(isset($_REQUEST['mtac']))){
	//show all of it!

	// error handling....
	if (isset($_SESSION['err'])){
		$err = $_SESSION['err'];
		unset($_SESSION['err']);
		$data = $_SESSION['post'];
		unset($_SESSION['post']);
		$body .= '<p />'.errorMsg('filledform');
		foreach ($err as $id){
			$wrongs[$id] = 'error';
		}
	}

    //get the info by what it content should be ordered
    $orderby = (isset($_REQUEST['orderby'])) ? explode(':', $_REQUEST['orderby']) : explode(':', 'dueDate:SORT_ASC');

	//filtering
	if (isset($_REQUEST['filter'])){
		$f = preg_split('/:/', $_REQUEST['filter']);
		switch ($f[0]){
			case 'team':
				$f_team = 'selected';
				break;
			case 'home';
				$f_home = 'selected';
				break;
			case 'visitor';
				$f_visitor = 'selected';
				break;
			case 'matchday';
				$f_matchday = 'selected';
				break;
		}
	}

    $event = $events_test->getEventById($_REQUEST['ev']);
    $bdp_matches = $event->getBetsContainer()->getBets($_REQUEST['filter'],implode(':',$orderby));
    $body .= '';
    $bdp_rows =  sizeof($bdp_matches);


    // decide how and which badges to use for point factors
    $pointsFactor = $event->getPointFactorsArray();
    $badgemap = array(1 => "");
    $badgeoptions = array("badge",
                            "badge badge-success",
                            "badge badge-info",
                            "badge badge-warning",
                            "badge badge-important",
                            "badge badge-inverse");

    if (sizeof($pointsFactor)==1) {
        $badgemap[$pointsFactor[0]] = "";
    } else {
        for ($i = 0; $i<  sizeof($pointsFactor); $i++) {
            $badgemap[$pointsFactor[$i+1]] = $badgeoptions[$i % 6];
        }
    }

	//$mnb stands for Match NumBer, is necessary to limit the amount of matches displayed
	$mnb = (isset($_REQUEST['mnb'])) ? $_REQUEST['mnb'] : 1;

	if($bdp_matches == NULL && !isset($_REQUEST['filter'])){

		//well, there aren't any matches
		$body .= $lang['general_nomatches'];
		$body .= ' ('.$events['u']['e'.$_REQUEST['ev']]['name'].')';
	}else{
		if($bdp_rows == 0 && isset($_REQUEST['filter'])){
			//no results with this filter
			$body .= errorMsg('filter_emptyresults');
		}



		//filterform
		$filterurl = preg_replace('/(filter=)[a-zA-Z0-9:]+[&]/i', '', $link_query); 
		$filterurl = $link.$filterurl;
		$body .= '<form action="javascript: filter(\''.$filterurl.'\')">
			<a href="javascript: showFilter()" >'.$lang['general_filter'].'</a>
			<div id="filterform" class="notvisible" >
				<select id="filter_on" onChange="filterChange()">
					<option value="nofilter"></option>
					<option value="team" '.$f_team.'>'.$lang['general_team'].'</option>
					<option value="home" '.$f_home.'>'.$lang['admin_events_home'].'</option>
					<option value="visitor" '.$f_visitor.'>'.$lang['admin_events_visitor'].'</option>
					<option value="matchday" '.$f_matchday.'>'.$lang['admin_events_matchday'].'</option>
				</select>';
				$body .= ' <span id="filter_contains">'.$lang['general_contains'].'</span> ';
				$body .= ' <span id="filter_is" class="notvisible">'.$lang['general_is'].'</span> ';
				$body .= '<input id="filter_this" value="'.$f[1].'" size="15"/>';
				$body .= '<a href="javascript: filterUnset()"> x </a>';
				$body .= ' <input type="submit" value="'.$lang['general_filterverb'].'"/>';
			$body .= '</div>';	
		$body .= '</form>';

		//the form (begins already here)
		$body .= '<form action="'.$link.'mtac=savetips" method="POST" name="matches">';
		
		$tipplus = '( 1 /';
		if(!($evdat['ko_matches']=='only' && $evdat['enable_tie']=='no')){
			$tipplus .= ' X /';
			$colspan = 3;
		}else{
			$colspan = 2;
		}
		$tipplus .= ' 2 )';

		//content
        $MATCHHEADER .= '<table id="tipstable" class="showmatches  table table-striped">';
		$MATCHHEADER .= '<tr class="title">
			<td class=" visible-desktop"><a href="'.$link.orderBy('dueDate', $orderby, $link_query).'"> '.$lang['mytips_betcloses'].'</a></td>
			<td class="visible-desktop">'.$lang['admin_events_time'].'</td>
			<td class="visible-desktop"><a href="'.$link.orderBy('matchDay', $orderby, $link_query).'"> '.$lang['admin_events_matchday'].'</a></td>
			<td class="visible-desktop" span="2">
			    <div  class="span1 visible-desktop"><a href="'.$link.orderBy('home', $orderby, $link_query).'"> '.$lang['admin_events_home'].'</a></div>
			    <div class="span1 visible-desktop"><a href="'.$link.orderBy('visitor', $orderby, $link_query).'"> '.$lang['admin_events_visitor'].'</a></div>
			</td>
			<td class="visible-desktop">'.$lang['admin_events_score'].'</td>';
			if($evdat['bet_on']=='results'){
				$MATCHHEADER .= '<td class="visible-desktop">'.$lang['mytips_tip'].'</td>';
			}else{
				$MATCHHEADER .='<td class="visible-desktop" colspan="'.$colspan.'">'.$lang['mytips_tip'].' '.$tipplus.'</td>';
			}
			$MATCHHEADER .= '<td class="visible-desktop">'.$lang['mytips_sametip'].'</td>
			<td class="visible-desktop">'.$lang['mytips_tendency'].'</td>
			<td class="visible-desktop">Extra</td>
			<td class="visible-desktop"></td>
			</tr>';

        $QUESTIONHEADER .= '<table class="showmatches">';
        $QUESTIONHEADER .=  '<tr class=title>
			<td class=title><a href="'.$link.orderBy('time', $orderby, $link_query).'"> '.$lang['mytips_betcloses'].'</a></td>
			<td class=title > Question</td>
			<td class=title>'.$lang['admin_events_score'].'</td>
			<td class=title>'.$lang['mytips_tip'].'</td>';

        $QUESTIONHEADER .= '<td class=title>'.$lang['mytips_sametip'].'</td>
			<td></td>
			</tr>';

        $MATCHESSTRING ='';

        //estimate page to display if nothing else specified


		if (!isset($_REQUEST['orderby']) && !isset($_REQUEST['mnb'])){
			$closestGame = closestGame($_REQUEST['ev'], time());
            //$closestGame = closestGame($_REQUEST['ev'], time()+abs(betUntil(0, $_REQUEST['ev'])));

            $page = ($closestGame%$settings['formlines'] == 0)  ?
				$closestGame/$settings['formlines'] - 1  : 
				floor($closestGame/$settings['formlines']);
			$mnb = $page * $settings['formlines'] + 1;
		}

		//if filter is set, watch out that mnb is not too high
		if($bdp_rows < $mnb) $mnb = 1; 	

		//foreach($bdp_matches as $nb => $m){
        $nb = -1;
        foreach ($bdp_matches as $bet) {
            /* @var $bet Bet */
            $nb++;
			$start = $mnb;
			$limit = $mnb + $settings['formlines'];

            //still editable or not??
            $betuntil = $bet->getDueDate();
            $now = time();
            $disabled = "";
            if ($betuntil<$now){
                //no, not editable
                $robool = "true";
                $ro = 'class="readonly" readonly="readonly"';
            }else{
                //yes, it is
                $robool = "false";
                $ro = 'class=""';
                $disabled = 'class=""';
            }

			
			if ($nb+1 >= $start && $nb+1 < $limit){

				$ids .= $bet->getId().':';
				$id = $bet->getId();
                $betid = $bet->getId();

                //further error handling
				//=>decide if the data in the forms should come from db or error the $_post array

                if ($bet instanceof Question) {

                    /* @var $bet Question */
                    $userbet = $bet->getBet($_SESSION['userid']);
                    $pointing = preg_split('/:/',$bet->getPoints());
                    $options = sizeof($pointing)-1;
                    $possibilities = preg_split('/:/',$bet->getPossibilities());
                    $autocomplete = "";
                    $sameBet = $bet->getSameBets($bet->getBet($_SESSION['userid']));
                    foreach ($possibilities as $poss) {
                        if ($poss == '')
                            continue;
                        $autocomplete .= '"'.$poss.'", ';
                    }

                    $c = 0;
                    $betinput = '';
                    foreach ($pointing as $point) {
                        if ($point == '')
                            continue;
                        $inputid = 'bet_'.$betid.'_'.$c;
                        $nr = $c+1;
                        $betinput .= '<nobr>'.$lang['mytips_tip'].' '.$nr.' ('.$point.' '.$lang['ranking_points'].'):
                                        <input id="'.$inputid.'"  '.$ro.' value="'.$userbet[$c].'" size="10" >';
                        $betinput .= "</nobr>
                            <script type=\"text/javascript\">
                                $(document).ready(function() {
                                    $(\"#$inputid\").autocomplete({
                                        source: [".$autocomplete."]
                                    });
                                $( \"#$inputid\" ).autocomplete({
                                   select: function(event, ui) {
                                        savequestion('$betid',$options,ui.item.value,$c); }
                                });
                            });

                             </script>
                             ";

                        $c++;
                    }

                    $res = '';
                    if($bet->getResult != "") {
                    foreach($bet->getResult() as $r) {
                        if ($res != '') $comma = ", ";
                        $res .= $comma.$r;
                    }
                    }


                    $QUESTIONTABLE .= '
                        <tr>
                            <td id="remaining_'.$betid.'">'.$bet->getRemainingTime().'</td>
                            <td >'.$bet->getQuestion().'</td>
                            <td>  '. $res .'  </td>
                            <td>'.$betinput.'</td>
                            <td id = "samebet_'.$betid.'">'.$sameBet.'</td>
                            <td id = "savestatus_'.$betid.'"></td>

                        </tr>';



                    continue;
                }



                $userbet = $bet->getBet($_SESSION['userid']);
                $dummy = preg_split('/ : /',$userbet);
                $score_h = $dummy[0];
                $score_v = $dummy[1];
                $checked[$m[$_SESSION['userid'].'_toto']] = 'checked="checked"';
                $toto = $m[$_SESSION['userid'].'_toto'];


                $sameBet = $bet->getSameBets($userbet);
                $tendency = $bet->getTendency();
                $matchday = $bet->getMatchday();
                $remainingTime = timeOrMatchDetails($bet);
                $pointsFactor = "";
                if ($bet instanceof Match) {
                    if ($bet->getPointsFactor() != 1) {
                        $f = (float) $bet->getPointsFactor();
                        $pointsFactor = '<span class="'.$badgemap[$f].'">'.$f.'x!</span>';
                    }
                }

				// same tips?

                $matchtime = $bet->getTime();
                $home = $bet->getHome();
                $visitor = $bet->getVisitor();

                $lastgamesH = $event->getBetsContainer()->getBets('team:'.$home.':withresult:','dueDate:SORT_DESC');
                $lastgamesV = $event->getBetsContainer()->getBets('team:'.$visitor.':withresult:','dueDate:SORT_DESC');

                $lastGamesHome = '';
                foreach ($lastgamesH as $lastmatch) {
                    if (!$lastmatch instanceof Match)
                        continue;
                    /* @var $lastmatch Match */
                    $athome = $lastmatch->getHome() == $home;
                    $lastGamesHome .= $athome ? '<b>'.$lastmatch->getResult() : '<b>'.$lastmatch->getInverseResult();
                    $lastGamesHome .= '</b> vs ';
                    $lastGamesHome .= $athome ? $lastmatch->getVisitor() : $lastmatch->getHome();
                    $lastGamesHome .= '<span class=\'greyedout\'>  '.$lastmatch->getMatchday().'</span>';
                    $lastGamesHome .= '<br/>';
                }

                $lastGamesVisitor = '';
                foreach ($lastgamesV as $lastmatch) {
                    if (!$lastmatch instanceof Match)
                        continue;
                    /* @var $lastmatch Match */
                    $athome = $lastmatch->getHome() == $visitor;
                    $lastGamesVisitor .= $athome ? '<b>'.$lastmatch->getResult() : '<b>'.$lastmatch->getInverseResult();
                    $lastGamesVisitor .= '</b> vs ';
                    $lastGamesVisitor .= $athome ? $lastmatch->getVisitor() : $lastmatch->getHome();
                    $lastGamesVisitor .= '<span class=\'greyedout\'>  '.$lastmatch->getMatchday().'</span>';
                    $lastGamesVisitor .= '<br/>';
                }


                $time1 = date('d.m.Y', $matchtime);
				$time2 = date('H:i', $matchtime);
				$result = $bet->getResult();

				//the form can continue here
				$MATCHESSTRING .= '<tr>
				    <td class="input" id="remains_'.$betid.'">'.$remainingTime.'</td>
					<td class="input  visible-desktop">'.weekday($matchtime,1).', '.$time1.' <br/>'.$lang['general_time_at'].' '.$time2.'</td>
					<td class="input  visible-desktop">'.$matchday.'</td>
					<td class="input" span="2">
					 <div class="span1" rel="popover" data-original-title="lastgames" data-content="'.$lastGamesHome.'">'.$home.'</div>
					 <div class="span"><b>-</b></div>
					 <div class="span1" rel="popover" data-original-title="lastgames" data-content="'.$lastGamesVisitor.'">'.$visitor.'<div>
					</td>
					<td class="input  visible-desktop">'.$result.'</td>';

					if($evdat['bet_on']=='results'){
						$MATCHESSTRING .= '<td>
						                    <div class="span1">
                                                 <input type="number" class="input-mini"
                                                    style="height:25px;"
                                                    size="2"
                                                    id="h_'.$betid.'"
                                                    '.$ro.'
                                                    name="score_h_'.$betid.'"
                                                    value="'.$score_h.'"
                                                    oninput="savebet(event,\''.$betid.'\')"
                                                >
                                            </div>
						                    <div class="span1">
                                                <input type="number" class="input-mini"
                                                    style="height:25px;"
                                                    size="2"
                                                    id="v_'.$betid.'"
                                                    '.$ro.'
                                                    name="score_v_'.$bet->getId().'"
                                                    value="'.$score_v.'"
                                                    oninput="savebet(event,\''.$betid.'\')"
                                                  >
                                              </div>
                                            </td>';

					}elseif($evdat['bet_on']=='toto'){
						if($robool=='true'){
							$MATCHESSTRING .= '<td class="input" colspan="'.$colspan.'">'.$toto.'</td>';
						}else{
							$MATCHESSTRING .= '<td class="input">';
								$MATCHESSTRING .= '<input class="'.$disabled.'" type="radio" value="1" '.$checked['1'].' name="toto_'.$bet->getId().'">';
							$MATCHESSTRING .= '</td>';
							if(!($evdat['ko_matches']=='only' && $evdat['enable_tie']=='no')){
								$MATCHESSTRING .= '<td class="input">';
									if($m['komatch'] && $evdat['enable_tie']!='yes')
										$MATCHESSTRING .= '--';
									else
										$MATCHESSTRING .= '<input class="'.$disabled.'" type="radio" value="3" '.$checked['3'].' name="toto_'.$bet->getId().'">';
								$MATCHESSTRING .= '</td>';
							}
							$MATCHESSTRING .= '<td class="input">';
								$MATCHESSTRING .= '<input class="'.$disabled.'" type="radio" value="2" '.$checked['2'].' name="toto_'.$bet->getId().'">';
							$MATCHESSTRING .= '</td>';
						}
					}
					$MATCHESSTRING .= '<td class="input" id="samebet_'.$betid.'">'.$sameBet.'</td>
					                    <td class="input  hidden-phone"  id="tendency_'.$betid.'"">'.$tendency.'</td>
					                    <td>'.$pointsFactor.'</td>
					                    <td id="savestatus_'.$betid.'"></td>
					                    </tr>';
				$MATCHESSTRING .= '<input id="ro_'.$bet->getId().'" name="ro_'.$bet->getId().'" type="hidden" value="'.$robool.'">';
				$MATCHESSTRING .= '<input id="komatch_'.$bet->getId().'" name="komatch_'.$bet->getId().'" type="hidden" value="'.$m['komatch'].'">';
				unset($checked);
			}
		}

        if (isset($QUESTIONTABLE)) {
            $body .= $QUESTIONHEADER.$QUESTIONTABLE.'</table>';
        }

        if (isset($MATCHESSTRING)) {
            $body .= $MATCHHEADER.$MATCHESSTRING.'</table>';
        }


        $xajax -> register(XAJAX_FUNCTION, 'checkmatches');
        $xajax -> register(XAJAX_FUNCTION, 'savebet');

       // $xajax->configure('debug',true);

        $xajax->processRequest();
        $xajax->printJavascript();


        $body .= '<input name="query" type="hidden" value="'.$link_query.'">';
		$body .= '<input name="event" type="hidden" value="'.$_REQUEST['ev'].'">';
		$body .= '<input name="ids" type="hidden" value="'.$ids.'">';
		$body .= '<input name="mnb" type="hidden" value="'.$mnb.'">';
		$body .= '<input name="orderby" type="hidden" value="'.$_REQUEST['orderby'].'"';
		//$body .= '</table>';
		//the form finishes here
		$body .= '</form>';
		$body .= '<p />';



		//skip pages
		if (!(isset($err))){

            $queryfilter = preg_replace( '/mnb=([0-9]+)([& ])/', '', $link_query);


            $body .= '<div id="pager" class="pagination pagination-centered">';
            $body .= '<ul>';
            if($mnb > 1){
				$gonb = $mnb-$settings['formlines'];
				if ($gonb < 1) $gonb = 1;
                $body .= '
                        <li class="previous">
                            <a href="'.$link.$queryfilter.'mnb='.$gonb.'">'.$lang['general_goback'].'</a>
                        </li>
                        ';
				//$body .= '<a href="'.$link.$queryfilter.'mnb='.$gonb.'">'.$lang['general_goback'].'</a> | ';
			}

            //$body .= '<li class="disabled"><a href="#pager">'.$lang['general_page'].'</a></li>';
            $y = 0;
			for($x=1 ; $x <= $bdp_rows; $x += $settings['formlines']){
                $y++;
                $activeclass = ($x==$mnb) ? 'class="active"' : '';
					$body .= '  <li '.$activeclass.'><a href="'.$link.$queryfilter.'mnb='.$x.'">'.$y.'</a></li>';
			}


			if($mnb + $settings['formlines'] < $bdp_rows){
				$gonb = $mnb+$settings['formlines'];
				if ($gonb > $bdp_rows) $gonb = $bdp_rows;
                $body .= '
                        <li class="next">
                            <a href="'.$link.$queryfilter.'mnb='.$gonb.'">'.$lang['general_goforward'].'</a>
                        </li>
                        ';
                $body .= '</ul></div><!--close pagination-->';

            }
		}
	}

//========== save tips
}elseif($_REQUEST['mtac'] == 'savetips'){

	$body .= $lang['general_updating'].'<br>'.$lang['general_redirect'];

	//make array with ids to update
	$idar = explode(':', $_POST['ids']);
	$ok = Array();
	$err = Array();	
	foreach($idar as $id){
		
		//if it wasn't editable, it's not worth updating it
		if ($_POST['ro_'.$id] == "false"){
			
			if($evdat['bet_on']=='results'){
			

				//pepare for check => delicate with NULL & zero
				if ($_POST['score_h_'.$id] == "") $_POST['score_h_'.$id] = "NULL";
				if ($_POST['score_v_'.$id] == "") $_POST['score_v_'.$id] = "NULL";


				//check if the entries were correct
				if ( ( $_POST['score_h_'.$id] == "NULL" && $_POST['score_v_'.$id] == "NULL" )
						|| ( is_numeric($_POST['score_h_'.$id]) && is_numeric($_POST['score_v_'.$id]) ) ){
					$ok[] = $id;
				}else{
					$err[] = $id;
				}
			}elseif($evdat['bet_on']=='toto'){
				if ($_POST['toto_'.$id] == "") $_POST['toto_'.$id] = "NULL";
				$ok[] = $id;
			}
			
		}
	}
	
	if (isset($err) && sizeof($err)>0){
		$_SESSION['err'] = $err;
		$_SESSION['post'] = $_POST;
		//go back without updating but with a lot of information
		redirect( preg_replace('/(&mtac=savetips)/', '',$rlink.$link_query.$_POST['query']), 0);

	}else{
		//update	
		foreach($ok as $x){
			
			if($evdat['bet_on']=='results'){
				//no apostrophes for scores, because  'NULL' => 0
				$query_changes = " UPDATE ".PFIX."_event_".$_POST['event']."
							SET ".$_SESSION['userid']."_h = ".$_POST['score_h_'.$x].",
							".$_SESSION['userid']."_v = ".$_POST['score_v_'.$x]."
							WHERE id = '".$x."';";
			}elseif($evdat['bet_on']=='toto'){
				//no apostrophes for scores, because  'NULL' => 0
				$query_changes = " UPDATE ".PFIX."_event_".$_POST['event']."
							SET ".$_SESSION['userid']."_toto = ".$_POST['toto_'.$x]."
							WHERE id = '".$x."';";
				
			}
			if($db->query($query_changes)){
				$body .= $lang['general_savedok'];
				redirect( preg_replace('/(&mtac=savetips)/', '',$rlink.$link_query.$_POST['query']), 0);
			}else{
				redirect( preg_replace('/(&mtac=savetips)/', '',$rlink.$link_query.$_POST['query']), 1);
			}
		}
		
	}


}

//========== xajax


$body .= '<script type="text/javascript" charset="UTF-8">
            /* <![CDATA[ */
            function load_xajax() {
                setTimeout("setInterval(\\"xajax_checkmatches(\''.$ids.'\')\\",5000)",1000);
           }

           function savequestion(betid,options,newvalue,index) {

                  var img = "<img src=\"src/style_'.$settings['style'].'/img/icon_loading_whitebg.gif\" height=\'20px\' width = \'20px\' />"
                  document.getElementById("savestatus_"+betid).innerHTML=img;

                var bet = "";

                for (var i = 0; i < options; i++) {
                    chosen = "";
                    if (i==index) {
                        chosen = newvalue;
                    } else {
                        chosen = document.getElementById("bet_" + betid + "_" + i).value;
                    }

                    bet = bet + chosen +  ":" ;
                }
                xajax_savebet(betid,bet);
           }


           function savebet(event,id) {

                  evt = event || window.event;
                  var keyPressed = evt.which || evt.keyCode;

                  home = document.getElementById("h_"+id).value;
                  visitor = document.getElementById("v_"+id).value;

                  if (home == "" || visitor == "" || keyPressed == 9) {
                        return;
                  }

                  var img = "<img src=\"src/style_'.$settings['style'].'/img/icon_loading_whitebg.gif\" height=\'20px\' width = \'20px\' />"
                  document.getElementById("savestatus_"+id).innerHTML=img;

                  var bet = "";
                  bet = home  + ":" + visitor;

                  xajax_savebet(id,bet)
           }

           /* ]]> */
           </script>';

function timeOrMatchDetails($bet){
    /* @var $bet Bet */
    $remainingTime = $bet->getRemainingTime();
    if ($remainingTime == "-") {
        return '<a class="btn btn-small" href="index.php?menu=overview&row='.preg_replace('/\D/', '', $bet->getId()).'">
            <i class="icon-info-sign"></i>
          </a>';
    } else {
        return $remainingTime;
    }   
}

function  checkmatches($idsstring) {
    global $events_test;
    $response = new xajaxResponse();
    $ids = preg_split('/:/',$idsstring);
    foreach ($ids as $id) {

        if ($id=='')
            continue;
        $event = $events_test->getEventById($_REQUEST['ev']);
        $bet= $event->getBetById($id);


        /** @var $bet Bet */
        $response->assign('remains_'.$id,'innerHTML', timeOrMatchDetails($bet));
        $response->assign('samebet_'.$id,'innerHTML', $bet->getSameBets($bet->getBet($_SESSION['userid'])));
        $response->assign('tendency_'.$id,'innerHTML', $bet->getTendency());

    }
    return $response;
}

function savebet($id,$userbet) {
    global $events_test,$settings;
    $response = new xajaxResponse();
    $event = $events_test->getEventById($_REQUEST['ev']);

    $bet = $event->getBetById($id);
    $bool = $bet->setBet($_SESSION['userid'],$userbet);

    $src = '';
    if ($bool) {
        $src = 'src/style_'.$settings['style'].'/img/icon_ok.png';
        $response->script('xajax_checkmatches("'.$id.'");');
    } else {
       $src = 'src/style_'.$settings['style'].'/img/icon_not_ok.png';

    }
    $image = " <img src=".$src." width = '20px' height= '20px' />";
    $response->assign('savestatus_'.$id,'innerHTML', $image);
    return $response;

}


?>

