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
if(isset($_REQUEST['installnow'])){

	global $my_db;

	$err = Array();
	$data = $_POST;

	$my_db['host'] = $data['dbhost'];
	$my_db['user'] = $data['dbusername'];
	$my_db['pass']= $data['dbpassword'];
	$my_db['db'] = $data['dbname'];
	$my_db['prefix'] = $data['dbprefix'];


	$connection = @mysql_connect($my_db['host'],$my_db['user'],$my_db['pass']);
	if (!$connection){
		$err['server']=1;
	}else{
		if(!@mysql_select_db($my_db['db'],$connection)) {
			$err['table']=1;
		}
	}

	if($_POST['dbprefix']=="") $err['prefix']=1;

	if($_POST['useremail']=="") $err['email']=1;
	if($_POST['username']=="") $err['username']=1;


	@chmod('src/vars.php', 777);
	if(!is_writable('src/vars.php')) $err['varsnotwritable']=1;

	if(!is_writable('installation')) @chmod('installation', 777);
	if(!is_writable('installation')) $err['installationnotwritable']=1;
	
	if(sizeof($err)==0){


		echo 'writing src/vars.php<br/>';

		$buffer = Array();
		$newbuffer = Array();
		$handle = @fopen("installation/vars_template.php", "r");
		if ($handle) {
		    while (!feof($handle)) {
			$buffer[] = fgets($handle, 4096);
		    }
		    fclose($handle);
		}

	
		foreach ($buffer as $line){
			if(ereg('\$my_db\[\'[a-z]+\'\]', $line)){
				$what = ereg_replace('(\$my_db\[\')([a-z]+)(\'\]\ =\ )\'\'.+', '\\2', $line);
				$newline = ereg_replace('(\$my_db\[\')([a-z]+)(\'\]\ =\ ).+(;.+)', '\\1\\2\\3\''.$my_db[$what].'\'\\4', $line);
			}else{
				$newline = $line;
			}
			$newbuffer[] = $newline;
		}

		$handle = fopen("src/vars.php" , "w");
			foreach($newbuffer as $line){
				echo $line;
				fwrite($handle, $line);
			}
		fclose($handle);
		@chmod('src/vars.php', 744);

		echo 'creating tables<br/>';
		
		$sqlbuffer = Array();
		$newsqlbuffer = Array();
		$handle = @fopen("installation/tables.sql", "r");
		if ($handle) {
			$index = 0;
		    while (!feof($handle)) {
			$read = fgets($handle, 4096);
			if ( preg_match("/^\\s*(insert|create) /i",$read) ) {
				$read = ereg_replace('(.+)(#PFIX#)(.+)', '\\1'.$my_db['prefix'].'\\3', $read);
				$index++;
			}
			//filter out comments
			if (substr($read, 0,1) != '-') $sqlqueries[$index] .= $read;
		    }
		    fclose($handle);
		}
		unset($sqlqueries[0]);
		$dbinstance = new bDb();
		foreach($sqlqueries as $sqlquery){
//			echo 'newquery<pre>'.$sqlquery.'</pre>';
			$dbinstance->query($sqlquery);
		}

		$dbinstance->query("INSERT INTO ".$my_db['prefix']."_settings 
					(setting, value) VALUES 
					('lang', '".$_SESSION['dlang']."'), 
					('description', '".$data['zockdesc']."'), 
					('name', '".$data['zockname']."') ;");

		

		echo 'creating user account (name: '.$data['username'].' password: admin)<br/>';
		$dbinstance->query("INSERT INTO ".$my_db['prefix']."_users 
					(login, pw, email,lang) VALUES 
					('".$data['username']."', '".crypt('admin')."', '".$data['useremail']."', '".$_SESSION['dlang']."') ;");

		$handle = fopen("installation/installationwassuccess.txt", "w");
			fwrite($handle, 'delete this file or the directory installation!!');
		fclose($handle);
	}
}


if(file_exists('installation/installationwassuccess.txt')){

	echo indication('The installation of zock! was a success! In order to continue delete the directory "installation/" with all its contents');

}elseif(!isset($_REQUEST['installnow']) || isset($_REQUEST['installnow']) && sizeof($err)>0){


	if(!isset($_REQUEST['installnow'])){
		echo $lang['instl_welcome'];
		echo indication($lang['instl_exp']);
	}


	if(sizeof($err)>0){
		foreach ($err as $name=>$bool)
			echo errorMsg('instl_err_'.$name,1);
	}else{

		@chmod('src/vars.php', 777);
		if(!is_writable('src/vars.php')) echo errorMsg('instl_err_varsnotwritable',1);

		if(!is_writable('installation')) @chmod('installation', 777);
		if(!is_writable('installation')) echo errorMsg('instl_err_installationnotwritable',1);
	}

	echo '<form name="installing" action="?installnow=1" method="POST">';
	echo '<div class="showformleft">';
	echo '<div class="title">'.$lang['instl_zock_title'].'</div>';
	echo '<div class="explanation">'.$lang['instl_zock_name'].'</div>';
	echo '<div class="input"><input size="20" name="zockname"></div>';
	echo '<div class="explanation">'.$lang['instl_zock_desc'].'</div>';
	echo '<div class="input"><input size="20" name="zockdesc"></div>';

	echo '<div class="title">'.$lang['instl_user_title'].'</div>';
	echo '<div class="explanation">'.$lang['instl_user_exp'].'</div>';
	echo '<div class="input"><input size="20" name="username"></div>';
	echo '<div class="explanation">'.$lang['instl_user_email'].'</div>';
	echo '<div class="input"><input size="20" name="useremail"></div>';
	echo '</div>';


	echo '<div class="showformright">';
	echo '<div class="title">'.$lang['instl_db_title'].'</div>';
	echo '<div class="explanation">'.$lang['instl_db_host'].'</div>';
	echo '<div class="input"><input size="20" name="dbhost"></div>';
	echo '<div class="explanation">'.$lang['instl_db_username'].'</div>';
	echo '<div class="input"><input size="20" name="dbusername"></div>';
	echo '<div class="explanation">'.$lang['instl_db_password'].'</div>';
	echo '<div class="input"><input type="password" size="20" name="dbpassword"></div>';
	echo '<div class="explanation">'.$lang['instl_db_name'].'</div>';
	echo '<div class="input"><input size="20" name="dbname"></div>';
	echo '<div class="explanation">'.$lang['instl_db_prefix'].'</div>';
	echo '<div class="input"><input size="20" name="dbprefix" value="zock"></div>';
	echo '</div>';
	echo '</p><div class="clearfloat"></div>';
	echo '<div class="submit"><input type="submit" value="'.$lang['instl_installnow'].'"/>'.'</div>';
	echo '</form>';
}

?>

