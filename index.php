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

//start the session, set cookies and include all the functions
session_start();

if($_REQUEST['menu']=='logout'){
	setcookie('dtl');
	setcookie ('rememberme', 'false' , time()+60*60*24*30);
	unset($_SESSION['destroycookie']);
}
if(isset($_SESSION['setcookie'])){
	setcookie('dtl', $_SESSION['pw'].':'.$_SESSION['setcookie'], time()+60*60*24*30);
	setcookie ('rememberme', 'true' , time()+60*60*24*30);
	unset($_SESSION['setcookie']);
	unset($_SESSION['pw']);
}
if(isset($_SESSION['setstylecookie'])){
	setcookie('style', $_SESSION['setstylecookie'],time()+60*60*24*30);
	unset($_SESSION['setstylecookie']);
}
if(isset($_SESSION['setlangcookie'])){
	setcookie('lang', $_SESSION['setlangcookie'],time()+60*60*24*30);
	unset($_SESSION['setlangcookie']);
}

//if language change invoked, must be set before the languageSelector!
if(isset($_REQUEST['langchange'])){
	$_SESSION['dlang'] = $_REQUEST['langchange'];
	setcookie('lang', $_REQUEST['langchange'],time()+60*60*24*30);
}

define (VERSION, 'v0.7-SNAPSHOT');

//installing?
if(is_dir('installation')){
	$site = '../installation/install';
	define(INSTALLING, 'true');

	if(!isset($_SESSION['dlang'])) $_SESSION['dlang'] = 'en';
	include('installation/ilang/install_'.$_SESSION['dlang'].'.php');
	$langs = Array(
		'long'	=> Array(
			'English',
			'Deutsch'
		),
		'short' => Array(
			'en',
			'de'
		)
	);
	
	include_once('src/functions.php');

	$settings = Array(
		'name'		=>	'zock!',
		'description'	=>	$lang['instl_yourbettingoffice'],
		'style' 	=>	'zock',
		'email' 	=>	'zock@sagex.ch' );
	//include style info file
	include_once('src/style_'.$settings['style'].'/info.php');
}else{

    include_once('src/classes/class.usercollection.php');
    include_once('src/classes/class.ranking.php');
    include_once('src/classes/class.plotter.php');

    include_once('src/functions.php');

//useful variable as constant.
//this one is needed for all the DB-reqeusts
define(PFIX, $my_db['prefix']);


//load settings of the site, not the user
$settings = loadSettings();

// set time zone
if ($settings['time_zone'] != "") {
    date_default_timezone_set($settings['time_zone']);
}


//include style info file
include_once('src/style_'.$settings['style'].'/info.php');

//initialitation @ first call of the website
if(!(isset($_SESSION['init']))) init();


//load the language elements ($lang) & the available languages ($langs)
	//pay attention to the subtle s which makes de difference :)
include_once('src/classes/class.lang.php');
$lang = languageSelector($_SESSION['dlang']);
$cont = new Lang($_SESSION['dlang']);
$langs = languagesReader();

if (isset($_POST['hf_read'])){
	messageRead($_POST['hf_read'], $_SESSION['userid']);
}

//load events (public, unpublic+public, inactive)
//for array design, make a print_r or take a look at
//the function
include_once('src/classes/class.event.php');
include_once('src/classes/class.eventcollection.php');
include_once('src/classes/class.zockqueries.php');

$events_test = new EventCollection($_SESSION['userid']);

$events['p'] = loadEvents(0);
$events['u'] = loadEvents(1);
$events['i'] = loadEvents(-1);

//seek out the site to display & check if allowed
$homepage = isset($_SESSION['userid']) ? 'loginhome' : 'home';
$site = (isset($_REQUEST['menu'])) ? $_REQUEST['menu'] : $homepage;

}

$xajax_compliant = array('mytips', 'loginhome', 'overview', 'admin','ranking');
//include_once 'src/opensource/xajax/xajax_core/xajax.inc.php';


//the following function goes to error page, if a menu's not tought for the one demanding
if (!(menuAllowance($site, $_REUQUEST['submenu']))){
    $site = 'error';
	siteConstructor('header', $lang['general_bettingOffice'].' '.$settings['name'].' > '.$lang[$site.'_title'], $lang['general_bettingOffice'].' || '.$lang[$site.'_title']);
	errorPage('menu');
	siteConstructor('footer');
}else{
	//build the acutal site with this weird siteConstructor (is in fact just a forwarder)

    if (in_array($site,$xajax_compliant)) {

        include_once('src/opensource/xajax/xajax_core/xajax.inc.php');
        $xajax = new xajax();
        $xajax->configure('javascript URI','src/opensource/xajax/');

        $body = siteConstructor('body', $site.'.php', 'horizontal', '', $xajax);
        $header = siteConstructor('header',
            $lang['general_bettingOffice'].' '.$settings['name'].' > '.$lang[$site.'_title'], $lang['general_bettingOffice'].' || '.$lang[$site.'_title'],
            '',
            '',
            $xajax);

        echo $header.$body;

    } else {

        $header = siteConstructor('header',
                                    $lang['general_bettingOffice'].' '.$settings['name'].' > '.$lang[$site.'_title'], $lang['general_bettingOffice'].' || '.$lang[$site.'_title']);
        echo $header;
        $body = siteConstructor('body', $site.'.php', 'horizontal','noxajax');
        //if (isset($body)) echo $body;

    }
	siteConstructor('footer');
}

?>
