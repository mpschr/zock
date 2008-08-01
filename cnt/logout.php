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

echo '<h2>'.$lang['logout_title'].'</h2>';

//the logout!
session_destroy();

//goodbye & send to home
echo $lang['logout_content'];
redirect('home', 3);
?>
