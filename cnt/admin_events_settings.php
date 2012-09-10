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

global $events, $events_test, $cont;

if (!(isset($events['u']['e'.$_REQUEST['ev']]))){
    $body .=  '<h3>'.$events['i']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_settings_title'].'</h3>';
}else{
    $body .=  '<h3>'.$events['u']['e'.$_REQUEST['ev']]['name'].': '.$lang['admin_events_settings_title'].'</h3>';
}
//========== edit an event
if($_REQUEST['ssubmenu'] == 'settings'){
    foreach($events_read as $e){
        if($e['id'] == $_REQUEST['ev']){
            $eve = $e;
        }
    }

    foreach($events_test->getAllEvents() as $e){
        /* @var $e Event */
        if($e->getId() == $_REQUEST['ev']){
            /** @var $selectedEvent Event */
            $selectedEvent = $e;
        }
    }


    $err['name'] = $err['deadline'] = $err['currency'] = $err['stake'] = $err['round'] = 'title';

    //were there any errors while filling out the form?
    if(isset($_SESSION['err'])){
        $err = $_SESSION['err'];
        $eve = $_SESSION['post'];
        unset ($_SESSION['err']);
        unset ($_SESSION['post']);
        foreach ($err as $i => $e){
            $err[$i] = ($e) ? 'error'  : 'title';
        }
        if (in_array('error', $err)) $body .=  '<font class="error">'.$lang['error_filledform'].'</font>';
    }

    /*
    $flcnt = generateEventInfo($_REQUEST['ev']);
        foreach($flcnt as $sid => $cnt)
            $body .=  makeFloatingLayer($events['u']['e'.$id]['name'], $cnt, 1, $_REQUEST['ev'].'_'.$sid);

    $body .=  '<a href="javascript: showFloatingLayer(\''.$_REQUEST['ev'].'_stake\')">sfkasfisldf</a><p/>';
    */
    //is event active or not?
    if($selectedEvent->getActive() == 1){
        $body .=  $lang['admin_events_active'].'<p>';
        $users['db'] = $db->query("SELECT id, login, account_type, account_holder, account_details FROM ".PFIX."_users");
        foreach ($users['db'] as $row){
            $users['all'][$row['id']]['name'] = $row['login'];
            $users['all'][$row['id']]['account_type'] = $row['account_type'];
            $users['all'][$row['id']]['account_holder'] = $row['account_holder'];
            $users['all'][$row['id']]['account_details'] = $row['account_details'];
            //prepare hidden fields for form
            $u['h'] .= '<input type="hidden" name="hiddenfield_'.$row['id'].'" id="hf_'.$row['id'].'" value="0">';
        }

        //Preparation for the form
        //=> process public setting
        if($selectedEvent->getPublic()==1){
            $p['y'] = 'selected';
            $p['n'] = '';
        }else{
            $p['n'] = 'selected';
            $p['y'] = '';
        }

        //=> process users (approved, waiting, denied)
        $users['approved'] = preg_split('/:/', $selectedEvent->getUsersApproved());
        array_pop($users['approved']);
        $u['a'] = "<table align='center'>";
        foreach($users['approved'] as $id){
            $u['a']	.= "<tr><td>".$users['all'][$id]['name']."</td>";
            if ($users['all'][$id]['account_type'] == NULL){
                $u['a'].="<td></td>";
            }else{
                $u['a'].=' <td> <a href="javascript: showFloatingLayer(\''.$id.'_account\')">('.$lang['general_bank_account'].')</a></td>';
                $flcnt = '<b>'.$users['all'][$id]['account_type'].'</b><p/<p/>'
                    .'<pre>'.$users['all'][$id]['account_holder'].'</pre><br/>'
                    .$users['all'][$id]['account_details'].'<br/>';
                $body .=  makeFloatingLayer($users['all'][$id]['name'], $flcnt,1,$id.'_account');
            }
            $flcnt .= '<input type="hidden" name="id" value="'.$selectedEvent->getId().'"/>';

            $u['a'] .= '<input type="hidden" name="'.$id.'_paidhf" id="'.$id.'_paidhf" value="">';
            $paidstring = ($selectedEvent->userHasPaid($id)) ?
                $cont->get('admin_settings_paid'):
                $cont->get('admin_settings_notpaid');
            $paidclass = ($selectedEvent->userHasPaid($id)) ?
                'positive' : 'negative';
            $u['a'] .= '<td><a href="javascript: userPaid(\''.$cont->get('admin_settings_paid').'\', \''.$cont->get('admin_settings_notpaid').'\', \''.$id.'\')">
                            <p class="'.$paidclass.'" id="'.$id.'_paid">'.$paidstring.'</p>
                        </a></td>';

            if ($selectedEvent->getFinished()==true) {
                $u['a'] .= '<input type="hidden" name="'.$id.'_reimbursedhf" id="'.$id.'_reimbursedhf" value="">';
                $reimbursedstring = ($selectedEvent->userHasBeenReimbursed($id)) ?
                    'zrüggzaut' :
                    'nid zrüggzaut';

                $reimbursedclass = ($selectedEvent->userHasBeenReimbursed($id)) ?
                    'positive' : 'negative';
                $u['a'] .= '<td><a href="javascript: userReimbursed(\'zrüggzaut\', \'nid zrüggzaut\', \''.$id.'\')">
                            <p class="'.$reimbursedclass.'" id="'.$id.'_reimbursed">'.$reimbursedstring.'</p>
                        </a></td>';
            }
            $u['a'] .= "</tr>";
        }
        $u['a'] .= "</table>";
        $users['waiting'] = explode(':', $selectedEvent->getUsersWaiting());
        array_pop($users['waiting']);
        foreach($users['waiting'] as $id){
            $userinfo = loadSettings($id);
            $flcnt = '<table><tr>
							<td>'.$lang['general_name'].' & '.$lang['general_famname'].':</td>
							<td>'.$userinfo['name'].' '.$userinfo['famname'].'</td>
						</tr><tr>
							<td>'.$lang['register_email'].':</td>
							<td>'.$userinfo['email'].'</td>
					</tr></table>';
            $body .=  makeFloatingLayer($userinfo['login'], $flcnt, 1, 'u'.$userinfo['id']);
            $u['w']	.= '<font id="uw_'.$id.'">'.$users['all'][$id]['name'].'</font>
					<a href="javascript: manageUser(\'a\', \''.$id.'\')">'.$lang['admin_events_approve'].'</a> /
					<a href="javascript: manageUser(\'d\', \''.$id.'\')">'.$lang['admin_events_deny'].'</a> / 
					<a href="javascript: manageUser(\'w\', \''.$id.'\')">'.$lang['admin_events_wait'].'</a> / 
					<a href="javascript: showFloatingLayer(\'u'.$id.'\')" title="'.$lang['general_show_info'].'"> '.$cont->get('general_info').' </a> <br/>';
        }
        $users['denied'] = explode(':', $selectedEvent->getUsersDenied());
        array_pop($users['denied']);
        foreach($users['denied'] as $id)
            $u['d']	.= $users['all'][$id]['name'].'<br>';

        //=>If deadline's over, propose the addition of users, nevertheless...
        if ($selectedEvent->getDeadline() < time()){
            $afterdeadline = '<br>'.$lang['admin_events_deadlineover'];
            $afterdeadline .= ' <a href="javascript: showFloatingLayer(\'1\')">+</a>';
        }

        $xajax -> register(XAJAX_FUNCTION, 'manageuser');

        //prepare a Floating layer:
        $flcnt = '<form name="adduser" action="?menu=admin&submenu=events&evac=saveactive" method="POST">';
        //=> the values in the hidden fields don't change, but the info is needed by the update-procedure following this form
        $flcnt .= '<input type="hidden" name="id" value="'.$selectedEvent->getId().'"/>';
        $flcnt .= '<input type="hidden" name="adduserform" value="1"/>';
        $flcnt .= '<table><tr>';
        $counter = 0;
        $users['db'];
        foreach($users['db'] as $user){
            if ( !(userParticipates($selectedEvent->getId(), $user['id'])) && !(userWaits($selectedEvent->getId(), $user['id']))){
                $counter++;
                $flcnt .= '<td><input class="input_fl" type="checkbox" name="u_'.$user['id'].'" value="1"/> '.$user['login'].'</td>';
                if (($counter % 3) == 0) $flcnt .= '<tr/><tr>';
            }
        }
        $flcnt .= '</tr></table><input type="submit"/ value="'.$lang['general_savechanges'].'"></form>';
        $body .=  makeFloatingLayer($lang['admin_events_adduser'], $flcnt);

        //Settings_title
        $body .=  '<h3>'.$lang['admin_events_eventsettings'].'</h3>';


        //the form
        $body .=  '<form name="edit" action="?menu=admin&submenu=events&evac=saveactive" method="POST">'
            .'<input type="hidden" name="id" value="'.$selectedEvent->getId().'">'.$u['h']
            .'<table class="showform">
			<tr>
				<td class="title">'.$lang['admin_events_name'].'</td>
			</tr><tr>
				<td class="input"><input name="name" size=20 value="'.$selectedEvent->getName().'"</td>
			</tr><tr>
				<td class="title">'.$lang['admin_events_public'].'</td>
			</tr><tr>
				<td class="input"><select name="public" size=2>
					<option value="1" '.$p['y'].'>'.$lang['general_yes'].'</option>
					<option value="0" '.$p['n'].'>'.$lang['general_no'].'</option></td>
			</tr><tr>
				<td class="title">'.$lang['admin_events_userswaiting'].' ('.sizeof($users['waiting']).')</td>
			</tr><tr>
				<td class="input">'.$u['w'].'</td>
			</tr><tr>			
				<td class="title">'.$lang['admin_events_deadline'].'</td>
			</tr><tr>
				<td class="input">'.date('d.m.Y', $selectedEvent->getDeadline()).$afterdeadline.'</td>
			</tr><tr>
				<td class="title">'.$lang['admin_events_usersapproved'].' ('.sizeof($users['approved']).')</td>
			</tr><tr>
				<td class="input">'.$u['a'].'</td>
			</tr><tr>
				<td class="title">'.$lang['admin_events_usersdenied'].' ('.sizeof($users['denied']).')</td>
			</tr><tr>
				<td class="input">'.$u['d'].'</td>
			</tr><tr>
				<td></td>
			</tr><tr>
				<td class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"></td>
			</tr>
		</table>';
        //integrate FloatingLayer into form!
        $body .=  '</form>';



        //not active && phase = 2
    }elseif($selectedEvent->getActive()<0){
        if(!($err['deadline'] == 'error')) $eve['deadline'] = date('d.m.Y', $eve['deadline']);

        if($eve['p_correct'] != NULL) $nextstep = 2;
        $body .=  infoBarEventCreation(2, $nextstep);
        if($eve['p_correct'] != NULL){
            $flcnt = generateEventInfo($_REQUEST['ev']);
            foreach($flcnt as $sid => $cnt)
                $body .=  makeFloatingLayer($events['u']['e'.$id]['name'], $cnt, 1, $_REQUEST['ev'].'_'.$sid);
            $body .=  '<p/><div align="center"><b><a href="javascript: showFloatingLayer(\''.$_REQUEST['ev'].'_stake\')">'.$lang['admin_events_displayinfo'].'</a></b></div>';
        }



        $body .=  '<p>'.$lang['admin_events_inactive'];

        $body .= '<script type="text/javascript">
			function switchInput(spec){
				input = document.getElementById(spec);
				input.disabled = (input.disabled == true) ? false : true;
			}
			function setStake(mode){
				var matchnbexp = document.getElementById("matchnbexp");
				var matchnbdiv = document.getElementById("matchnbdiv");
				var matchnbfield = document.getElementById("matchnbfield");
				var fix = document.getElementById("fix");
				var permatch = document.getElementById("permatch");
				var stakediv = document.getElementById("stakediv");
				var stakefield = document.getElementById("stakefield");
				var currencyexp = document.getElementById("currencyexp");
				var currencydiv = document.getElementById("currencydiv");
				var currencyfield = document.getElementById("currencyfield");
				var roundexp = document.getElementById("roundexp");
				var rounddiv = document.getElementById("rounddiv");
				var roundfield = document.getElementById("roundfield");
				var stakebackexp = document.getElementById("stakebackexp");
				var stakebackdiv = document.getElementById("stakebackdiv");
				var stakebackfield = document.getElementById("stakebackfield");
				var betonfield = document.getElementById("betonfield");
				var jackpotsection = document.getElementById("jackpotsection");
				switch(mode){
					case "none":
						matchnbexp.setAttribute("class", "explanation notvisible");
						matchnbdiv.setAttribute("class", "input notvisible");
						matchnbfield.value = "";
						fix.setAttribute("class", "explanation notvisible");
						permatch.setAttribute("class", "explanation notvisible");
						stakediv.setAttribute("class", "input notvisible");
						stakefield.value = "0";
						currencyexp.setAttribute("class", "explanation notvisible");
						currencydiv.setAttribute("class", "input notvisible");
						currencyfield.value = "";
						roundexp.setAttribute("class", "explanation notvisible");
						rounddiv.setAttribute("class", "input notvisible");
						stakebackexp.setAttribute("class", "explanation notvisible");
						stakebackdiv.setAttribute("class", "input notvisible");
						jackpotsection.setAttribute("class", "notvisible");
						break;
					case "fix":
						matchnbexp.setAttribute("class", "explanation notvisible");
						matchnbdiv.setAttribute("class", "input notvisible");
						matchnbfield.value = "";
						fix.setAttribute("class", "explanation");
						permatch.setAttribute("class", "explanation notvisible");
						stakebackexp.setAttribute("class", "explanation notvisible");
						stakebackdiv.setAttribute("class", "input notvisible");
						stakediv.setAttribute("class", "input");
						currencyexp.setAttribute("class", "explanation");
						currencydiv.setAttribute("class", "input");
						roundexp.setAttribute("class", "explanation");
						rounddiv.setAttribute("class", "input");
						jackpotsection.setAttribute("class", "");
						break;
					case "permatch":
						matchnbexp.setAttribute("class", "explanation");
						matchnbdiv.setAttribute("class", "input");
						fix.setAttribute("class", "explanation notvisible");
						permatch.setAttribute("class", "explanation");
						stakediv.setAttribute("class", "input");
						currencyexp.setAttribute("class", "explanation");
						currencydiv.setAttribute("class", "input");
						roundexp.setAttribute("class", "explanation");
						rounddiv.setAttribute("class", "input");
						stakebackexp.setAttribute("class", "explanation");
						stakebackdiv.setAttribute("class", "input");
						if(betonfield.value=="toto" && stakebackfield.value=="yes") {
							alert("' . $lang['admin_events_stakebacknotpossible'] . '");
							stakebackfield.value="no";
							stakebackfield.selectedindex=0;
						}
						jackpotsection.setAttribute("class", "");
						break;
				}
			}

		function verifyStakeBack(){
			var stakebackfield = document.getElementById("stakebackfield");
			var betonfield = document.getElementById("betonfield");
			if(betonfield.value=="toto" && stakebackfield.value=="yes"){
				alert("' .  $lang['admin_events_stakebacknotpossible'] . '");
				stakebackfield.value="no";
				stakebackfield.selectedindex=0;
			}
		}

		function setJackpot(sharers){
			fractionexp = document.getElementById("fractionexp");
			fractiondiv = document.getElementById("fractiondiv");
			jackpotfixexp = document.getElementById("jackpotfixexp");
			jackpotfixdiv = document.getElementById("jackpotfixdiv");
			switch(sharers){
				case "fraction":
					fractionexp.setAttribute("class", "explanation");
					fractiondiv.setAttribute("class", "input");
					jackpotfixexp.setAttribute("class", "explanation notvisible");
					jackpotfixdiv.setAttribute("class", "input notvisible");
				break;
				case "fix":
					fractionexp.setAttribute("class", "explanation notvisible");
					fractiondiv.setAttribute("class", "input notvisible");
					jackpotfixexp.setAttribute("class", "explanation");
					jackpotfixdiv.setAttribute("class", "input");
				break;
			}
		}

		function setDistr(alg, firstjackpotfixfield){
			layer = document.getElementById("floating_layerfixshares");
			jackpotfixfield = document.getElementById("jackpotfixfield");
			fixsharesexp = document.getElementById("fixsharesexp");
			fixsharesdiv = document.getElementById("fixsharesdiv");
			fixsharesfield = document.getElementById("fixsharesfield");
			expsharesexp = document.getElementById("expsharesexp");
			expsharesdiv = document.getElementById("expsharesdiv");
			switch(alg){
				case "fix":
					if(jackpotfixfield.value == "" || jackpotfixfield.value < "1"){
						alert(firstjackpotfixfield);
						fixsharesexp.setAttribute("class", "explanation notvisible");
						fixsharesdiv.setAttribute("class", "input notvisible");
						expsharesexp.setAttribute("class", "explanation notvisible");
						expsharesdiv.setAttribute("class", "input notvisible");
						document.edit.jp_distr_algorithm.options[0].selected = false;
						jackpotfixfield.focus();
					}else{
						fixsharesexp.setAttribute("class", "explanation");
						fixsharesdiv.setAttribute("class", "input");
						expsharesexp.setAttribute("class", "explanation notvisible");
						expsharesdiv.setAttribute("class", "input notvisible");
						showFloatingLayer("fixshares");
						var nb = jackpotfixfield.value;
						if (fixsharesfield.value != ""){
							shares = fixsharesfield.value.split(":");
						}else{
							var shares = new Array();
							for (var i = nb; i>0; i--) shares[i-1] = 0;
						}
						for (var i = nb; i>0; i--){
							if(i==nb){
								layer.innerHTML = (nb-i+1)+\' <input id="rank\'+ (nb-i+1) +\'" size="3" value="\' + shares[nb-i] +\' " onchange="sumShares(\'+nb+\')">%<br/>\';
							}else{
								layer.innerHTML = layer.innerHTML + (nb-i+1) +\'. <input id="rank\'+(nb-i+1)+\'" size="3" value="\'+shares[nb-i]+\'" onchange="sumShares(\'+nb+\')>%<br/>\';
							}
						}
						layer.innerHTML = layer.innerHTML + "----------------------<br/>";
						layer.innerHTML = layer.innerHTML + \'<div id="putSharesDiv">0%</div>\';
						sumShares(nb);
					}
					break;
				case "lin":
					fixsharesexp.setAttribute("class", "explanation notvisible");
					fixsharesdiv.setAttribute("class", "input notvisible");
					expsharesexp.setAttribute("class", "explanation notvisible");
					expsharesdiv.setAttribute("class", "input notvisible");
					break;
				case "exp":
					fixsharesexp.setAttribute("class", "explanation notvisible");
					fixsharesdiv.setAttribute("class", "input notvisible");
					expsharesexp.setAttribute("class", "explanation");
					expsharesdiv.setAttribute("class", "input");
					break;
			}
		}

		function sumShares(nb){
			var percent = 0;
			putSharesDiv = document.getElementById("putSharesDiv");
			for(var i = nb; i>0; i--){
				percent = parseInt(document.getElementById("rank"+i).value) + parseInt(percent);
			}
			document.getElementById("putSharesDiv").innerHTML = percent+"%";
			if (percent==100)
				putSharesDiv.innerHTML = putSharesDiv.innerHTML + \'<br/><button onClick="putSharesIn("\'+nb+\'")">OK</button>\';
		}

		function putSharesIn(nb){
			var percentstr = "";
			for(var i = nb; i > 0 ; i--){
				percentstr = percentstr + document.getElementById("rank"+(nb-i+1)).value +":";
			}
			document.getElementById("fixsharesfield").value = percentstr;
			hideFloatingLayer("fixshares");
		}

		function setPoints(beton){
			correctresult = document.getElementById("correctresult");
			correcttoto = document.getElementById("correcttoto");
			diff = document.getElementById("diff");
			diffbox = document.getElementById("diffbox");
			almost = document.getElementById("almost");
			almostbox = document.getElementById("almostbox");
			stakebackfield = document.getElementById("stakebackfield");
			inputtypeexp = document.getElementById("inputtypeexp");
			inputtypefield = document.getElementById("inputtypefield");
			switch (beton){
				case "results":
					correctresult.setAttribute("class", "explanation");
					correcttoto.setAttribute("class", "explanation notvisible");
					diffbox.disabled = false;
					almost.disabled = false;
					almostbox.disabled = false;
					almostbox.checked = true;
					inputtypeexp.setAttribute("class", "explanation notvisible");
					inputtypefield.setAttribute("class", "input notvisible");
					break;
				case "toto":
					correctresult.setAttribute("class", "explanation notvisible");
					correcttoto.setAttribute("class", "explanation");
					diffbox.disabled = true;
					diffbox.checked = false;
					diff.disabled = true;
					almost.disabled = true;
					almostbox.disabled = true;
					almostbox.checked = false;
					inputtypeexp.setAttribute("class", "explanation");
					inputtypefield.setAttribute("class", "input");
					if(stakebackfield.value=="yes"){
						alert("' . $lang['admin_events_stakebacknotpossible'] . '");
						stakebackfield.value="no";
						stakebackfield.selectedindex=0;
					}

					break;
			}
		}

		function koMatches(answer){
			tietoughexp = document.getElementById("tietoughexp");
			tietoughdiv = document.getElementById("tietoughdiv");
			tietoughfield = document.getElementById("tietoughfield");
			switch (answer){
				case "no":
					tietoughexp.setAttribute("class", "explanation notvisible");
					tietoughdiv.setAttribute("class", "input notvisible");
					tietough("yes");
					break;
				case "yes":
					tietoughexp.setAttribute("class", "explanation");
					tietoughdiv.setAttribute("class", "input");
					if(tietoughfield.value=="no") tietough("no");
					break;
				case "only":
					tietoughexp.setAttribute("class", "explanation");
					tietoughdiv.setAttribute("class", "input");
					if(tietoughfield.value=="no") tietough("no");
					break;
			}
		}

		function tietough(answer){
			afterpenaltyexp = document.getElementById("afterpenaltyexp");
			afterpenaltydiv = document.getElementById("afterpenaltydiv");
			switch (answer){
				case "yes":
					afterpenaltyexp.setAttribute("class", "explanation notvisible");
					afterpenaltydiv.setAttribute("class", "input notvisible");
					break;
				case "no":
					afterpenaltyexp.setAttribute("class", "explanation");
					afterpenaltydiv.setAttribute("class", "input");
					break;
			}
		}

		function verify(nextstep){
			if(nextstep==1) document.getElementById("nextstep").value = 1;

			namefield = document.getElementById("namefield");
			if(namefield.value==""){
				alert("' .  $lang['admin_events_err_invalid_name'] . '");
				return;
			}


			deadlinefield = document.getElementById("deadlinefield");
			dl = deadlinefield.value;
			dlsp = dl.split(".");
			numericaldot = /^([0-9])+(\.([0-9])+){0,1}$/;
			numerical = /^([0-9])+$/;
			numericalnegative = /^[-]?([0-9])+$/;
			two_digits = /[0-9][0-9]/;
			four_digits = /[0-9][0-9][0-9][0-9]/;
			if(!(two_digits.test(dlsp[0])) || !(two_digits.test(dlsp[1])) || !(four_digits.test(dlsp[2])) ){
				alert("' . $lang['admin_events_err_invalid_date'] . '");
				return;
			}

			stakemodefield = document.getElementById("stakemodefield");
			if(stakemodefield.value != "none"){

				if(stakemodefield.value=="permatch"){
					matchnbfield = document.getElementById("matchnbfield");
					if(matchnbfield.value == "" || matchnbfield.value=="0" || !(numerical.test(matchnbfield.value))){
						alert("'. $lang['admin_events_err_invalid_matchnb'] .'");
						return;
					}
				}


				stakefield = document.getElementById("stakefield");
				if(stakefield.value == "" || stakefield.value=="0" || !(numericaldot.test(stakefield.value))){
					alert("'. $lang['admin_events_err_invalid_stake'] .'");
					return;
				}

				currencyfield = document.getElementById("currencyfield");
				if(currencyfield.value == "" || numerical.test(currencyfield.value)){
					alert("'. $lang['admin_events_err_invalid_currency'] . '");
					return;
				}

				roundfield = document.getElementById("roundfield");
				if(roundfield.value == "" || roundfield.value=="0" || !(numericaldot.test(roundfield.value))){
					alert("'. $lang['admin_events_err_invalid_round'] . '");
					return;
				}

				jackpotmodefield = document.getElementById("jackpotmodefield");
				if(jackpotmodefield.value=="fraction"){
					fractionfield = document.getElementById("fractionfield");
					if(!(numerical.test(fractionfield.value)) || parseInt(fractionfield.value) > 100 || parseInt(fractionfield.value) < 1){
						alert("'. $lang['admin_events_err_invalid_fraction'] . '");
						return;
					}
				}else{
					jackpotfixfield = document.getElementById("jackpotfixfield");
					if(!(numerical.test(jackpotfixfield.value)) || parseInt(jackpotfixfield.value) < 1){
						alert("'. $lang['admin_events_err_invalid_jackpotfix'] . '");
						return;
					}
				}


				jpdistrmodefield = document.getElementById("jpdistrmodefield");
				if(jpdistrmodefield.value=="fix"){
					fixsharesfield = document.getElementById("fixsharesfield");
					fixshsp = fixsharesfield.value.split(":");
					fixshsp.pop();
					if(fixshsp.length != parseInt(jackpotfixfield.value)){
						alert("'. $lang['admin_events_err_invalid_jackpotfixshares'] . '");
						return;
					}
				}else if(jpdistrmodefield.value=="exp"){
					expsharesfield = document.getElementById("expsharesfield");
					if(!(numericaldot.test(expsharesfield.value)) || parseFloat(expsharesfield.value) >= 1 || parseFloat(expsharesfield.value) < 0.1){
						alert("'. $lang['admin_events_err_invalid_expshare'] . '");
						return;
					}
				}

			}


			betuntilfield = document.getElementById("betuntilfield");
			if(betuntilfield.value=="" || !(numericaldot.test(betuntilfield.value))){
				alert("'. $lang['admin_events_err_invalid_betuntil'] . '");
				return;
			}

			correct = document.getElementById("correct");
			if(correct.value == "" || !(numerical.test(correct.value))){
				alert("'. $lang['admin_events_err_invalid_pcorrect'] . '");
				return;
			}
			if(document.getElementById("diffbox").checked==true){
				diff = document.getElementById("diff");
				if(diff.value == "" || !(numerical.test(diff.value))){
					alert("'. $lang['admin_events_err_invalid_pdiff'] . '");
					return;
				}
			}
			if(document.getElementById("almostbox").checked==true){
				almost = document.getElementById("almost");
				if(almost.value == "" || !(numerical.test(almost.value))){
					alert("'. $lang['admin_events_err_invalid_palmost'] . '");
					return;
				}
			}
			wrong = document.getElementById("wrong");
			if(wrong.value == "" || !(numericalnegative.test(wrong.value))){
				alert("'. $lang['admin_events_err_invalid_pwrong'] . '");
				return;
			}

			document.edit.submit();
		}

		</script>';


        //preparations
        $jackpotdistributions = Array($lang['admin_events_settings_stakemode_fix'],
            $lang['admin_events_settings_distr_lin'],
            $lang['admin_events_settings_distr_exp']);

        $body .=  makeFloatingLayer('','', 0, 'fixshares');

        //preparations...

        //notvisible-classes, selected, checked, disabled
        switch($eve['stake_mode']){
            case 'none':
                $sel['stake_none'] = 'selected';
                $nt['matchnb'] = $nt['fix'] = $nt['permatch'] = $nt['stakediv'] = $nt['currency'] = $nt['round'] = $nt['jackpotsection'] = 'notvisible';
                $nt['matchnbexp'] = $nt['matchnbdiv'] = $nt['stakeback'] = 'notvisible';
                break;
            case 'fix':
                $sel['stake_fix'] = 'selected';
                $nt['matchnb'] = $nt['permatch'] = $nt['stakeback'] = 'notvisible';
                break;
            case 'permatch':
                $sel['stake_permatch'] = 'selected';
                $nt['fix'] = 'notvisible';
                break;
        }

        if($eve['stake_back'] == "yes")
            $sel['stakebackyes'] = 'selected';
        else
            $sel['stakebackno'] = 'selected';

        if($eve['jp_fraction_or_fix'] == 'fraction'){
            $eve['jp_fraction'] = $eve['jp_fraction']*100;
            $sel['fraction'] = 'selected';
            $nt['jackpotfix'] = 'notvisible';
        }else{
            $sel['jackpotfix'] = 'selected';
            $nt['fraction'] = 'notvisible';
        }

        switch($eve['jp_distr_algorithm']){
            case 'fix':
                $sel['fixshares'] = 'selected';
                $nt['expshares'] =  'notvisible';
                break;
            case 'exp':
                $sel['expshares'] = 'selected';
                $nt['fixshares'] = 'notvisible';
                break;
            case 'lin':
                $sel['linshares'] = 'selected';
                $nt['fixshares'] = 'notvisible';
                $nt['expshares'] =  'notvisible';
                break;
        }

        $bu = preg_split('/:/', $eve['bet_until']);
        $val['betuntil_nb'] = $bu[0];
        if($bu[1] == "m") $sel['betuntil_time_m'] = 'selected';
        elseif($bu[1] == "h") $sel['betuntil_time_h'] = 'selected';
        elseif($bu[1] == "d") $sel['betuntil_time_d'] = 'selected';
        if($bu[2] == "m") $sel['betuntil_before_m'] = 'selected';
        elseif($bu[2] == "t") $sel['betuntil_before_t'] = 'selected';

        if($eve['p_diff'] != NULL) $check['diff'] = 'checked';
        else $dis['diff'] = 'disabled';

        if($eve['p_almost'] != NULL) $check['almost'] = 'checked';
        else $dis['almost'] = 'disabled';

        switch($eve['bet_on']){
            case 'results':
                $sel['betonresults']  = 'selected';
                $nt['inputtype'] = 'notvisible';
                break;
            case 'toto':
                $sel['betontoto'] = 'selected';
                $dis['diffbox'] = $dis['almostbox'] = 'disabled';
                $nt['inputtype'] = '';
                break;
        }

        switch($eve['score_input_type']){
            case 'results':
                $sel['inputtyperes']  = 'selected';
                break;
            case 'toto':
                $sel['inputtypetoto']  = 'selected';
                break;
        }

        switch($eve['ko_matches']){
            case 'no':
                $sel['ko_no'] = 'selected';
                $nt['enable_tie'] = $nt['afterpenalty'] = 'notvisible';
                break;
            case 'yes':
                $sel['ko_yes'] = 'selected';
                break;
            case 'only':
                $sel['ko_only'] = 'selected';
        }

        if ($eve['enable_tie'] == 'yes'){
            $sel['tie_yes'] = 'selected';
            $nt['afterpenalty'] = 'notvisible';
        }else{
            $sel['tie_no'] = 'selected';
        }

        if ($eve['ap_score'] == 'addall'){
            $sel['ap_all'] = 'selected';
        }elseif($eve['ap_score'] == 'addone'){
            $sel['ap_one'] = 'selected';
        }

        //form
        $body .=  '<h3>'.$lang['admin_events_eventsettings'].'</h3>';

        $body .=  '<form name="edit" action="index.php?menu=admin&submenu=events&evac=save" method="POST">'
            .'<input type="hidden" name="form" value="ssubmenu=settings&'.$link_query.'">'
            .'<input type="hidden" name="formname" value="phase2">'
            .'<input type="hidden" id="nextstep" name="nextstep" value="">'
            .'<input type="hidden" name="id" value="'.$selectedEvent->getId().'">'
            .'<div class="showform">
				<div class="title">'.$lang['admin_events_name'].'</div>
				<div class="input"><input id="namefield" name="name" size=20 value="'.$eve['name'].'"></div>

				<div class="title">'.$lang['admin_events_deadline'].'</div>
				<div class="input"><input id="deadlinefield" name="deadline" size=10 value="'.$eve['deadline'].'"></div>

				<div class="title">'.$lang['admin_events_stake'].'</div>
				<div class="input"><select id="stakemodefield" name="stake_mode" size=3>
						<option value="none" onClick="setStake(\'none\')" '.$sel['stake_none'].'>'.$lang['admin_events_settings_stakemode_none'].'</option>
						<option value="fix" onClick="setStake(\'fix\')" '.$sel['stake_fix'].'>'.$lang['admin_events_settings_stakemode_fix'].'</option>
						<option value="permatch" onClick="setStake(\'permatch\')" '.$sel['stake_permatch'].'>'.$lang['admin_events_settings_stakemode_permatch'].'</option>
					</select>
				</div>

				<div id="matchnbexp" class="explanation '.$nt['matchnb'].'">'.$lang['admin_events_settings_matchnb'].'</div>
				<div id="matchnbdiv" class="input '.$nt['matchnb'].'"><input id="matchnbfield" name="match_nb" size=10 value="'.$eve['match_nb'].'"></div>
				<div  id="fix" class="explanation '.$nt['fix'].'">'.$lang['admin_events_settings_stake_fix'].'</div>
				<div  id="permatch" class="explanation '.$nt['permatch'].'">'.$lang['admin_events_settings_stake_permatch'].'</div>
				<div  id="stakediv" class="input '.$nt['stakediv'].'"><input id="stakefield" name="stake" size=10 value="'.$eve['stake'].'"></div>

				<div id="currencyexp" class="explanation '.$nt['currency'].'">'.$lang['admin_events_currency'].'</div>
				<div id="currencydiv" class="input '.$nt['currency'].'"><input id="currencyfield" name="currency" size=10 value="'.$eve['currency'].'"></div>
				<div id="roundexp" class="explanation '.$nt['round'].'">'.$lang['admin_events_round'].'</div>
				<div id="rounddiv" class="input '.$nt['round'].'"><input id="roundfield" name="round" size=10 value="'.$eve['round'].'"></div>

				<div id="stakebackexp" class="explanation '.$nt['stakeback'].'">'.$lang['admin_events_stakeback'].'</div>
				<div id="stakebackdiv" class="input '.$nt['stakeback'].'">
					<select id="stakebackfield" onClick="verifyStakeBack()" name="stake_back" size=2>
						<option value="no" '.$sel['stakebackno'].'>'.$lang['general_no'].'</option>
						<option value="yes" '.$sel['stakebackyes'].'>'.$lang['general_yes'].'</option>
					</select>
				</div>

				<div id="jackpotsection" class="'.$nt['jackpotsection'].'">
				<div class="title">'.$lang['overview_jackpot'].'</div>
				<div class="explanation">'.$lang['admin_events_jackpotexp'].'</div>
				<div class="input"><select id="jackpotmodefield" name="jp_fraction_or_fix" size=2>
						<option value="fraction" onClick="setJackpot(\'fraction\')" '.$sel['fraction'].'>'.$lang['admin_events_settings_jackpotmode_fraction'].'</option>
						<option value="fix" onClick="setJackpot(\'fix\')" '.$sel['jackpotfix'].'>'.$lang['admin_events_settings_stakemode_fix'].'</option>
					</select></div>

				<div id="fractionexp" class="explanation" '.$nt['fraction'].'>'.$lang['admin_events_jackpot_fraction'].'</div>
				<div id="fractiondiv" class="input" '.$nt['fraction'].'><input id="fractionfield" name="jp_fraction" size=3 value="'.$eve['jp_fraction'].'" />%</div>

				<div id="jackpotfixexp" class="explanation '.$nt['jackpotfix'].'">'.$lang['admin_events_jackpot_fix'].'</div>
				<div id="jackpotfixdiv" class="input '.$nt['jackpotfix'].'"><input id="jackpotfixfield" name="jp_fix" size=3 value="'.$eve['jp_fix'].'" /></div>

				<div class="title">'.$lang['admin_events_distr'].'</div>
				<div class="explanation">'.substitute($lang['admin_events_distralgorithm'], $jackpotdistributions).'</div>
				<div class="input"><select id="jpdistrmodefield" name="jp_distr_algorithm" size=3>
						<option value="fix" onClick="setDistr(\'fix\', \''.$lang['admin_events_firstjackpotfixfield'].'\')" '.$sel['fixshares'].'>'.$lang['admin_events_settings_stakemode_fix'].'</option>
						<option value="lin" onClick="setDistr(\'lin\')" '.$sel['linshares'].'>'.$lang['admin_events_settings_distr_lin'].'</option>
						<option value="exp" onClick="setDistr(\'exp\')" '.$sel['expshares'].'>'.$lang['admin_events_settings_distr_exp'].'</option>
					</select></div>

				<div id="fixsharesexp" class="explanation '.$nt['fixshares'].'">'.substitute($lang['admin_events_fixshares'], $lang['admin_events_settings_stakemode_fix']).'</div>
				<div id="fixsharesdiv" class="input '.$nt['fixshares'].'"><input id="fixsharesfield" name="jp_distr_fix_shares" size="17" onclick="setDistr(\'fix\')" value="'.$eve['jp_distr_fix_shares'].'"></div>

				<div id="expsharesexp" class="explanation '.$nt['expshares'].'">'.$lang['admin_events_expshares'].'</div>
				<div id="expsharesdiv" class="input '.$nt['expshares'].'"><input id="expsharesfield" name="jp_distr_exp_value" size="3" value="'.$eve['jp_distr_exp_value'].'"></div>
				</div><!-- end jackpotsection-->


				<div class="title">'.$lang['admin_events_betuntil'].'</div>
				<div class="explanation">'.$lang['admin_events_betuntilexp'].'</div>
				<div class="input">
					<input id="betuntilfield" name="betuntil_nb" value="'.$val['betuntil_nb'].'" size="4" />
					<select name="betuntil_time">
						<option value="m" '.$sel['betuntil_time_m'].'>'.$lang['general_minute_s'].'</option>
						<option value="h" '.$sel['betuntil_time_h'].'>'.$lang['general_hour_s'].'</option>
						<option value="d" '.$sel['betuntil_time_d'].'>'.$lang['general_day_s'].'</option>
					</select>
					'.$lang['general_before'].'
					<select name="betuntil_before">
						<option value="m" '.$sel['betuntil_before_m'].'>'.$lang['general_match'].'</option>
						<option value="t" '.$sel['betuntil_before_t'].'>'.$lang['admin_events_bettinggameinitiation'].'</option>
					</select>
				</div>


				<div class="title">'.$lang['admin_events_beton'].'</div>
				<div class="explanation">'.$lang['admin_events_betonexp'].'</div>
				<div class="input"><select id="betonfield" name="bet_on" size=2>
						<option value="results" onClick="setPoints(\'results\')" '.$sel['betonresults'].'>'.$lang['admin_events_score'].'</option>
						<option value="toto" onClick="setPoints(\'toto\')" '.$sel['betontoto'].'>'.$lang['admin_events_toto'].'</option>
					</select></div>


				<div class="explanation '.$nt['inputtype'].'" id="inputtypeexp">'.$lang['admin_events_inputtypeexp'].'</div>
				<div class="input '.$nt['inputtype'].'" id="inputtypefield"><select name="score_input_type" size=2>
						<option value="results" '.$sel['inputtyperes'].'>'.$lang['admin_events_score'].'</option>
						<option value="toto" '.$sel['inputtypetoto'].'>'.$lang['admin_events_toto'].'</option>
					</select></div>

				<div class="title">'.$lang['admin_events_pointdistr'].'</div>
				<div id="correctresult" class="explanation">'.$lang['admin_events_pointdistr_correct'].'</div>
				<div id="correcttoto" class="explanation notvisible">'.$lang['admin_events_pointdistr_toto'].'</div>
				<div class="input"><input id="correct" name="p_correct" size="4" '.$dis['correct'].' value="'.$eve['p_correct'].'"></div>
				<div class="explanation">'.$lang['admin_events_pointdistr_diff'].'</div>
				<div class="input"><input id="diffbox" name="diffbox" type="checkbox" onChange="switchInput(\'diff\')" '.$check['diff'].' '.$dis['diffbox'].'>
					<input id="diff" name="p_diff" size="4" '.$dis['diff'].' value="'.$eve['p_diff'].'"></div>
				<div class="explanation">'.$lang['admin_events_pointdistr_almost'].'</div>
				<div class="input"><input id="almostbox" name="almostbox" type="checkbox" onChange="switchInput(\'almost\')" '.$check['almost'].' '.$dis['almostbox'].'>
					<input id="almost" name="p_almost" size="4" '.$dis['almost'].' value="'.$eve['p_almost'].'"></div>
				<div class="explanation" >'.$lang['admin_events_pointdistr_wrong'].'</div>
				<div class="input"><input id="wrong" name="p_wrong" size="4" '.$dis['wrong'].' value="'.$eve['p_wrong'].'"></div>

				<div class="explanation" >'.$lang['admin_events_komatchesexp'].'</div>
				<div class="input"><select name="ko_matches" size=3>
						<option value="no" onClick="koMatches(\'no\')" '.$sel['ko_no'].'>'.$lang['general_no'].'</option>
						<option value="yes" onClick="koMatches(\'yes\')" '.$sel['ko_yes'].'>'.$lang['general_yes'].'</option>
						<option value="only" onClick="koMatches(\'only\')" '.$sel['ko_only'].'>'.$lang['general_only'].'</option>
					</select></div>

				<div class="explanation '.$nt['enable_tie'].'" id="tietoughexp">'.$lang['admin_events_tietough'].'</div>
				<div class="input '.$nt['enable_tie'].'" id="tietoughdiv"><select id="tietoughfield" name="enable_tie" size=2>
						<option value="yes" onClick="tietough(\'yes\')" '.$sel['tie_yes'].'>'.$lang['general_yes'].'</option>
						<option value="no" onClick="tietough(\'no\')" '.$sel['tie_no'].'>'.$lang['general_no'].'</option>
					</select></div>

				<div class="explanation '.$nt['afterpenalty'].'" id="afterpenaltyexp">'.$lang['admin_events_afterpenalty'].'</div>
				<div class="input '.$nt['afterpenalty'].'" id="afterpenaltydiv"><select name="ap_score" size=2>
						<option value="addall" '.$sel['ap_all'].'>'.$lang['admin_events_afterpenalty_addall'].'</option>
						<option value="addone" '.$sel['ap_one'].'>'.$lang['admin_events_afterpenalty_addone'].'</option>
					</select></div>

				<div class="submit"><input type="button" onclick="javascript: verify();" value="'.$lang['general_savechanges'].'"></div>
		</div>
		</form>';
    }else{
        $body .= infoBarEventCreation(3,3);
        $sslink = preg_replace('/ssubmenu=settings/', 'ssubmenu=matches', $link.$link_query);
        $body .=  substitute($lang['admin_events_phase3text'], array($sslink, $lang['admin_events_deadline']));

        $flcnt = generateEventInfo($_REQUEST['ev']);
        foreach($flcnt as $sid => $cnt)
            $body .=  makeFloatingLayer($events['u']['e'.$id]['name'], $cnt, 1, $_REQUEST['ev'].'_'.$sid);
        $body .=  '<p/><div align="center"><b><a href="javascript: showFloatingLayer(\''.$_REQUEST['ev'].'_stake\')">'.$lang['admin_events_displayinfo'].'</a></b></div>';

        //Preparation for the form
        //=> process public setting
        if($eve['public']){
            $p['y'] = 'selected="selected"';
            $p['n'] = '';
        }else{
            $p['n'] = 'selected="selected"';
            $p['y'] = '';
        }


        //the form
        $body .=  '<form name="phase3" action="?menu=admin&submenu=events&evac=save" method="POST">'
            .'<input type="hidden" name="id" value="'.$selectedEvent->getId().'">'.$u['h']
            .'<input type="hidden" name="form" value="ssubmenu=settings&'.$link_query.'">'
            .'<input type="hidden" name="formname" value="phase3">'
            .'<div class="showform">
				<div class="title">'.$lang['admin_events_name'].'</div>
				<div class="input"><input name="name" size=20 value="'.$eve['name'].'"</div>
				<div class="title">'.$lang['admin_events_public'].'</div>
				<div class="input">
					<select name="public" size=2>
						<option value="1" '.$p['y'].'>'.$lang['general_yes'].'</option>
						<option value="0" '.$p['n'].'>'.$lang['general_no'].'</option>
					</select>
				</div>
				<div class="title">'.$lang['admin_events_deadline'].'</div>
				<div class="input"><input id="deadlinefield" name="deadline" size=10 value="'.date('d.m.Y', $eve['deadline']).'"></div>
				<div class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"></div>
		</div>';
        $body .=  '</form>';


    }
//========== activate an event
//	=> in admin_events_actions.php
}


