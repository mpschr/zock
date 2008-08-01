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


//get the submenu
if(isset($_REQUEST['submenu'])){
	$sm = $_REQUEST['submenu'];
}


echo '<h2>'.$lang['myprofile_title'].'</h2>';

if(!isset($_REQUEST['submenu']))
	echo $lang['myprofile_content'];

//submeu title
echo '<h3>'.$lang['myprofile_'.$sm.'_title'].'</h3>';

//submenu
include_once('cnt/myprofile_'.$sm.'.php');
?>
