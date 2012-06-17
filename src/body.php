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

global $settings, $style, $body;

$hour = (int)date('H', time());
$min = (int)date('i', time());
$sec = (int)date('s', time());


$additionalFunctions = "";
if ($_REQUEST['menu'] == 'overview') $additionalFunctions .= ", overviewArrange()";
if (isset($_REQUEST['filter'])) $additionalFunctions .= ", filterChange(), showFilter()";
if (isset($xajax)) $additionalFunctions .= ", load_xajax()";

$body .= '

<body onLoad="placeFloatingLayers(), sClock(\'' . $hour . '\',\'' . $min . '\',\'' . $sec . '\', \'' . $lang['footer_server_time'] . '\') ' . $additionalFunctions . '"> <!-- load the layer if one\'s constructed -->
<script type="text/javascript" src="src/opensource/wz_tooltip.js" ></script> ';

$body .= styleComment();

//plain view mode
if ($style['plainviewcompatible']) {
    if (isset($_REQUEST['plain'])) $_SESSION['plain'] = $_REQUEST['plain'];
    if ($_SESSION['plain']) {
        $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '&plain=0';
        $body .= '<div id="plain"> <a href="' . $uri . '">' . $lang['general_normalview'] . '</a></div>';
    }
}
if (!($_SESSION['plain'])) {
    if ($style['plainviewcompatible']) {
        $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '&plain=1';
        $body .= '<div id="plain"> <a href="' . $uri . '">' . $lang['general_largeview'] . '</a></div>';
    }

    $body .= '<div align="center"> <!-- will be closed by footer.php -->


	<div id="motherdiv"> <!-- will be closed by footer.php -->';

    $body .= styleLogo();


    $body .= '<div id="top_top">
        <div id="top_bottom">
        <div id="top_left">
        <div id="top_right">
        <div id="top">
            <h1>' . $settings['name'] . ' :: ' . $settings['description'] . '</h1>
        </div>
        </div>
        </div>
        </div>
        </div>';


//plain view mode
}
if ($menu == 'horizontal' && !defined(INSTALLING)) {


    $body .= '<div id="menu_h_top">
    <div id="menu_h_bottom">
    <div id="menu_h_left">
    <div id="menu_h_right">
    <div id="menu_h">';
    $body .= '<div class="menu_h_level_1">';
    $body .= createHorizontalMenu();
    $body .= '</div>';


// the following variables are needed in fucntions & other files
    global $rlink, $link, $link_query, $ssm, $sm;

//submenuhandling
    $menulist = menus();
    if (isset($menulist[$_REQUEST['menu']]) && $_REQUEST['menu']) {
        $body .= '<div class="menu_h_level_2">';
        $body .= createHorizontalMenu($_REQUEST['menu']);
        $body .= '</div>';
        $sm = (isset($_REQUEST['submenu'])) ? $_REQUEST['submenu'] : $menulist[$_REQUEST['menu']][0];
    }
    if (isset($menulist[$_REQUEST['submenu']])) $ssm = $_REQUEST['ssubmenu'];
    unset ($menulist);

//create link-variables for links within a file (for redirect(); & normal links)

    if (isset($sm)) $smlink = 'submenu=' . $sm . '&';
    if (isset($ssm)) $smlink .= 'ssubmenu=' . $ssm . '&';

    $rlink = $_REQUEST['menu'] . '&' . $smlink;
    $link = '?menu=' . $rlink;
    $link_query = preg_replace('/menu=' . $rlink . '/', '', $_SERVER['QUERY_STRING'] . '&');

    $body .= '
    </div> <!-- menu_h divs.. -->
    </div>
    </div>
    </div>
    </div><!-- ..closed -->';

}


$body .= ' <div id="cnt_top"><!-- cnt_divs -->
        <div id="cnt_bottom">
        <div id="cnt_left">
        <div id="cnt_right">
        <div id="cnt">';


if (!defined(INSTALLING)) {
    // get Message (very important)
    $msg = getNewMessage($_SESSION['userid']);
    if ($msg != "") {
        $body .= makeModal($msg['title'], $msg['content'], $msg['footer'], $msg['id'], 'show');
        //$body .= makeFloatingLayer($msg['title'], $msg['content'], 0, '_msg_' . $msg['id'], 'message_layer');
    }
}

if ($noxajax) {
    echo $body;
}

//the most important part of the site =)
include_once('cnt/' . $site);

$bodyend = '
</div><!-- cnt-divs.. -->
</div>
</div>
</div>
</div><!-- ..closed -->';


if ($noxajax) {
    echo $bodyend;
} else {
    $body .= $bodyend;
}


function getBody()
{
    global $body;
    return $body;
}

?>
