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
global $db, $settings, $events, $events_test;
$bdp_style = 'style_'.$settings['style'];


// is it an inactive event?
if (!(isset($events['u']['e'.$_REQUEST['ev']]))){
	$body .= '<h3>'.$events['i']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_results_title'].'</h3>';
	if($events['i']['e'.$_REQUEST['ev']]['active']<0) infoBarEventCreation(2);
	else infoBarEventCreation(3);
	$body .= $lang['admin_events_activatefirst'];
}else{


	//show all of it!
	
	$evdat = $events['u']['e'.$_REQUEST['ev']];
	$body .= '<h3>'.$events['u']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_results_title'].'</h3>';

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
		$idarray = explode(':', $data['ids']);
	}

	//get the info by what it content should be ordered
    $orderby = (isset($_REQUEST['orderby'])) ? explode(':', $_REQUEST['orderby']) : explode(':', 'dueDate:SORT_ASC');


    //get the info by applying the insight of $orderby
/*	$query = "SELECT *
				FROM ".PFIX."_event_".$_REQUEST['ev'].
				" ORDER BY ".$orderby[0]." ".$orderby[1].";";
	$bdp_matches =  $db->query($query);
	$bdp_rows =  $db->row_count($query);
*/

    $event = $events_test->getEventById($_REQUEST['ev']);
    $bdp_matches = $event->getBetsContainer()->getBets($_REQUEST['filter'],implode(':',$orderby));
    $bdp_rows =  sizeof($bdp_matches);
	
	//$mnb stands for Mantch NumBer, is necessary to limit the amount of matches displayed
	$mnb = (isset($_REQUEST['mnb'])) ? $_REQUEST['mnb'] : 1;

	
	if($bdp_matches == NULL){
			//well, there's nothing to display
			$body .= $lang['general_nomatches'];
	}else{


		$tipplus = '( 1 /';
		if(!($event->getKoMatches()=='only' && $event->getEnableTie()=='no')){
			$tipplus .= ' X /';
			$colspan = 3;
		}else{
			$colspan = 2;
		}
		$tipplus .= ' 2 )';


		//$MATCHESHEADER .= '<form action="?menu=admin&submenu=events&'.'evac=saveresults&which='.$mnb.'" method="POST" name="matches">';
        $MATCHESHEADER .= '<table class="showmatches" id="showresults">';
        $MATCHESHEADER .= '<tr class=title>
			<td class="visible-desktop"> <a href="'.$link.orderBy('dueDate', $orderby, $link_query).'"> '.$lang['admin_events_time'].'</a></td>
            <td span="2">
			    <div  class="span1 visible-desktop"><a href="'.$link.orderBy('home', $orderby, $link_query).'"> '.$lang['admin_events_home'].'</a></div>
			    <div class="span1 visible-desktop"><a href="'.$link.orderBy('visitor', $orderby, $link_query).'"> '.$lang['admin_events_visitor'].'</a></div>
			</td>';
			if($event->getScoreInputType()=='results'){
                $MATCHESHEADER .= '<td>'.$lang['admin_events_score'].'</td>';
			}else{
                $MATCHESHEADER .=  '<td colspan="'.$colspan.'">'.$lang['admin_events_score'].' '.$tipplus.'</td>';
			}
        $MATCHESHEADER .=  '<td>'.$lang['admin_events_special'].'<td>

			</tr>';

        $QUESTIONSHEADER = '<table class="showmatches">';
        $QUESTIONSHEADER .= '<tr class="title">
        			<td> '.$lang['general_id'].'</a></td>
					<td> <a href="'.$link.orderBy('dueDate', $orderby, $link_query).'"> '.$lang['admin_events_time'].'</a></td>
    				<td> Question </td>
    				<td> Answer </td>
    				<td>save</td>
    		</tr>';


		if (!isset($_REQUEST['orderby']) && !isset($_REQUEST['mnb'])){
			$closestGame = closestGame($_REQUEST['ev'], time());
			$page = floor($closestGame/$settings['formlines']);
			$mnb = $page * $settings['formlines'] + 1;
		}
		
		
        $nb = -1;
        $MATCHESTABLE = '';
        $QUESTIONSTABLE = '';

        foreach ($bdp_matches as $bet) {
            /* @var $bet Bet */
            $nb++;

			$start = $mnb;
			$limit = $mnb + $settings['formlines'];
			
			if ($nb+1 >= $start && $nb+1 < $limit){

                $ids .= $bet->getId().':';
                $id = $bet->getId();
                $betid = $bet->getId();
				
				//further error handling
				$imgsrc = 'src/'.$bdp_style.'/img/edit.png';
                
                $ro="";
				//decide if the data in the forms should come from db or error the $_post array

                if ($bet instanceof Match) {
                    /* @var $bet Match */

                    $score_h = $bet->getScoreH();
                    $score_v = $bet->getScoreV();
                    $special = $bet->getScoreSpecial();
                    //readonly per default
                    $robool = "true";
                    $ro = 'readonly="true"';
                    $dis = 'disabled="true"';


                    $time1 = date('d.m.Y', $bet->getTime());
                    $time2 = date('H:i', $bet->getTime());
                    $home = $bet->getHome();
                    $visitor = $bet->getVisitor();

                    $MATCHESTABLE .=  '<tr>
                        <td class="input visible-desktop">'.$time1.' '.$lang['general_time_at'].' '.$time2.'</td>
                        <td class="input" span="2">'
                            .$home.'<b> - </b>'.$visitor.'
                        </td>';
                        if($event->getScoreInputType()=='results'){
                            $MATCHESTABLE .= '<td>
                                                <input
                                                 type="number"
                                                 class="input-mini"
                                                 style="height:25px;"
                                                 id="h_'.$id.'"
                                                 '.$ro.'
                                                 name="score_h_'.$id.'"
                                                 value="'.$score_h.'"
                                                 oninput="savematchresult(event,\''.$betid.'\')"
                                                 > : '
                                            .'<input id="v_'.$id.'"
                                                '.$ro.'
                                                 type="number"
                                                 class="input-mini"
                                                 style="height:25px;"
                                                 name="score_v_'.$id.'"
                                                 value="'.$score_v.'"
                                                 oninput="savematchresult(event,\''.$betid.'\')"
                                                 >
                                               </td>';
                        }elseif($event->getScoreInputType()=='toto'){
                            $MATCHESTABLE .= '<td class="input">';
                            $MATCHESTABLE .= '<input '.$dis.' id="s1_'.$id.'" type="radio" value="1"  name="toto_'.$id.'">';
                            $MATCHESTABLE .= '</td>';
                            if(!($event->getKoMatches()=='only' && $event->getEnableTie()=='no')){
                                $MATCHESTABLE .= '<td class="input">';
                                if($bet->getKomatch() && $event->getEnableTie()!='yes')
                                    $MATCHESTABLE .= '<font id="sX_'.$id.'">--</font>';
                                else
                                    $MATCHESTABLE .= '<input '.$dis.' id="sX_'.$id.'" type="radio" value="3"  name="toto_'.$id.'">';
                                $MATCHESTABLE .= '</td>';
                            }else{
                                //dummy
                                $MATCHESTABLE .= '<font id="sX_'.$id.'"></font>';
                            }
                            $MATCHESTABLE .= '<td class="input">';
                            $MATCHESTABLE .= '<input '.$dis.' id="s2_'.$id.'" type="radio" value="2"  name="toto_'.$id.'">';
                            $MATCHESTABLE .= '</td>';

                        }



                    $MATCHESTABLE .= '<td class="input"><input id="special_'.$id.'"
                                    '.$ro.'
                                    class="input-mini"
                                    name="special_'.$id.'"
                                    value="'.$special.'"></td>';
                    $MATCHESTABLE .= '<td id = "savestatus_'.$betid.'" class="input">
                                        <a class="btn btn-small" 
                                        href="javascript:editResult(\''.$id.'\', \''.$lines.'\')">
                                            <i class="icon-edit"> </i>
                                        </a>
                                      </td>';
                    
                    $infobutton = '<a class="btn btn-small" href="index.php?menu=overview&row='.preg_replace('/\D/', '', $bet->getId()).'">
            </i>
          </a><br/>';
                    
                    $MATCHESTABLE .= '</tr>';
                    $MATCHESTABLE .= '<input id="ro_'.$id.'" name="ro_'.$id.'" type="hidden" value="'.$robool.'">';
                    $MATCHESTABLE .= '<input id="komatch_'.$id.'" name="komatch_'.$id.'" type="hidden" value="'.$bet->getKomatch().'">';
                    $MATCHESTABLE .= '<td ></td>';

                } else if ($bet instanceof Question) {
                    /* @var $bet Question */

                    $possibilities = preg_split('/:/',$bet->getPossibilities());
                    $autocomplete = "";
                    foreach ($possibilities as $poss) {
                        if ($poss == '')
                            continue;
                        $autocomplete .= '"'.$poss.'", ';
                    }
                    $res = $bet->getResult();
                    $options = sizeof($res);
                    if ($res == "") {
                        $res = array("");
                    }

                    $c = 0;
                    $betinput = '';

                    foreach ($res as $r) {
                        $inputid = 'bet_'.$betid.'_'.$c;
                        $nr = $c+1;
                        $betinput .= '<nobr> <input id="'.$inputid.'"  '.$ro.' value="'.$res[$c].'" size="10" >';
                        $betinput .= "</nobr>
                            <script type=\"text/javascript\">
                                $(document).ready(function() {
                                    $(\"#$inputid\").autocomplete({
                                        source: [".$autocomplete."]
                                    });
                                $( \"#$inputid\" ).autocomplete({
                                   select: function(event, ui) {
                                        saveanswer('$betid',$options,ui.item.value,$c); }
                                });
                            });

                             </script>
                             ";
                        $c++;
                    }

                    $QUESTIONSTABLE .= '<tr>
                            <td>'. $bet->getId() .'</td>
                            <td> '. $bet->getDueDate() .' </td>
                            <td> '. $bet->getQuestion() .' </td>
                            <td> '. $betinput .' </td>
                            <td id = "savestatus_'.$betid.'"></td>
                    </tr>';

                }
			}

		}


	}


    $body .= '<input name="query" type="hidden" value="'.$link_query.'">';
    $body .= '<input name="event" type="hidden" value="'.$_REQUEST['ev'].'">';
    $body .= '<input name="ids" type="hidden" value="'.$ids.'">';
