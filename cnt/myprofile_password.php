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


if(isset($_REQUEST['ac'])){

	//update
	global $db;

	//get the password
	$dbpw = $db->query('SELECT pw FROM '.PFIX.'_users WHERE id = "'.$_SESSION['userid'].'"');
	$data=$_POST;

	//condition: the two new ones must be the same & and the old must pass the test with the in the db
	if($data['new1'] != NULL && $data['new1'] == $data['new2'] && crypt($data['old'], $dbpw[0]['pw']) == $dbpw[0]['pw']){

		//update the new one
		$query = "UPDATE ".PFIX."_users SET pw='".crypt($data['new1'])."' WHERE id = '".$_SESSION['userid']."'";
		if($db->query($query)){
		
			echo $lang['general_savedok'].'<br>';
			echo $lang['general_redirect'];
			redirect($rlink,3);
			
		}else{

			echo $lang['general_savednotok'];
			redirect($rlink.'error=1',1);

		}

	}else{
		redirect($rlink.'error=1');
	}

}else{

	if (isset($_REQUEST['error'])) echo '<font class=error>'.$lang['error_myprofile_password'].'</font><p>';
	
	echo $lang['myprofile_password_content'];
	
	//the form
	echo '<form name="password" action="'.$link.'ac=verify" method="POST">
		<table class="showform"><tr>
			<td class="title">'.$lang['myprofile_password_old'].'</td>
		</tr><tr>
			<td class="input"><input type="password" size="10" name="old"></td>
		</tr><tr >
			<td class="title">'.$lang['myprofile_password_new'].'</td>
		</tr><tr>
			<td class="input"><input type="password" size="10" name="new1"></td>
		</tr><tr>
			<td class="input"><input type="password" size="10" name="new2"></td>
		</tr><tr>
			<td class="input"><input type="submit" value="'.$lang['general_savechanges'].'"></td>
		</tr></table>
	</form>';
}
?>
