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

$body .=  '<h2>' . $lang['overview_title'] . '</h2>';

global $db, $settings, $events, $events_test, $cont;


$viewableEvents = $events_test->onlyPublicEvents($events_test->getActiveEvents());
$nb = sizeof($viewableEvents);

if ($_SESSION['logged']) {
    $viewableEvents = $events_test->getUserEvents();
    $nb = sizeof($viewableEvents);
}

$event = null;

if ($nb < 1) {
    //no events
    $body .=  $lang['overview_noevents'];

} elseif ($nb == 1) {
    //one event
    $event = $viewableEvents[0];

} elseif ($nb > 1) {
    //multible events

    //the session variable currevent must either a public event or the user participates. It can be in the session
    //after having looked at a public event in the overview section

    $requestedE = $events_test->getEventById($_REQUEST['ev']);

    if ($requestedE != null ) {
        $event = $requestedE;
        $body .= 'set event to requested!'.$event->getName();
    } else {
        if (isset($_SESSION['currevent'])) {
            $event = $events_test->getEventById($_SESSION['currevent']);
        } else {
            $event = $viewableEvents[0];
        }
    }
}
//$_REQUEST['ev'] overrules the insight of the event handling :)
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $event->getId();
//update the current event variable in Session
if (eventIsPublic($_REQUEST['ev']) || $event->userIsApproved($_SESSION['userid'])) {


    $_SESSION['currevent'] = $_REQUEST['ev'];

    $usersC = new UserCollection();
    $users = $usersC->getEventUsers($event);


    if ($nb >= 1) {
        //show all of it
        if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $event->getId();



        //filtering
        if (isset($_REQUEST['filter'])) {
            $filter = " WHERE ";
            $f = preg_split('/:/', $_REQUEST['filter']);
            switch ($f[0]) {
                case 'team':
                    $filter .= "`home` LIKE '%" . $f[1] . "%' OR `visitor` LIKE '%" . $f[1] . "%'";
                    $f_team = 'selected';
                    break;
                case 'home';
                    $filter .= "`home` LIKE '%" . $f[1] . "%'";
                    $f_home = 'selected';
                    break;
                case 'visitor';
                    $filter .= "`visitor` LIKE '%" . $f[1] . "%'";
                    $f_visitor = 'selected';
                    break;
                case 'matchday';
                    $filter .= "`matchday` LIKE '" . $f[1] . "'";
                    $f_matchday = 'selected';
                    break;
            }
        }

        //get all the data!

        $bets = $event->getBetsContainer()->getBets($filter='',$orderby);
        $rows = sizeof($bets);

        $orderby = (isset($_REQUEST['orderby'])) ? explode(':', $_REQUEST['orderby']) : explode(':', 'dueDate:SORT_ASC');
        //which users participate in this event? => get their names

        //$mnb stands for Match NumBer, is necessary to limit the amount of matches displayed (not yet implemented in overview)
        $mnb = (isset($_REQUEST['mnb'])) ? $_REQUEST['mnb'] : 1;

        if ($bets == NULL && !isset($_REQUEST['filter'])) {

            //there are no matches
            $body .=  $lang['general_nomatches'];
            $body .=  ' (' . $event->getName() . ')';

        } else {
            if ($rows == 0 && isset($_REQUEST['filter'])) {
                //no results with this filter
                $body .=  errorMsg('filter_emptyresults');
            }

            $body .=  $lang['overview_content'];
            $body .= $events_test->createEventsTabs($viewableEvents);

            //filterform
            $filterurl = preg_replace('/(filter=)[a-zA-Z0-9:]+[&]/', '', $link_query);
            $filterurl = $link . $filterurl;
            $body .=  '<form action="javascript: filter(\'' . $filterurl . '\')">
			<a href="javascript: showFilter()" >' . $lang['general_filter'] . '</a>
			<div id="filterform" class="notvisible" >
				<select id="filter_on" onChange="filterChange()">
					<option value="nofilter"></option>
					<option value="team" ' . $f_team . '>' . $lang['general_team'] . '</option>
					<option value="home" ' . $f_home . '>' . $lang['admin_events_home'] . '</option>
					<option value="visitor" ' . $f_visitor . '>' . $lang['admin_events_visitor'] . '</option>
					<option value="matchday" ' . $f_matchday . '>' . $lang['admin_events_matchday'] . '</option>
				</select>';
            $body .=  ' <span id="filter_contains">' . $lang['general_contains'] . '</span> ';
            $body .=  ' <span id="filter_is" class="notvisible">' . $lang['general_is'] . '</span> ';
            $body .=  '<input id="filter_this" value="' . $f[1] . '" size="15"/>';
            $body .=  '<a href="javascript: filterUnset()"> x </a>';
            $body .=  ' <input type="submit" value="' . $lang['general_filterverb'] . '"/>';
            $body .=  '</div>';
            $body .=  '</form>';


            //user2column
            if (isset($_REQUEST['u'])) $_REQUEST['col'] = user2column($_REQUEST['u'], $_REQUEST['ev']);


            $body .=  '<div id="overview"><table>';

            $rowview = false;
            if (isset($_REQUEST['row'])) {
                $rowview = true;
            }

            //title row of the table
            $body .=  '<tr class=title>';
            if (!$rowview) {
			$body .= '<td class=title><a href="' . $link . orderBy('time', $orderby, $link_query) . '"> ' . $lang['admin_events_time'] . '</a></td>
			<td class=title><a href="' . $link . orderBy('matchday', $orderby, $link_query) . '"> ' . $lang['admin_events_matchday'] . '</a></td>
			<td class=title><a href="' . $link . orderBy('home', $orderby, $link_query) . '"> ' . $lang['admin_events_home'] . '</a></td>
			<td class=title><a href="' . $link . orderBy('visitor', $orderby, $link_query) . '"> ' . $lang['admin_events_visitor'] . '</a></td>';
            if ($event->getScoreInputType() == 'results')
                $body .=  '<td class=title><a href="' . $link . orderIt('score_h', $orderby, $link_query) . '"> ' . $lang['admin_events_score'] . '</a></td>';
            else
                $body .=  '<td class=title><a href="' . $link . orderIt('score', $orderby, $link_query) . '"> ' . $lang['admin_events_score'] . '</a></td>';
            if ($event->getStakeMode() == 'permatch')
                $body .=  '<td class=title><a href="' . $link . orderIt('jackpot', $orderby, $link_query) . '"> ' . $lang['overview_jackpot'] . '</a></td>';


                foreach ($users as $user) {
                    /* @var $user User */
                    $player_column = $user->getId();
                    if (!isset($_REQUEST['col'])) {
                        $body .=  '<td class="title"><a href="' . $link . 'col=' . $player_column . '">' . $user->getLogin() . '</a></td>';
                    } elseif (isset($_REQUEST['col']) && $player_column == $_REQUEST['col']) {
                        $body .=  '<td class="title">' . $user->getLogin() . '</td><td class="title"><a href="' . $link . '"> >>> </a>';
                    }
                }
            }

            $body .= '</tr>';

            //to count the rows of matches
            $r = 0;

            //is set false if summeries are to be displayed
            $onlyall = true;
            $betonresults = ($event->getBetOn() == 'results') ? true : false;
            $last = '';
            $now = '';
            foreach ($bets as $bet) {
                /* @var $bet Bet */
                //increment rows
                $r++;

                if ($rowview && $r != $_REQUEST['row'])
                    continue;

                /*display a summary (either time or matchday, else no summary),
                if the value in question changes (matchday, or day)*/
                $last = $now; //for the comparison
                if($orderby[0] == 'dueDate') {
                        $now = date('Ymd', (int) $bet->getTime());
                        $onlyall = false;      
                }
                else if ($orderby[0] == 'matchday') {
                        $now = $bet->getMatchdayId();
                        $onlyall = false;
                        break;
                }
                //the actual summary
                if ($last != $now && $r != 1 && (!$rowview)) {
                    $body .=  '<tr class="ow_summary">';
                    $pointsnmoney = array($lang['ranking_points'], $lang['ranking_gain']);
                    $body .=  '<td>' . substitute($lang['overview_summary'], $pointsnmoney) . '</td>';
                    $body .=  '<td></td>';
                    $body .=  '<td></td>';
                    $body .=  '<td></td>';
                    $body .=  '<td></td>';
                    if ($event->getStakeMode() == 'permatch') $body .=  '<td class="ow">' . $jackpot . '</td>';
                    $jackpot_all += $jackpot;
                    $jackpot = 0;
                    foreach ($users as $user) {
                        /* @var $user User */
                        $uid = $user->getId();
                        $player_column = $uid;
                        if (!isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']) {
                            if ($event->getStakeMode() == 'permatch') $display_m = $money[$uid] . ' & ';
                            $body .=  '<td>' . $display_m . $points[$uid] . '</td>';
                        }
                        $money_all[$uid] += $money[$uid];
                        $money[$uid] = 0;
                        $points_all[$uid] += $points[$uid];
                        $points[$uid] = 0;
                    }
                    $body .=  '</tr>';
                } elseif ($onlyall) {
                    $jackpot_all = $jackpot;
                    foreach ($users as $user) {
                        /* @var $user User */
                        $uid = $user->getId();

                        $money_all[$uid] = $money[$uid];
                        $points_all[$uid] = $points[$uid];
                    }
                }

                $body .=  '<tr id="tr' . $r . '"
                                onMouseOver="setOverBG(\'tr' . $r . '\', \'' . $settings['style'] . '\')"
								onMouseOut="unsetOverBG(\'tr' . $r . '\')"
								onClick="switchToActivatedBG(\'tr' . $r . '\', \'' . $settings['style'] . '\')">';

                //match details & tips

                $ismatch = false;
                if ($bet instanceof Question) {
                    /* @var $bet Question */
                    $body .= '<td class="ow_team" colspan="4">'. wordwrap($bet->getQuestion(),'70','<br/>') .'</td>';
                    $answer = '';
                    foreach ($bet->getResult() as $a)
                        $answer .= $a.' ';
                    $body .= '<td class="title">'. $answer .'</td>
                                <td></td>';
                }
                elseif ($bet instanceof Match) {
                    $ismatch = true;

                    /* @var $bet Match */
                    $rowlink_p1 = !$rowview ? '<a href="' . $link . 'row=' . $r . '">' : '';
                    $rowlink_p2 = !$rowview ? '</a>' : '';
                    $body .=  '<td class="ow_date" width="50px">' . $rowlink_p1
                        . weekday($bet->getTime(), 1) . ', ' . date('d.m.Y - H:i', $bet->getTime())
                        . $rowlink_p2 . '</td>';
                    $body .=  '<td class="ow">' . $bet->getMatchday() . '</td>'; // "normal" td

                    $body .=  '<td class="ow_team" width="50px">' . $bet->getHome() . '</td>';
                    $body .=  '<td class="ow_team" width="50px">' . $bet->getVisitor() . '</td>';
                    $body .=  '<td class="ow"><nobr><font class="ow_correct">';
                    if ($bet->getResult() != '') {
                        $body .=  $bet->getResult();
                        $body .=  '</font>(' .  $bet->getCorrectBets() . 'x)';
                    }

                    $body .=  '</nobr></td>';
                    if ($event->getStakeMode() == 'permatch') {
                        $body .=  '<td class="ow">' . $bet->getJackpot() . '</td>';
                        $jackpot += $bet->getJackpot();
                    }
                }
                if ($rowview) {
                    $body .= '</table>';
                    $body .= '<table>';
                }
                foreach ($users as $user) {

                    if ($rowview) {
                       if (!isset($_REQUEST['col'])) {
                           $body .=  '<td><a href="' . $link . 'col=' . $user->getId() . '">' . $user->getLogin() . '</a></td>';
                       } elseif (isset($_REQUEST['col']) && $user->getId() == $_REQUEST['col']) {
                           $body .=  '<td>' . $user->getLogin() . '</td><td class="title"><a href="' . $link . '">>>></a>';
                       }
                    }

                    $uid = $user->getId();
                    $player_column = $uid;
                    // show tips already?
                    $showbets = (time() > $bet->getDueDate()) ? true : false;

                    if (!isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']) {
                        //if the time of the game has come, then show the tips

                        if ($showbets) {

                            $userbet = $bet->getBet($uid);
                            if (!is_array($userbet))  $userbet = array($userbet);

                            $userbetString = '';
                            if ($bet instanceof Match) {

                                //the user tip can have different formats
                                if ($bet->isCorrectBet($uid))
                                    $rclass = 'ow_correct';
                                elseif ($betonresults && $ismatch && $bet->isCorrectBet($uid))
                                    $rclass = 'ow_diff';
                                elseif ($betonresults && $ismatch && $bet->isCorrectWinner($uid))
                                    $rclass = 'ow_almost';
                                else $rclass = 'ow_wrong';


                                $userbetString = '';
                                if (sizeof($userbet)>0) {
                                    $userbetString = $userbet[0];
                                    unset($userbet[0]);
                                    foreach ($userbet as $ub)  $userbetString .= '<br/>'.$ub;
                                }

                                $body .=  '<td   rel="popover" data-orignal-title="'.$user->getLogin() .'"
                                    data-content="'
                                    . '<img  src=&quot;./data/user_img/' . $user->getPicture() . '&quot; '
                                    . 'alt=&quot;' . $lang['general_nopic'] . '&quot;/>"

                                       class="' . $rclass . '">'.$userbetString;

                            } else if ($bet instanceof Question) {

                                $correct = $bet->isCorrectBet($uid);
                                $pointing = preg_split('/:/',$bet->getPoints());

                                for ($i = 0; $i < sizeof($correct); $i++) {
                                    if ($pointing[$i] == '')
                                        continue;

                                    if ($userbetString != '')
                                        $userbetString .= '<br/>';

                                    if($correct[$i]) {
                                        $userbetString .= '<span class="ow_correct">'.$userbet[$i].' ('.$pointing[$i].'p)</span>';
                                    } else {
                                        $userbetString .=  '<span class="ow_wrong">'.$userbet[$i].'</span>';
                                    }

                                }
                                $body .=  '<td   rel="popover" data-orignal-title="'.$user->getLogin() .' "
                                            data-content="<img  src=\'./data/user_img/' . $user->getPicture() . ' \' '
                                            .'/>">'
                                    .$userbetString;
                            }
                        } else {
                            $body .= '<td class=ow>x';
                        }

                    }
                   // if (!isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']) {
                        //if ($betonresults) $body .=  ($showbets) ? $bet->getBet($uid) : 'x' ;
                        $body .=  '</td>';
                    //}

                    $points[$uid] += $bet->getUserPoints($uid); //for the summaries
                    $money[$uid] += $bet->getMoney($uid); //for the summaries

                    if ($rowview) $body .= '</tr>';

                }

                if (!$rowview) $body .=  '</tr>' . "\r\n";
            }

            if ($rowview) {
                $body .= '</table>';
            } else {
                //the end summary
                $body .=  '<tr class="ow_summaryall">';
                $body .=  '<td>' . $lang['overview_summaryall'] . '</td>';
                $body .=  '<td></td>';
                $body .=  '<td></td>';
                $body .=  '<td></td>';
                $body .=  '<td></td>';
                if ($event->getStakeMode() == 'permatch') $body .=  '<td class="ow">' . $jackpot_all . '</td>';
                foreach ($users as $user) {
                    $player_column = $user->getId(); // set player_column 0 again..
                    //$player_column++;
                    if (!isset($_REQUEST['col']) || isset($_REQUEST['col']) && $player_column == $_REQUEST['col']) {
                        if ($event->getStakeMode() == 'permatch') $display_m = $money_all[$user->getId()] . ' & ';
                        $body .=  '<td class="ow">' . $display_m . $points_all[$user->getId()] . '</td>';
                    }
                }
                $body .=  '</tr>';

                $body .=  '</table>';
            }
            $body .=  '</div>';
        }

    }

} else {
    errorPage('notinevent');
}

?>