//	for javascript to read out infos
    $body .= '<input name="score_input_type" id="score_input_type" type="hidden" value="'.$event->getScoreInputType().'" />';
    $body .= '<input name="style" id="style" type="hidden" value="'.$bdp_style.'" />';
    $body .= '<input name="edit" id="edit" type="hidden" value="'.$lang['general_edit'].'" />';
    $body .= '<input name="cancel" id="cancel" type="hidden" value="'.$lang['general_cancel'].'" />';

    if ($MATCHESTABLE != '') $body .= $MATCHESHEADER.$MATCHESTABLE.'</table>';
    if ($QUESTIONSTABLE != '') $body .= $QUESTIONSHEADER.$QUESTIONSTABLE.'</table>';

    $xajax -> register(XAJAX_FUNCTION, 'saveresult');

    //$xajax->configure('debug',true);

    $xajax->processRequest();
    $xajax->printJavascript();

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
        }

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



//========== xajax

function saveresult($id,$betresult) {
    global $events_test,$settings;
    $response = new xajaxResponse();
    $event = $events_test->getEventById($_REQUEST['ev']);

    $bet = $event->getBetById($id);
    $bool = $bet->setResult($betresult,'');

    if ($bool) {
        $src = 'src/style_'.$settings['style'].'/img/icon_ok.png';
    } else {
        $src = 'src/style_'.$settings['style'].'/img/icon_not_ok.png';
    }

    $image = " <img src=".$src." width = '20px' height= '20px' />";
    $response->assign('savestatus_'.$id,'innerHTML', $image);
    return $response;

}

$body .= '<script type="text/javascript" charset="UTF-8">
            /* <![CDATA[ */


           function saveanswer(betid,options,newvalue,index) {

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
                    if (chosen != "") {
                        bet = bet + chosen +  ":" ;
                    }
                }
                xajax_saveresult(betid,bet);

           }

           function savematchresult(event,id) {

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
                  bet = home  + " : " + visitor;

                  xajax_saveresult(id,bet)
           }

           /* ]]> */
           </script>';




