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
global $db, $settings, $events, $my_smtp;

if(isset($_REQUEST['sendmessage'])){

	$data = $_POST;

	if($data['system'] == "internal"){
		$query = "INSERT INTO ".PFIX."_messages (author, receivers, time, title, content) 
						VALUES ('".$_SESSION['userid']."', 
						'".$data['hf_receivers']."',
						'".time()."',
						'".$data['title']."',
						'".$data['content']."')";
		$db->query($query);
	}else{
		$receivers = preg_split('/:/', $data['hf_receivers']);
		array_pop($receivers);
		foreach ($receivers as $r){
			//send the user a mail to notify him of his approval
			$uinfo = loadSettings($r);
			$ainfo = loadSettings($_SESSION['userid']);
			$ulang = languageSelector($uinfo['lang']);
			$from = $settings['email'];
			$to = $uinfo['email'];
			$subject = $data['title'];
			$text = $data['content'];

			$mail  = initMail();
			$mail->AddReplyTo($ainfo['email'], $ainfo['name']." ".$ainfo['famname']);
			$mail->From = $my_smtp['from']; 
			$mail->FromName =  $ulang['general_bettingOffice']." ".$settings['name'];
			$mail->Subject = $subject;
			$mail->Body = $text;
			$mail->AddAddress($to, $uinfo['name']." ".$uinfo['famname']);
			if(!$mail->Send()) {
 				$body .=  "<br/>Mailer Error: " . $mail->ErrorInfo;
			} else {
				$body .=  "<br/>".$to.": Message sent!";
			}


//			$body .=  $to.'<br/>'.$subject.'<br/>'.$text.'<br/>'.$headers.'<br/>'.$x;
		}
	}
//	print_r($data);
//	redirect($rlink);

}else{

	$body .=  $lang['admin_messages_content'];
	
	//get all users
	$usersraw = $db->query("SELECT id, login, name, famname FROM ".PFIX."_users");

	$flcnt = '<a>'.$lang['participants_title'].'</a> | 
		<a href="javascript: changeFloatingLayer(\'singles\')">'.$lang['admin_messages_singleusers'].'</a><p/>';
		foreach($events['u'] as $event){
			$counter++;
			if($counter%2==0) 
				$flcnt .= '- <b>'.$event['name'].' </b>
				<a href="javascript: addReceivers(\''.$event['a'].'\', \''.$lang['general_none'].'\')">'
				.$lang['general_add'].'</a><br/>';
		}

	$body .=  makeFloatingLayer($lang['admin_messages_receivers'], $flcnt, 1, 'participants');


	$flcnt = '<a href="javascript: changeFloatingLayer(\'participants\')">'.$lang['participants_title'].'</a> | 
		<a >'.$lang['admin_messages_singleusers'].'</a><p/>';
	$flcnt .= '<form name="search" autocomplete="off">
			<input id="searchstr" 
				onkeyup="searchUsers(\''.sizeof($usersraw).'\', \''.$lang['general_none'].'\');" 
				name="search" value="" size="30"/><p/>
			</form>';
	$flcnt .= '<div id="results"></div>';
	$body .=  makeFloatingLayer($lang['admin_messages_receivers'], $flcnt, 1, 'singles');

	$body .=  '<div class="error" id="warning"></div>';
	$warnings = $lang['error_attention'].';'.$lang['admin_messages_notitle'].';'.$lang['admin_messages_nocontent'].';'.$lang['admin_messages_noreceivers']; 

	//the form
	$body .=  '<div class="showform">';
	$body .=  '<form name="message" method="POST" action="'.$link.'sendmessage=1">';
		//=> Name of the site/bet office
		$body .=  '<div class="title">'.$lang['comments_commenttitle'].'</div>';
			$body .=  '<div class="input"><input name="title" size="30" value=""></div>';
		$body .=  '<div class="title">'.$lang['general_content'].':</div>';
			$body .=  '<div class="input"><textarea name="content" rows="6" cols="50"></textarea></div>';
		$body .=  '<div class="title">'.$lang['admin_messages_system'].':</div>
				<div><input type="radio" name="system" value="internal" checked/>'.$lang['admin_messages_internal'];
				$body .=  ($settings['functionalSMTP']=='true') ? 
					'<input type="radio" name="system" value="email" />'.$lang['register_email'].'</div>' :
					'</div>';

		$body .=  '<div class="title">'.$lang['admin_messages_receivers'].': <a href="javascript: showFloatingLayer(\'participants\')">'.$lang['general_add'].'</a></div>';
			$body .=  '<div class="input" id="receivers">'.$lang['general_none'].'</div>';
			$body .=  '<input type="hidden" name="hf_receivers" id="hf_receivers" value=""/>' ;

			// hiddenfields for each user
			foreach($usersraw as $user)
				$body .=  '<input type="hidden" id="user_'.$user['id'].'" 
					value="'.$user['login'].' ('.$user['name'].' '.$user['famname'].')"/>';
		
		$body .=  '<div class="submit"><button onclick="javascript: checkMessage(\''.$warnings.'\')" >'.$lang['general_send'].'</button></div>';
	$body .=  '</form>';

	$body .=  '</div>';
}
?>
