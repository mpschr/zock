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

global $my_smtp;
echo '<h2>'.$lang['login_title'].'</h2>';

if (isset ($_REQUEST['check'])){

	if ($_REQUEST['check']==1 ){
		//this is the login, verify login data
		login($_POST['login'], $_POST['pw'], isset($_POST['rememberme']));
	}elseif($_REQUEST['check']==2){
		global $db;
		$user = $db->query("SELECT login,email,lang,name,famname FROM ".PFIX."_users WHERE email = '".$_POST['email']."';");
		if(sizeof($user) == 1){
			$pw = generatePassword();
			$query = "UPDATE ".PFIX."_users SET `pw` = '".crypt($pw)."' WHERE email = '".$_POST['email']."';";
			$db->query($query);
			$ulang = languageSelector($user[0]['lang']);
			$mail = initMail();
			$mail->From = $my_smtp['from'];
			$mail->FromName = $ulang['general_bettingOffice'].' '.$settings['name'];
			$mail->Subject = $ulang['login_userdatasubject'];
			$i = Array($ulang['general_bettingOffice'].' '.$settings['name'],
					$ulang['login_yourpw'].' '.$pw,
					$ulang['login_yourlogin'].' '.$user[0]['login'],
					$settings['site_url']);
			$mail->IsHTML(true);
			$mail->MsgHTML(substitute($ulang['login_userdatamail'],$i));
			$to = $user[0]['email'];
			$mail->AddAddress($to, $user[0]['name']." ".$user[0]['famname']);
			if(!$mail->Send()) {
 				echo "<br/>Mailer Error: " . $mail->ErrorInfo;
				$showform = 2;
			} else {
				echo "<br/>".substitute($lang['login_userdatasent'].'<br/>', $to);
				$showform = 1;
			}
		}else{
			echo errorMsg('nouserwiththisemail');
			$showform = 2;
		}
	}
}
if($showform>0 || !(isset($_REQUEST['check']))){

	if($_REQUEST['forgot']==1 || $showform == 2){

		if($settings['functionalSMTP'] == 'true'){
		echo $lang['login_askfordata'];
?>
			<form action="?menu=login&check=2" method="POST" name="loginform">
			<div class="showform">
					<div class="title"> <?echo $lang['register_email'];?> </div>
					<div class="input"> <input value="" name="email"></input> </div>
					<div class="submit"> <input type="submit" value="<? echo $lang['login_senddata'];?>" name="loginbutton"></div>
			</div>
<?
		}else{
			echo $lang['error_nofunctionalsmtp'].' '.$lang['general_writetoadmin'];
		}
	}elseif (!isset($_REQUEST['forgot']) || $showform == 1){
		// request login data
		echo $lang['login_content'];
		
		?>
		<form action="?menu=login&check=1" method="POST" name="loginform">
		<div class="showform">
				<div class="title"> <?echo $lang['login_yourlogin'];?> </div>
				<div class="input"> <input value="" name="login"></input> </div>
				<div class="title"> <?echo $lang['login_yourpw'];?> </div>
				<div class="input"> <input value=""type="password" name="pw"></input> </div>
				<div class="input"> <input value="" type="checkbox" name="rememberme"></input> <?echo $lang['login_rememberme']?></div>
				<div class="submit"> <input type="submit" value="<? echo $lang['login_title'];?>" name="loginbutton"></div>
				<div class="forgotpassword"><br/><a href="?menu=login&forgot=1"><?echo $lang['login_forgotpassword'];?></a></div>
		</div>
<?php
		}
}
?>
