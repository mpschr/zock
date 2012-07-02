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
global $db, $settings, $langs;

if($_REQUEST['setac'] == 'savesettings'){

	$data = $_POST;

	//test what's important to have a specific type/structure
	if(!(is_numeric($data['formlines']))) $data['formlines'] = $settings['formlines'];
	if(!($data['email1'] == $data['email2'] && stristr($data['email1'], '@') != FALSE))
		$data['email1'] = $data['email2'] = $settings['email'];
	if(!isset($data['lsel'])) $data['lsel'] = $_SESSION['dlang'];
	
	$update = array (	'lang' => $data['lsel'],
				'name' => $data['name'],
				'description' => $data['description'],
				'formlines' => $data['formlines'],
				'email' => $data['email1'],
				'style' => $data['style'],
				'account_type' => $data['account_type'],
				'account_details' => $data['account_details'],
				'account_holder' => $data['account_holder'],
				'site_url' => $data['site_url'],
				'notify_newaccount' => $data['notify_newaccount'],
				'notify_participate' => $data['notify_participate'],
				'notify_withdraw' => $data['notify_withdraw'],
				'notification_system' => $data['notification_system']);
	//the rest can be updated as it is
	foreach ($update as $setting => $value)
		$db->query("UPDATE ".PFIX."_settings SET value = '".$value."' WHERE setting = '".$setting."';");

	//language install & remove
	foreach ($data as $l=>$v){
		if(substr($l, 0,4) == 'lang' && $v=='on'){
			if(substr($l,4,1) == 'i')
				installLang(str_replace('langi_', '', $l));
			elseif(substr($l,4,1) == 'r')
				$db->query("DELETE FROM ".PFIX."_langs WHERE `short` = '".str_replace('langr_', '', $l)."' LIMIT 1;");
		}
	}

	redirect($rlink);
//	print_r($_POST);

}elseif($_REQUEST['setac']=='savesmtp'){


	$buffer = Array();
	$newbuffer = Array();
	$handle = @fopen("src/vars.php", "r");
	if ($handle) {
	    while (!feof($handle)) {
		$buffer[] = fgets($handle, 4096);
	    }
	    fclose($handle);
	}
	foreach ($buffer as $line){
		if(preg_match('/\$my_smtp\[\'[a-z]+\'\]/', $line)){
			$what = preg_replace('/(\$my_smtp\[\')([a-z]+)(\'\]\ =\ ).+/', '\\2', $line);
			$newline = preg_replace('/(\$my_smtp\[\')([a-z]+)(\'\]\ =\ ).+(;.+)/', '\\1\\2\\3\''.$_POST[$what].'\'\\4', $line);
		}else{
			$newline = $line;
		}
		$newbuffer[] = $newline;
	}


	$handle = fopen("src/vars.php" , "w");
		foreach($newbuffer as $line){
			$body .= $line;
			fwrite($handle, $line);
		}
	fclose($handle);

	$subject = $lang['admin_settings_testemail_subject'];
	$txt = $lang['admin_settings_testemail_txt'];

	include("src/opensource/phpmailer/class.phpmailer.php");
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = "ssl";
	$mail->Host = $_POST['host'];
	$mail->Port = 465;
	$mail->Username = $_POST['username'];
	$mail->Password = $_POST['pw'];
	$mail->WordWrap = 50;
	$mail->AddReplyTo($settings['email'], $lang['general_bettingOffice']." ".$settings['name']);
	$mail->From = $_POST['from']; 
	$mail->FromName =  $lang['general_bettingOffice']." ".$settings['name'];
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->MsgHTML($txt);
	$mail->AddAddress($settings['email'], $lang['general_bettingOffice']." ".$settings['name']);
    $mail->SMTPDebug  = 1;
	if(!$mail->Send()){
		$db->query("UPDATE ".PFIX."_settings SET value = 'false' WHERE setting = 'functionalSMTP';");
		$body .= '<font class="error">'.$lang['admin_settings_mailnotsent'].'</font>';
	}else{
		$db->query("UPDATE ".PFIX."_settings SET value = 'true' WHERE setting = 'functionalSMTP';");
		$body .= substitute($lang['admin_settings_mailsent'], $settings['email']);
	}

}else{
	$body .= $lang['admin_settings_content'];

	//preparations

	$settings['notify_newaccount'] = ($settings['notify_newaccount'] == 'true') ? 'checked' : '';
	$settings['notify_participate'] = ($settings['notify_participate'] == 'true') ? 'checked' : '';
	$settings['notify_withdraw'] = ($settings['notify_withdraw'] == 'true') ? 'checked' : '';
	if ($settings['functionalSMTP']!='true') {
		$notification_system['internal'] = 'checked';
	}else{
		$notification_system['email'] = ($settings['notification_system'] == 'email') ? 'checked' : '';
		$notification_system['internal'] = ($settings['notification_system'] == 'internal') ? 'checked' : '';
	}

	//the form
	$body .= '<div class="showform">';
	$body .= '<form name="settings" method="POST" action="'.$link.'setac=savesettings">';

		//=> Name of the site/bet office
		$body .= '<div class="title">'.$lang['admin_settings_name'].':</div>';
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['admin_settings_nametext'], 40)).'</div>';
			$body .= '<div class="input"><input name="name" size="15" value="'.$settings['name'].'"></div>';

		//=> slogan/description of the site/bet office
		$body .= '<div class="title">'.$lang['admin_settings_description'].':</div>';
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['admin_settings_descriptiontext'], 40)).'</div>';
			$body .= '<div class="input"><input name="description" size="30" value="'.$settings['description'].'"></div>';
	
			//=> bank account details
		$body .= '<div class="title">'.$lang['general_bank_account'].':</div>';
			$body .= '<div class="explanation">'.$lang['admin_settings_bank_account_text'].'<br/>'.$lang['admin_settings_bank_account_text1'].'</div>';
			$body .= '<div class="input"><input name="account_type" size="30" value="'.$settings['account_type'].'"/></div>';
			$body .= '<div class="explanation">'.$lang['admin_settings_bank_account_text2'].'</div>';
			$body .= '<div class="input"><input name="account_details" size="30" value="'.$settings['account_details'].'"/></div>';
			$body .= '<div class="explanation">'.$lang['admin_settings_bank_account_text3'].'</div>';
			$body .= '<div class="input"><textarea name="account_holder" rows="4" cols="30">'.$settings['account_holder'].'</textarea></div>';


		//=> the language
		$body .= '<div class="title">'.$lang['general_language'].'</div>';
		if(sizeof($langs['short'])>1){
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['admin_settings_lang'], 40)).'</div>';
			$select = makeLangSelect($settings['lang']);
			$body .= '<div class="input">'.$select.'</div>';
		}
		?><script type="text/javascript">
			function showLangList(){
				var c = document.getElementById("langlistdiv").getAttribute("class");
				if(c=="input notvisible"){
					document.getElementById("langlistdiv").setAttribute("class", "input");
				}else{
					document.getElementById("langlistdiv").setAttribute("class", "input notvisible");
				}
		}
		</script><?
		//=> install language
		$body .= '<div>'.$lang['admin_settings_installlang'].'<br/>
				<a href="javascript: showLangList()">'.$lang['admin_settings_langlist'].'</a></div>';
		$body .= '<div class="input" id="langlistdiv">';
		$langdircnt = scandir('data/langs');
		$body .= '<b>'.$lang['general_remove'].':</b><br/>';
		for ($x = 0 ; $x < sizeof($langs['short']) ; $x++){
			if($langs['short'][$x] != $_SESSION['dlang'])
				$body .= $langs['long'][$x].' <input type="checkbox" name="langr_'.$langs['short'][$x].'" /><br/>'; 
		} 
		$body .= '<br/><b>'.$lang['general_install'].':</b><br/>';
		foreach ($langdircnt as $el){
			if (is_file('data/langs/'.$el) && substr($el, -3) == 'xml'){
				$l_short = substr($el, 5);
				$l_short =  str_replace('.xml', '', $l_short);
				if (!in_array($l_short, $langs['short'])){
					$l_long = lookupLangName($l_short);
					$body .= $l_long.' <input type="checkbox" name="langi_'.$l_short.'" /><br/>'; 
				}
			}
		}
		$body .= '</div>';

		//=> the style
		$body .= '<div class="title">'.$lang['admin_settings_style'].'</div>';
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['admin_settings_styletext'], 40)).'</div>';
			$selectStyle = makeStyleSelect();
			$body .= '<div class="input">'.$selectStyle.'</div>';

		//=> the url of the site
		$body .= '<div class="title">'.$lang['admin_settings_siteurl'].':</div>';
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['myprofile_settings_siteurltext'], 40)).'</div>';
			$body .= '<div class="input"><input name="site_url" size="30" value="'.$settings['site_url'].'"></div>';


		//=> the email of the "owner"
		$body .= '<div class="title">'.$lang['register_email'].':</div>';
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['myprofile_settings_changeemail'], 40)).'</div>';
			$body .= '<div class="input"><input name="email1" size="30" value="'.$settings['email'].'"></div>';
			$body .= '<div class="input"><input name="email2" size="30" value=""></div>';
		//=> sending of emails activated?
		if($settings['functionalSMTP'] == 'true'){
			$body .= '<div class="input">'.$lang['admin_settings_smtpactivated'];
			$body .= '<br/><a href="javascript: showFloatingLayer(\'smtpdetails\')">'.$lang['admin_settings_modifysmtp'].'</a></div>';
		}else{
			$body .= '<div class="input">'.$lang['admin_settings_smtpdisactivated'];
			$body .= '<br/><a href="javascript: showFloatingLayer(\'smtpdetails\')">'.$lang['admin_settings_modifysmtp'].'</a></div>';

		}

	
		//=> notifications 
		$body .= '<div class="title">'.$lang['admin_settings_notifications'].':</div>';
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['admin_settings_notificationstext'], 40)).'</div>';
			$body .= '<div class="input"><input type="checkbox" name="notify_newaccount" value="true" '.$settings['notify_newaccount'].'>'.$lang['admin_settings_notify_newaccount'].'<br/>';
			$body .= '<input type="checkbox" name="notify_participate" value="true" '.$settings['notify_participate'].'>'.$lang['admin_settings_notify_participate'].'<br/>';
			$body .= '<input type="checkbox" name="notify_withdraw" value="true" '.$settings['notify_withdraw'].'>'.$lang['admin_settings_notify_withdraw'].'</div>';
			$body .= '<div class="input"><input type="radio" name="notification_system" value="internal" '.$notification_system['internal'].'>'.$lang['admin_messages_internal'];
			$body .= ($settings['functionalSMTP']=='true') ? 
				'<input type="radio" name="notification_system" value="email" '.$notification_system['email'].'/>'.$lang['register_email'].'</div>' :
				'</div>';

		//=> formlines to be displayed
		$body .= '<div class="title">'.$lang['admin_settings_formlines'].':</div>';
			$body .= '<div class="explanation">'.nl2br(wordwrap($lang['admin_settings_formlinestext'], 40)).'</div>';
			$body .= '<div class="input"><input name="formlines" size="5" value="'.$settings['formlines'].'"></div>';
		$body .= '<div class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"></div>';
	$body .= '</form>';

	$body .= '</div>';
		$flcnt = '<form name="smtp" method="POST" action="'.$link.'setac=savesmtp">';
		$flcnt.= '<div >'.$lang['admin_settings_smtpexp'].'</div>';
		$flcnt.= '<div class="title">'.$lang['admin_settings_smtpserver'].'
			<font class="explanation">('.$lang['general_eg'].' smtp.googlemail.com)</font></div>';
		$flcnt.= '<div class="input"><input name="host" size="20" value=""></div>';
		$flcnt.= '<div class="title">'.$lang['admin_settings_smtplogin'].'
			<font class="explanation">('.$lang['general_eg'].' youremail@googlemail.com)</font></div>';
		$flcnt.= '<div class="input"><input name="username" size="20" value=""></div>';
		$flcnt.= '<div class="title">'.$lang['myprofile_password_title'].'</div>';
		$flcnt.= '<div class="input"><input name="pw" size="20" value="" autocomplete="off"></div>';
		$flcnt.= '<div class="title">'.$lang['admin_settings_fromaddress'].'</div>';
		$flcnt.= '<div class="explanation">'.$lang['admin_settings_fromaddressexp'].'</div>';
		$flcnt.= '<div class="input"><input name="from" size="20" value=""></div>';
		$flcnt.= '<div class="title">'.$lang['admin_settings_smtpport'].'</div>';
		$flcnt.= '<div class="explanation">'.$lang['admin_settings_smtpportexp'].'</div>';
		$flcnt.= '<div class="input"><input name="port" size="20" value=""></div>';
		$flcnt.= '<div class="submit"><input type="submit" value="'.$lang['general_savechanges'].'"/></div>';
		$flcnt.= '</form>';

		$body .= makeFloatingLayer('SMTP', $flcnt, 1, 'smtpdetails');		
}
?>
