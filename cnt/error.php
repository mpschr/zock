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

//display error mesage
echo errorMsg($_SESSION['error']);

//delete source
unset ($_SESSION['error']);

echo '<p />';

//give a logged user a link to go back home
if (isLogged()) echo $lang['error_content'];
?>