$xajax->processRequest();
$xajax->printJavascript();

function manageuser($user,$what) {
    global $events_test,$settings;
    $e = $events_test->getEventById($_REQUEST['ev']);
    $bool = ($e->manageUsers($user,$what));

    $response = new xajaxResponse();


    if ($bool) {
        $src = 'src/style_'.$settings['style'].'/img/icon_ok.png';
    } else {
        $src = 'src/style_'.$settings['style'].'/img/icon_not_ok.png';
    }

    $image = " <img src=".$src." width = '20px' height= '20px' />";
    $response->assign('savestatus_'.$id,'innerHTML', $image);
    return $response;
}

$body .= "<script type='text/javascript' charset='UTF-8'>
            /* <![CDATA[ */

				function userReimbursed(pos,neg,id) {
					var elementToSwap = document.getElementById(id+'_reimbursed');
					var toClass = '';
					var toString = '';

					if (elementToSwap.innerHTML==pos) {
						elementToSwap.innerHTML = neg;
						elementToSwap.className = 'negative';
						xajax_manageuser(id,'notreimbursed');
					} else {
						elementToSwap.innerHTML = pos;
						elementToSwap.className = 'positive';
						xajax_manageuser(id,'reimbursed');
					}
				}


				function userPaid(pos,neg,id) {
					var elementToSwap = document.getElementById(id+'_paid');
					var toClass = '';
					var toString = '';

					if (elementToSwap.innerHTML==pos) {
						elementToSwap.innerHTML = neg;
						xajax_manageuser(id,'notpaid');
					} else {
						elementToSwap.innerHTML = pos;
						elementToSwap.className = 'positive';
						xajax_manageuser(id,'paid');
					}
				}

           /* ]]> */
           </script>";
