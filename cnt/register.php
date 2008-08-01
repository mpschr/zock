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

echo '<h2>'.$lang['register_title'].'</h2>';
global $settings, $db, $my_smtp;



//================ register

//get the infos and register the user (if everything's ok)
if (isset($_REQUEST['verify'])){

	$data = $_POST;

	//check if the two email adresses are the same and there's at leastn an '@'
	if($data['username'] != NULL && $data['eMail1']==$data['eMail2'] && substr_count($data['eMail1'], '@') == 1){

		//does this user already exist?
		$alreadyregistered_raw = $db->query("SELECT login,email FROM ".PFIX."_users ;");
		foreach($alreadyregistered_raw as $d){
			$exists['l'][] = $d['login'];
			$exists['e'][] = $d['email'];
		}

		if(strlen($data['username']) > 15){
			//username too long
			$error = 'register_logintoolong';
		}
		
		if (in_array($data['username'],$exists['l'])){
			//yees, this username's taken
			$error = 'register_exists';
		}
			//overrides usernameerror
		if (in_array($data['eMail1'],$exists['e'])){
			//yees, this email exists taken
			$error = 'register_alreadyaccount';
		}

		if(!isset($error)){
			//everythinks's ok, register the new user
			$pw = generatePassword();
			
			//encrypt it
			$pwe = crypt($pw);
			$query = "INSERT INTO ".PFIX."_users
					(login, pw, email, lang)
					VALUES ('".$data['username']."', '".$pwe."', '".$data['eMail1']."', '".$_SESSION['dlang']."');";
//			if (true){
			if ($db->query($query)){
				$text = $lang['home_welcome'].', '.$data['username']."\n\n\n"
					.$lang['register_username'].' '.$data['username']."\n"
					.$lang['login_yourpw'].' '.$pw;

				if($settings['functionalSMTP'] == 'true'){
					//send an email
					$mail = initMail();
					$mail->From = $my_smtp['from'];
					$mail->FromName = $lang['general_bettingOffice'].' '.$settings['name'];
					$mail->Subject = $lang['register_emailsubject'];
					$mail->Body = $text;
					$mail->AddAddress($data['eMail1']);
					if(!$mail->Send()) {
		 				echo "<br/>Mailer Error: " . $mail->ErrorInfo;
					} else {
						echo '<p/>'.$lang['register_ok'];
					}

				}else{
					echo '<div class=appearance>'.$lang['error_nofunctionalsmtp'].' 
						'.$lang['register_emailhere'].'
						<p/><font class=error>'.nl2br($text).'</font></div>';
				}
				//notifiacation?

				if($settings['notify_newaccount'] == 'true'){
					$notification = $lang['register_newaccounttext'].'<br/>
							'.$lang['register_username'].' '.$data['username'].'<br/>
							'.$lang['register_email'].': '.$data['eMail1'];
					notify($lang['register_newaccount'], $notification);
				}
			}else{
				//set error
				$error = 'register_save';
			}
 		}
	}else{
		//set error
		$error = 'register';
	}

	if (isset($error)){
		//go back with error mesage
		redirect('register&error='.$error);
	}


//========== register form
}else{
	echo $lang['register_content'];

	if (isset($_REQUEST['error']))
		echo '<p><font class="error">'.$lang['error_'.$_REQUEST['error']].'</font><p>';
?>

<form name="register" action="?menu=register&verify=1" method="POST">
	<div class="showform">
		<div class="title"><? echo $lang['register_username']; ?></div>
		<div class="input"><input name="username" size=10></div>
		<div class="title"><? echo $lang['register_email']; ?> (2x):</div>
		<div class="input"><input name="eMail1" size=30></div>
		<div class="input"><input name="eMail2" size=30></div>
		<div class="submit"><input type="submit" value="<?echo $lang['register_title']; ?>"></div>
	</div>
<form>
<?php
}
?>
