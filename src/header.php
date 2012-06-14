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

/*the only thing worth mentionable here is the fact that the title, description & 
keywords are set dynamically. All the variables are delivered from the function
siteConstructor()*/

global $settings, $style;
if (isset($style['favicon'])) $favicon = '<link rel="shortcut icon" href="src/style_'.$settings['style'].'/img/'.$style['favicon'].'" type="image/x-icon" />';
if(defined(INSTALLING)) $installation_style = '<link rel="stylesheet" type="text/css" href="src/style_'.$settings['style'].'/layout_installation.css" />';
global $header;

$style_css = '<link rel="stylesheet" type="text/css" href="src/style_'.$settings['style'].'/layout.css" />';

$bootstrap = '<script type="text/javascript" src="src/opensource/bootstrap/js/bootstrap.min.js"></script>
            <link rel="stylesheet" type="text/css" href="src/opensource/bootstrap/css/bootstrap.min.css"/>';

$header ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>'.$title.'></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<meta name="description" content="'.$desc.'" />
	<meta name="keywords" content="'.$keys.'" />
	'.$bootstrap.'
	'. $installation_style.'
	'. $favicon.'

    '.$style_css.'


	<script type="text/javascript" src="src/functions.js" ></script>

    <script type="text/javascript" src="src/opensource/jquery/js/jquery-1.7.2.min.js"></script>


    <script type="text/javascript" src="src/opensource/jquery/js/jquery-ui-1.8.2.custom.min.js"></script>
    <link rel="stylesheet" type="text/css" href="src/opensource/jquery/css/jquery-ui-1.8.2.custom.css"/>

    <script language="text/javascript" type="text/javascript" src="src/opensource/jqplot/jquery.jqplot.min.js"></script>
    <link rel="stylesheet" type="text/css" href="src/opensource/jqplot/jquery.jqplot.css" />      
</head>';

function getHeader() {
    global $header;
    return $header;
}
?>
