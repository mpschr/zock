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

global $settings, $style;


$hour = (int) date('H', time());
$min = (int) date('i', time());
$sec = (int) date('s', time());


$additionalFuncitons = "";
if ($_REQUEST['menu'] == 'overview') $additionalFunctions .= ", overviewArrange()";
if (isset($_REQUEST['filter'])) $additionalFunctions .= ", filterChange(), showFilter()";
?>

<body onLoad="placeFloatingLayers(), sClock('<?echo $hour;?>','<?echo $min;?>','<?echo $sec;?>', '<?echo $lang['footer_server_time'];?>') <?echo $additionalFunctions;?>"> <!-- load the layer if one's constructed -->
<script type="text/javascript" src="src/opensource/wz_tooltip.js" ></script>


<?

 styleComment(); 

//plain view mode
if($style['plainviewcompatible']){
	 if (isset($_REQUEST['plain'])) $_SESSION['plain'] = $_REQUEST['plain'];
	 if ($_SESSION['plain']){
		 $uri = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&plain=0';
		 echo '<div id="plain"> <a href="'.$uri.'">'.$lang['general_normalview'].'</a></div>';
	 }
 }
if (!($_SESSION['plain'])){
if($style['plainviewcompatible']){
	$uri = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&plain=1';
	echo '<div id="plain"> <a href="'.$uri.'">'.$lang['general_largeview'].'</a></div>';
}
	?>
	<div align="center"> <!-- will be closed by footer.php -->


	<div id="motherdiv"> <!-- will be closed by footer.php -->

	<? styleLogo(); ?>


	<div id="top_top">
	<div id="top_bottom">
	<div id="top_left">
	<div id="top_right">
	<div id="top">
		<h1><? echo $settings['name'].' :: '.$settings['description']; ?></h1>
	</div>
	</div>
	</div>
	</div>
	</div>


	<?php
//plain view mode
}
if ($menu == 'horizontal' && !defined(INSTALLING)){
?>


<div id="menu_h_top">
<div id="menu_h_bottom">
<div id="menu_h_left">
<div id="menu_h_right">
<div id="menu_h">
<?php
	echo '<div class="menu_h_level_1">';
	createHorizontalMenu();
	echo '</div>';

?>
<?php

// the following variables are needed in fucntions & other files
global $rlink, $link, $link_query, $ssm, $sm;

//submenuhandling
$menulist = menus();
if (isset($menulist[$_REQUEST['menu']]) && $_REQUEST['menu']){
	echo '<div class="menu_h_level_2">';
	createHorizontalMenu($_REQUEST['menu']);
	echo '</div>';
	$sm = (isset($_REQUEST['submenu'])) ? $_REQUEST['submenu'] : $menulist[$_REQUEST['menu']][0];
}
if (isset($menulist[$_REQUEST['submenu']])) $ssm = $_REQUEST['ssubmenu'];
unset ($menulist);

//create link-variables for links within a file (for redirect(); & normal links)

if(isset($sm)) $smlink = 'submenu='.$sm.'&';
if(isset($ssm)) $smlink .= 'ssubmenu='.$ssm.'&';

$rlink = $_REQUEST['menu'].'&'.$smlink;
$link = '?menu='.$rlink;
$link_query = preg_replace('/menu='.$rlink.'/','', $_SERVER['QUERY_STRING'].'&');

?>
</div> <!-- menu_h divs.. -->
</div>
</div>
</div>
</div><!-- ..closed -->
<?
}
?>


<div id="cnt_top"><!-- div menu_h -->
<div id="cnt_bottom">
<div id="cnt_left">
<div id="cnt_right">
<div id="cnt">
<?php



if(!defined(INSTALLING)){
	// get Message (very important)
	$msg = getNewMessage($_SESSION['userid']);
	if($msg != ""){
		echo makeFloatingLayer($msg['title'], $msg['content'], 0, '_msg_'.$msg['id'], 'message_layer');
	}
}
//the most important part of the site =)
include_once('cnt/'.$site);
?>
</div><!-- cnt-divs.. -->
</div>
</div>
</div>
</div><!-- ..closed -->

<?php 
