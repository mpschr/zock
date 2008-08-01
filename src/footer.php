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


this defines the lowest part of the site.

*/
global $db, $settings;
?>



<div id="footer_top">
<div id="footer_bottom">
<div id="footer_left">
<div id="footer_right">
<div id="footer">
<table class="footer">
<tr><th width="10%">
<?php
//contact
echo '@ <a class="footer" href="mailto:'.$settings['email'].'">'.$lang['footer_contact'].'</a>';
$url = 'index.php?'.$_SERVER['QUERY_STRING'];
$dialog = $lang['general_langchange'];
?>
</th><th id="servertime" width="80%" align="center">
<?
echo $lang['footer_server_time'].': '.date('H:i:s', time());
?>
</th><th class="footer" width="10%" align="right">
<form name="langSelect" action="">

<?// the form to change the language of the site?>
<? echo makeLangSelect($_SESSION['dlang'], 'lang', 'onchange="languageChange(\''.$url.'\', \''.$dialog.'\')"'); ?>

</form>
</th></tr>
</table>
</div><!-- footer div closed -->
</div>
</div>
</div>
</div>
</div><!-- motherdiv closed -->

<div id="version_license"> <a href="http://zock.sf.net">zock!</a> <? echo VERSION . ' | &copy; 2006-2008' ?> | <a href="http://opensource.org/licenses/gpl-3.0.html">GPLv3</a></div>

</div><!-- first closed -->
</body>
</html>
