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


global $cont;
//=========== the submenu
if(isset($_REQUEST['submenu'])){
	$body .=  '<h2>'.$cont->get('admin_'.$_REQUEST['submenu'].'_title').'</h2>';
	include_once('cnt/admin_'.$_REQUEST['submenu'].'.php');

//=========== the admin enter site
}else{
	$body .=  '<h2>'.$cont->get('admin_title').'</h2>';
	$body .=  $lang['admin_content'];
}

?>
