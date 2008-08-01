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


//=========== the submenu
if(isset($_REQUEST['submenu'])){
	echo '<h2>'.$lang['admin_'.$_REQUEST['submenu'].'_title'].'</h2>';
	include_once('cnt/admin_'.$_REQUEST['submenu'].'.php');

//=========== the admin enter site
}else{
	echo '<h2>'.$lang['admin_title'].'</h2>';
	echo $lang['admin_content'];
}

?>
