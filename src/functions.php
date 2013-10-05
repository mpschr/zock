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

/*
This file contains most the functions called by the .php-files
*/

/*
Function index:
>init
>loadSettings
>siteConstructor
>redirect
>languageSelector
>languagesReader
>login
>errorPage
>errorMsg
>indication
>isLogged
>isAdmin
>menus
>menuAllowance
>createHorizontalMenu
>createVerticalMenu
>phpManageUser
>generatePassword
>loadEvents
>loadUserEvents
>UserEventNumber
>eventUserNumber
>ActiveEventsNumber
>eventIsPublic
>userParticipates
>userWaits
>makeLangSelect
>orderIt
>chooseDisplayEvent
>thumbnailer
>makeFloatingLayer
>styleComment
>styleLogo
>generateEventInfo
>user2column
>closestGame
>getNewMessage
>messageRead
>php2js
>betUntil
>isCorrect
>isDiff
>isAlmost
>isCorrectToto
>rankingCalculate
>initMail
>notify
>weekday
>infoBarEventCreation
>getStyleInfo
>installLang
>lookupLangName
>osort
 */



//include the 2 files responsible for the connection to the datbas
//=>all the information to pass
if(!defined(INSTALLING)) include("src/vars.php");
//=>the db class, for more convenient queries, important to cast a glance for better understanding! is quite nice
include_once("src/opensource/db_mysql.php");
//after loading the db class, we  want to us it and try to make an instance :)
if(!defined(INSTALLING)) $db = new bDb;


function init(){
	//is only invoked @ first connect to the website
	//sets all the important variables to initial state

	global $settings;

	if($_COOKIE['rememberme'] == 'true'){
		$dtl = preg_split('/:/', $_COOKIE['dtl']);
		login($dtl[1],$dtl[0]);
		$_SESSION['init'] = TRUE;
	}else{

	$_SESSION['init'] = TRUE;
	$_SESSION['logged'] = FALSE;
	$_SESSION['admin'] = FALSE;
	$_SESSION['dlang'] = (isset($_COOKIE['lang'])) ? $_COOKIE['lang'] : $settings['lang'];
	}

	
}

function loadSettings($user=0){
	//returns an array with all the settings for the site (_settings table)
		//=> $['SETTING'] = VALUE
	$sets = new bDb;
	$setting=Array();
	if($user == 0){
		$data = $sets->query("SELECT * FROM ".PFIX."_settings");
		foreach($data as $row)
			$setting[$row['setting']] = $row['value'];
		
		//style
		if($_SESSION['logged']){
			$styles = preg_split('/:/', $setting['style_forusers']);
			array_pop($styles);
			$usets = loadSettings($_SESSION['userid']);
			if ($usets['style']!="" && in_array($usets['style'],$styles))
				$setting['style'] = $usets['style'];
		}else{
			if(isset($_COOKIE['syle'])){
				$styles = preg_split('/:/', $setting['style_forusers']);
				array_pop($styles);
				if ($_COOKIE['style']!="" && in_array($_COOKIE['style'],$styles))
					$setting['style'] = $usets['style'];

			}

		}
	
	}else{
	//returns an array with all the settings for the user (_users table)
		//=> $['SETTING'] = VALUE
		//=> reading in everything except the password
		$data = $sets->query("SELECT * FROM ".PFIX."_users WHERE id='".$user."'");
		foreach($data[0] as $label => $el)
			$setting[$label] = $el;
	}
	return $setting;
}

function siteConstructor($action, $title_or_site=NULL, $desc_or_menu=NULL, $keys=NULL, $xajax=NULL){
	//creates the site from top to bottom...a forwarder function
	global $lang;
    $noxajax= ($xajax==null) ? true : false;
    switch($action){
		case 'header':
			$title = $title_or_site;
			$desc = $desc_or_menu;
			if($keys == NULL); $keys = $desc;
			include_once('src/header.php');
            return getHeader();
		case 'body':
			$site = $title_or_site;
			$menu = $desc_or_menu;
			include_once('src/body.php');
            return getBody();
        case 'footer':
			include_once('src/footer.php');
			break;
	}
}

function redirect($where, $wait=0, $properURL=0){
	//just redirects...hard to write by heart
	if ($properURL < 1) {
		echo '<META HTTP-EQUIV="Refresh" CONTENT="'.$wait.'; URL=?menu='.$where.'"';
	}else{
		echo '<META HTTP-EQUIV="Refresh" CONTENT="'.$wait.'; URL='.$where.'"';
	}

}

function languageSelector($standard=NULL){
$langObj = new bDb;
if ($standard == NULL) $standard='en';
$langid=$standard;

    $lang= array();
    $filename="data/langs/lang_$langid.xml";
    $xml = simplexml_load_file($filename);
    foreach($xml->zock_lang as $entry) {
        $lab = (string) $entry->label;
        #$lang[$lab] = utf8_decode((string) $entry->$langid);
        $lang[$lab] = (string) $entry->cnt;
    }
    
return $lang;	
}

function languagesReader(){
	//returns an array with all the languages as short & long form

	$langObj = new bDb;	
	
	$languages = $langObj->query("SELECT * FROM ".PFIX."_langs ORDER BY `long`");
	$langs = Array();

	for ($x = 0; $x < sizeof($languages) ; $x++) {
		$langs['short'][$x] = $languages[$x]['short'];
		$langs['long'][$x] = $languages[$x]['long'];
	}

	return $langs;
}


function login($user, $pw, $cookie=0){
global $db, $lang, $settings;

	//datablase read
	$dbpw = $db->query('SELECT pw,lang FROM '.PFIX.'_users WHERE login = "'.$user.'"');

	//encrypt the chosen password & compare it to the saved one
	if(crypt($pw, $dbpw[0]['pw']) == $dbpw[0]['pw'] || $_COOKIE['rememberme'] == 'true' && $pw == $dbpw[0]['pw']){

		//success!
		$_SESSION['logged'] = TRUE;
		
		//rememberme: next page call, they will be set into cookie and destroyed
		if ($cookie==1){
			$_SESSION['setcookie'] = $user;
			$_SESSION['pw'] = $dbpw[0]['pw'];
		}

		//load user settings
		$userinfo = $db->query('SELECT * FROM '.PFIX.'_users WHERE login = "'.$user.'"');
		$_SESSION['dlang'] = $userinfo[0]['lang'];
		$_SESSION['userid'] = $userinfo[0]['id'];
			$_SESSION['setlangcookie'] = $userinfo[0]['lang'];
		
		//is this user an admin by an chance? Let's find out.
		$admins = explode(':', $settings['admins']);
		foreach($admins as $a){
			if ($a == $userinfo[0]['id']){

				//he is a user => set the admin value in the $_SESSION variable
				$_SESSION['admin'] = TRUE;
				$thistimetrue = TRUE;
			}
		}
		//for the case, the admin didn't log out, but a user logs in 
			//=> there's still an admin in the session;
		if (!$thistimetrue) $_SESSION['admin'] = FALSE;


		
		if($_COOKIE['rememberme']!='true'){
			redirect('loginhome', 0);
		}elseif($_REQUEST['menu']==NULL){
			redirect('loginhome', 0);
		}
		
	}else{
		// no success!! send him back
		echo errorMsg('login');
		echo $lang['general_redirect'];
		redirect('login', 3);
	}
}


function errorPage($id){
	//redirection to error page and error delivery
	$_SESSION['error'] = $id;
	redirect('error');
}


function errorMsg($id,$plain=0){
	//return a nice error message
	global $lang;
	if($plain==0)
		return errorTxtMsg($lang['error_attention'].' '.$lang['error_'.$id]);
	else
		return errorTxtMsg($lang['error_attention'].' '.$lang[$id]);
}

function errorTxtMsg($txt){
		return '<div class="error"><font>'.$txt.'</font></div>';
}

function indication($txt){
	return '<div class="indication"><font>'.$txt.'</font></div>';
}


function isLogged(){
	//if user is logged, return 1, else 0
		//=> see function login
	return $_SESSION['logged'];
}

function isAdmin(){
	//if user is admin, return 1, else 0
		//=> see function login
	return $_SESSION['admin'];
}

function menus(){

	// the follwing arrays are the menus for the different user levels,
	// as well for the submenus .to change the menu order, 
	// simply change the order in the array (fifo :) )
		//=> uncommented menus are depricated, but have still their
		// translations in the lang-table in the DB;

	//the menus are to be loaded in in the functions 
	//menuAllowance, createVerticalMenu, createHorizontalMenu (and in src/body.php)
		
	$menus['normal'] = array(	'login',
					'register',
					'overview',
					'home');
					
	$menus['logged'] = array(	'mytips',
					'overview',
					'ranking',
					'participants',
					'comments',
					'loginhome');
					
	$menus['admin'] = array(	'settings',
					'events',
					'lang',
					'messages');
					
	$menus['events'] = array (	'settings',
					'matches',
					'results');

    $menus['logout'] = 'logout';

    $menus['profile'] = 'myprofile';
					
	$menus[$menus['profile']] = array(
                    'settings',
					'appearance',
					'password');
					
	//the following arrays are for allowance check purpose only
		//=> is necessary for they are only displayed when the
		//   the user has the priviledges
		
	$menus['loggedsubs'] = array (	'events',
					'myprofile');
					
	$menus['adminsubs'] = array (	'admin');


	//the important part =)
	return $menus;
}



function menuAllowance($requested_menu, $optional_submenu=NULL){
	//load the menus and check if the requested ones are legitmite for
	// the user demanding
	$menu = menus();

	//installation
	if($requested_menu == '../installation/install'){
		return TRUE;
	}

	// first, if there's a submenu requested, check it's ok;
	if($optional_submenu != NULL){
		if($_SESSION['logged'] && in_array($optional_submenu, $menu['loggedsubs']))
			return TRUE;
		elseif($_SESSION['admin'] && in_array($optional_submenu, $menu['adminsubs']))
			return TRUE;
		else return FALSE;
	}

    $loggeduserMenus = array_merge(array($menu['profile']),$menu['logged']);

	//if just a menu is demanded or the submenus are ok. Logged users should not
	//call the unlogged menus (as login, register....)
	
	//visitor calls visitor menu
	if (!($_SESSION['logged']) && in_array($requested_menu, $menu['normal']))
		return TRUE;	
		
	//user calls user menu (or admin calls user menu)
	elseif($_SESSION['logged'] && in_array($requested_menu, $loggeduserMenus))
		return TRUE;
		
	//neither of the menu types is demanded, which leaves the admin menu
	//=> if is logged and is admin, it's ok as well
	elseif($_SESSION['logged'] && $_SESSION['admin'])
		return TRUE;
		
	//ah yeah, the error page is not a real menu, just a "hidden" site one
	//should have allowance to go to =)
	elseif($requested_menu == 'error' || $requested_menu == "logout")
		return TRUE;

	//well, we've got a sneaky user
	else return FALSE;
}



function createHorizontalMenu($submenu=NULL){
	//load all the infos needed
	global $lang, $settings, $style;
	$stl = 'src/style'.$settings['style'].'/img';
	$menus = menus();
	$logged = isLogged();
	$admin = isAdmin();

    if ($logged) {
        $uc = new UserCollection();
        $userlogin = $uc->getUserById($_SESSION['userid'])->getLogin();
    }

    $hasButtonImages = (strlen($style['btn_format'])>0);
    $menuHTML = '';

    //creat the main Horizontal menu
	if($submenu==NULL){


        //normal menu (@ page entry)
        if (!($logged)){
            $menuHTML .= '
                            <div class="subnav subnav-fixed">
                              <ul class="nav nav-pills">';

            foreach($menus['normal'] as $menu){
                $active = '';
                if ($_REQUEST['menu'] == $menu) $active = 'active';

                $menuText = $hasButtonImages ?
                    '<img src="src/style_'.$stl.'/btn_'.$menu.'.'.$style['btn_format'].'" alt="'.$lang[$menu.'_title'].'" />' :
                    '<span>'.$lang[$menu.'_title'].'</span>';

                $menuHTML .=        '<li class="'.$active.'">
                                        <a href="index.php?menu='.$menu.'">'.$menuText.'</a>
                                    </li>';
            }

            $menuHTML .=     '</ul>
                            </div><!-- close subnav-->';

        }else{
            //the logged user menu

            //add the user profile and admin button
            $off = $menus['logout'];
            $userprofile = $menus['profile'];
            $userprofileMenus = $menus[$userprofile];

            $userM = array($userprofile,'admin');
            $active = '';
            if (in_array($_REQUEST['menu'],$userM)) $active = 'active';

            $message = '';
            $msg = getNewMessage($_SESSION['userid']);
            if ($msg != "") {
                //$message = makeModal($msg['title'], $msg['content'], $msg['footer'], $id, 'show');
                $envelope = '<a class="btn  btn-primary" data-toggle="modal"
                                href="#modal_'.$msg['id'].'"  title="New Message">
                                <span class="label label-info">1</span>
                                <i class="icon-envelope icon-white"></i>
                           </a>';
            }


            $menuHTML .= '
                       <div class="btn-group pull-right">
                           <a class="btn  btn-primary"
                                href="index.php?menu='.$off.'" rel="tooltip" title="Log Out">
                                <span>&nbsp;</span><i class="icon-off icon-white"></i>
                           </a>

                           '.$envelope.'

                           <a class="btn btn-primary dropdown-toggle '.$active.'" data-toggle="dropdown"
                                href="#" rel="tooltip" title="Account">
                                <i class="icon-user icon-white"></i>
                                <span>'.$userlogin.'</span>
                                <i class="caret"></i>
                           </a>';

            $menuHTML .= ' <ul class="btn-primary dropdown-menu">';

            foreach ($userprofileMenus as $usermenu) {
                $menuText = $hasButtonImages ?
                    '<img src="src/style_'.$stl.'/btn_'.$usermenu.'.'.$style['btn_format'].'" alt="'.$lang[$userprofile.'_'.$usermenu.'_title'].'"/>' :
                    '<span>'.$lang[$userprofile.'_'.$usermenu.'_title'].'</span>';

                $menuHTML .=        '<li>
                                        <a href="index.php?menu='.$userprofile.'&submenu='.$usermenu.'">'.$menuText.'</a>
                                    </li>';
            }


            if ($admin) {
                $menuHTML .= '<li class="divider"></li>';
                foreach ($menus['admin'] as $adminsubmenu) {
                    $adminmenu = 'admin';
                    $menuText = $hasButtonImages ?
                        '<img src="src/style_'.$stl.'/btn_'.$adminsubmenu.'.'.$style['btn_format'].'" alt="'.$lang[$adminmenu.'_'.$adminsubmenu.'_title'].'"/>' :
                        '<span>'.$lang[$adminmenu.'_'.$adminsubmenu.'_title'].'</span>';

                    $menuHTML .=        '<li>
                                        <a href="index.php?menu='.$adminmenu.'&submenu='.$adminsubmenu.'">'.$menuText.'</a>
                                    </li>';
                }
            }

            $menuHTML .= '</ul></div>';
            //$menuHTML .= '
            //           <div class="container">';
                        /*<div class="subnav subnav-fixed">
                          <ul class="nav nav-pills">';*/
            /*$menuHTML .= '<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar icon-white"></span>
                            <span class="icon-bar icon-white"></span>
                            <span class="icon-bar icon-white"></span>
                          </button>';*/
            //$menuHTML .= '<div class="subnav nav-collapse">';
            $menuHTML .= '<div class="subnav">';
            $menuHTML .= '<ul class="nav nav-pills">';


            foreach($menus['logged'] as $menu){
                $active = '';
                if ($_REQUEST['menu'] == $menu) $active = 'active';

                $menuText = $hasButtonImages ?
                    '<img src="src/style_'.$stl.'/btn_'.$menu.'.'.$style['btn_format'].'" alt="'.$lang[$menu.'_title'].'" />' :
                    '<span>'.$lang[$menu.'_title'].'</span>';

                $menuHTML .=    '<li class="'.$active.'">
                                    <a href="index.php?menu='.$menu.'">'.$menuText.'</a>
                                </li>';

            }

                    $menuHTML .= '</ul>';

            $menuHTML .='</div><!-- close subnav-->';


        }

	//make the submenu line
	}else{
        if ($submenu=='myprofile')
            return;
		foreach($menus[$submenu] as $menu){
			$active = 'menulink';
			if ($_REQUEST['submenu'] == $menu) $active = 'menulinksel';
			$menuHTML .=  '<a class="'.$active.'" id="'.$menu.'" href="index.php?menu='.$submenu.'&submenu='.$menu.'">';
				$menuHTML .=  $hasButtonImages ? '<img src="src/style_'.$stl.'/btn_'.$submenu.'_'.$menu.'.'.$style['btn_format'].'" alt="'.$lang[$submenu.'_'.$menu.'_title'].'"/>' :
                                            '<span>'.$lang[$submenu.'_'.$menu.'_title'].'</span>';
					$menuHTML .=  '</a>';
		}
	}
    return $menuHTML;
}

function createVerticalMenu($vmenu=NULL, $option=NULL){
	//the content of this menu is quite variable
	$menus = menus();
    $menuHTML = '';
    global $lang, $link, $link_query, $ssm, $events;
	if ($vmenu != NULL){
		$menuHTML .=  '<div id="menu_v">';
		foreach($menus[$vmenu] as $vm){
			// the $link has already a ssmenu in it.. so we have to replace it for creating links
			$sslink = preg_replace('/ssubmenu='.$ssm.'/', 'ssubmenu='.$vm, $link.$link_query);
			$menuHTML .=  '-<a class="vmenulink" href="'.$sslink.'">'.$lang['admin_'.$vmenu.'_'.$vm.'_title'].'</a>';
			$menuHTML .=  '<br>';
		}
		$menuHTML .=  '</div>';
	}
	//ueventlist lists events where user is registered to
	//peventlist lists events which are set public (minus userevents)
	elseif (preg_match("(ueventlist|peventlist)", $option )){
		$possibleEventsArray = LoadUserEvents();
		$menuHTML .=  '<div id="menu_v">';
		if(isset($_SESSION['userid'])){
			$possibleEvents = explode(':', $possibleEventsArray['approved']);
			array_pop($possibleEvents);
		
			if(UserEventNumber() > 0) $menuHTML .=  $lang['general_yourevents'].'<br />';
			foreach($possibleEvents as $e){
				$eventlink = $link.'ev='.$e;
				$menuHTML .=  '-<a class="vmenulink" href="'.$eventlink.'">'.$events['u']['e'.$e]['name'].'</a>';
				$menuHTML .=  '<br>';
				$taken[] = $e;
			}
		}
		if($option == 'peventlist'){
			unset($possibleEvents);
			if ($taken == NULL) $taken[0] = null;

			foreach ($events['p'] as $k => $pe){
				if (is_numeric($k) && !(in_array($pe, $taken))) $possibleEvents[] = $pe;
				}

			if($possibleEvents != NULL){	
				$menuHTML .=  (UserEventNumber > 0) ? $lang['general_furtherevents'] : $lang['general_publicevents'];
				$menuHTML .=  '<br />';
				foreach($possibleEvents as $e){
					$eventlink = $link.'ev='.$e;
					$menuHTML .=  '-<a class="vmenulink" href="'.$eventlink.'">'.$events['p']['e'.$e]['name'].'</a>';
					$menuHTML .=  '<br>';
				}
			}
		}	
		$menuHTML .=  '</div>';
	}

	//the following 2 div's serve for mini-/maximizing the v_menu (both are necessary);
	elseif($option == 'mmclose'){
		$menuHTML .=  '<div id="menu_v_mmc" class="menu_v_mm">';
			$menuHTML .=  '<a class="vmenulink" href="javascript: menuMM()" title="'.$lang['general_minimize'].'">></a>';
		$menuHTML .=  '</div>';
	}
	elseif($option == 'mmopen'){
		$menuHTML .=  '<div id="menu_v_mmo" class="menu_v_mm">';
			$menuHTML .=  '<a class="vmenulink" href="javascript: menuMM()" title="'.$lang['general_maximize'].'"><</a>';
		$menuHTML .=  '</div>';
	}
    return $menuHTML;
}


function phpManageUser($user, $what, $event){
	//if the admin aproves/refuses users, the different strings in the db have to be changed
		//=> a string is of the form 1:2:3: (for user 1, 2 & 3)
	global $db;
	$data = $db->query("SELECT users_approved, users_waiting, users_denied, users_paid, users_reimbursed FROM ".PFIX."_events WHERE id='$event'");
	$approved = $data[0]['users_approved'];
	$waiting = $data[0]['users_waiting'];
	$denied = $data[0]['users_denied'];
    $paid = $data[0]['users_paid'];
    $reimbursed = $data[0]['users_reimbursed'];
	if($what=='a'){
		$waiting = str_replace($user.':', '', $waiting);
		$approved .= $user.':';
	}elseif($what=='d'){
		$waiting = str_replace($user.':', '', $waiting);
		$denied .= $user.':';
	}elseif($what=='w'){
		$waiting .= $user.':';
	}elseif($what=='r'){
		$waiting = str_replace($user.':', '', $waiting);
	}elseif($what=='p'){
        $paid .= $user.':';
    }elseif($what=='np'){
        $paid = str_replace($user.':', '', $user);
    }elseif($what=='re') {
        $reimbursed .= $user.':';
    }elseif($what == 'nre') {
        $reimbursed = str_replace($user.':', '', $reimbursed);
    }

	$query =  "UPDATE ".PFIX."_events SET users_approved = '$approved', users_waiting = '$waiting', users_denied = '$denied', users_paid = '$paid' WHERE id = '$event';";
    return $query;
}


//a copied function
function generatePassword ($length = 8){
	// from http://www.laughing-buddha.net/jon/php/password/

	// start with a blank password
	$password = "";

	// define possible characters
        $possible = "0123456789abcdfghjkmnpqrstvwxyz"; 
	
	// set up a counter
	$i = 0; 

	// add random characters to $password until $length is reached
	while ($i < $length) { 

		// pick a random character from the possible ones
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

		// we don't want this character if it's already in the password
		if (!strstr($password, $char)) { 
			$password .= $char;
			$i++;
		}
	}
	//done!
	return $password;
}


function loadEvents($option=0){
	//this creates arrays with the event's & their most important infos
		//=> the event (id), their users (a,w,d) & their name and finally
		// the quantity of the type demanded
	
	//the structure is a little complicated, take a look at the function
		//=> optional variable for not public events = 1 && -1 for inactive
	
	global $db;
	$raw = $db->query("SELECT * FROM ".PFIX."_events");
	foreach	($raw as $row){		

		if($row['active'] > 0 && ($row['public']+$option > 0) ){
			$e++;
			$array[] = $id = $row['id'];
			$array['e'.$row['id']]['a'] = $row['users_approved'];
			unset($row['users_approved']);
			$array['e'.$row['id']]['w'] = $row['users_waiting'];
			unset($row['users_waiting']);
			$array['e'.$row['id']]['d'] = $row['users_denied'];
			unset($row['users_denied']);
			foreach($row as $label => $el)
				$array['e'.$id][$label] = $el;
		}elseif($row['active']>$option && $row['active']==0 || $option<0 && $row['active'] < 0){
			$e++;
			$array[] = $id = $row['id'];
			foreach($row as $label => $el)
				$array['e'.$id][$label] = $el;
		}
	}
	$array['nb'] = $e;
	return $array;
}

function loadUserEvents(){
	//returns array with event-situation of user
		//=> array ('approved, waiting, denied')
	global $events;
    global $cont;
	foreach ($events['u'] as $ar){
		if(!is_array($ar)) $a = $ar;
		if(is_array($ar)){
			$str = $_SESSION['userid'];
			$approved = explode(':', $ar['a']);
			$waiting = explode(':', $ar['w']);
			$denied = explode(':', $ar['d']);
			
			if(in_array($str, $approved)){
				 $array['approved'] .= $a.':';
			}elseif(in_array($str, $waiting)){
				 $array['waiting'] .= $a.':';
			}elseif(in_array($str, $denied)){
				 $array['denied'] .= $a.':';
			}elseif(gmdate('Ymd', $events['u']['e'.$a]['deadline']) >= gmdate('Ymd',time())){
				 $array['open'] .= $a.':';
			}
		}
	}	

	return $array;	
}

function UserEventNumber(){
	//returns the # of events where user is approved
	$uevents = loadUserEvents();
	$uaevents = explode(':', $uevents['approved']);
	return sizeof($uaevents)-1;
}

function eventUserNumber($ev){
	//returns the # of users in an event
	global $events;
	$evUsers = explode(':', $events['u']['e'.$ev]['a']);
	array_pop($evUsers);
	return sizeof($evUsers);
}

function ActiveEventNumber($nature='p'){
	//how many events are active?
	global $events;
	return $events[$nature]['nb'];
}

function eventIsPublic($givenevent){
	//checks if a given event is Public and returns 1 or 0
	if ($givenevent == 0) return 0;
	global $events;
	foreach ($events['p'] as $pe){
		if(!is_array($pe) && $pe == $givenevent) return 1;
	}
	return 0;
}


function userParticipates($event, $userID=NULL){
	//check's if a user participates in a given event
	global $events;
	$evarray = explode(':', $events['u']['e'.$event]['a']);
	if ($userID == NULL) $userID = $_SESSION['userid'];
	return in_array($userID, $evarray);
}

function userWaits($event, $userID=NULL){
	//checks if a user waits for approval in a given event
	global $events;
	$evarray = explode(':', $events['u']['e'.$event]['w']);
	if ($userID == NULL) $userID = $_SESSION['userid'];
	return in_array($userID, $evarray);
}

function makeLangSelect($actual, $id=NULL, $javascript=NULL){
	//produces a select form of all the languages => this way there's everywhere the same
	global $db, $langs;
	$select = '<select id="'.$id.'" name="lsel" size="1" '.$javascript.'>';
	for($x=0; $x<sizeof($langs['short']); $x++){	
		$select .= ($langs['short'][$x] == $actual) ? 
			'<option selected="selected" value="'.$actual.'">'.$langs['long'][$x].'</option>' : 
			'<option value="'.$langs['short'][$x].'">'.$langs['long'][$x].'</option>';
	}
	$select .= '</select>';
	return $select;
}

function makeStyleSelect(){
	//produces a select form for all the styles installed
	global $settings;
	$dircontents = scandir('src/');
	array($style);

	$select = '<select name="style" size="1">';
	foreach($dircontents as $dc){
		if (is_dir('src/'.$dc) && substr($dc, 0, 6) == 'style_'){
			$style =  substr($dc, 6);
			$select .= ($settings['style'] == $style) ?
				'<option selected="selected" value="'.$style.'">'.$style.'</option>' :
				'<option value="'.$style.'">'.$style.'</option>';
		}		
	}
	$select .= '</select>';
	return $select;
}


function makeTimeZoneSelector() {
    // produces a form with all time zones in it.
    global $settings;
    $default = $settings['time_zone'];
    $inputform = "<input class='autotimezone' 
						name='time_zone' size='20' 
						value=".$default.">";
    
    $timezone_ids = DateTimeZone::listIdentifiers();
    foreach ($timezone_ids as $t) {
        $timezonelist .= '"'.$t.'", ';
    }
    
    $inputscript = "<script>
                        $(document).ready(function() {
                            $(\"input.autotimezone\").autocomplete({
                                source: [".$timezonelist."]
                            });
                        });
                    </script>";   
    
    return $inputform.$inputscript;
    
}

function orderBy($what, $o, $lq){
    //this function cleans the links for the links where you can order a table
    //=> before using this function, the array $orderby must've been created!
    $str = preg_replace('/(orderby=)(.*)(SORT_ASC|SORT_DESC)[& ]/i', '', $lq);
    $str .= 'orderby=';
    $str .= ($what.$o[1] == $o[0].'SORT_ASC') ? $what.':SORT_DESC' : $what.':SORT_ASC' ;
    return $str;
}

function orderIt($what, $o, $lq){
	//this function cleans the links for the links where you can order a table	
		//=> before using this function, the array $orderby must've been created!
	$str = preg_replace('/(orderby=)(.*)(ASC|DESC)[& ]/i', '', $lq);
	$str .= 'orderby=';
	$str .= ($what.$o[1] == $o[0].'ASC') ? $what.':DESC' : $what.':ASC' ;
	return $str;
}

function chooseDisplayEvent(){
	return 'definethisbloodyfunctionplease';
}

function thumbnailer($src, $dest, $maxsidelength=100){
	
	// get image properties
	$imgprop= GetImageSize($src);
	$width=$imgprop[0];
	$height=$imgprop[1];
	$destWidth = ($width >= $height) ? $maxsidelength : ($width*$maxsidelength/$height);
	$destHeight = ($width < $height) ? $maxsidelength : ($height*$maxsidelength/$width);

	if($imgprop[2]==1) {
	// GIF

	$base_image = imageCreate($destWidth, $destHeight);
	imageGIF($base_image,$dest.'_temp');
	$image = imageCreateFromGIF($dest.'_temp');
	$imageToResize = imageCreateFromGIF($src);
	imageCopyResized($image, $imageToResize, 0,0,0,0,$destWidth, $destHeight, $width, $height);
	imageGIF($image, $dest);
	@unlink($dest.'_temp');

	return 1;
	}

	if($imgprop[2]==2) {
	// JPG
	$base_image = imageCreate($destWidth, $destHeight);
	imageJPEG($base_image,$dest.'_temp');
	$image = imageCreateFromJPEG($dest.'_temp');
	$imageToResize = imageCreateFromJPEG($src);
	imageCopyResized($image, $imageToResize, 0,0,0,0,$destWidth, $destHeight, $width, $height);
	$jpegQuality = 75;
	imageJPEG($image, $dest, $jpegQuality);
	@unlink($dest.'_temp');


	return 1;
	}

	if($imgprop[2]==3) {
	// PNG
	$base_image = imageCreate($destWidth, $destHeight);
	imagePNG($base_image,$dest.'_temp.png');
	$image = imageCreateFromPNG($dest.'_temp.png');
	$imageToResize = imageCreateFromPNG($src);
	imageCopyResized($image, $imageToResize, 0,0,0,0,$destWidth, $destHeight, $width, $height);
	$pngQuality = 75;
	imagePNG($image, $dest, $pngQuality);
	@unlink($dest.'_temp.png');

		return 1;
	}


}

function makeFloatingLayer($title=NULL, $content=NULL, $closeX=1, $id=1, $class="floating_layer"){
	$fl = '<div id="floating_layer'.$id.'" class="'.$class.'">';
	$param_id =  ($id) ? '\''.$id.'\'' : '\'1\'';
	if($closeX == 1) $fl .= '<div class="floating_layer_close"><a href="javascript:hideFloatingLayer('.$param_id.')">X</a></div>';
	$fl .= '<h3>'.$title.'</h3>';
	$fl.=$content.'</div>';
	return $fl;
}

function makeModal($title=NULL, $content=NULL, $footer, $id, $show="hide"){


    $id = 'modal_'.$id;

    $fl = '<div id="'.$id.'" class="modal hide fade in">';

    $fl .= '<div class="modal-header">';
    $fl .=      '<h3>'.$title.'</h3>';
    $fl .= '</div>';

    $fl .= '<div class="modal-body">
                <p>
                    '.$content.'
                </p>
            </div>';

    $fl .= '<div class="modal-footer">
                '.$footer.'
           </div>

           </div><!--close modal-->
           ';

    $fl .= '<div class="modal-backdrop hide  fade in"  ></div>';


    return $fl;
}


function substitute($string,$subarray){
	preg_match_all('(\$[0-9]+)', $string, $nb);
	return str_replace($nb[0], $subarray, $string);
}

function styleLogo(){
	global $style, $settings;
	if (isset($style['logo']))
		return '<div id="style_logo"><a href="'.$settings['site_url'].'"><img src="src/style_'.$settings['style'].'/img/'.$style['logo'].'" /></a></div>';
}

function styleComment(){
	global $style;
	if ($style['comment'])
		return '<div id="style_comment">'.$style['comment'].'</div>';
}

/**
 * @depricated
 */
function generateEventInfo($id){
	global $db, $lang, $settings;
	$dbinfo =  $db->query("SELECT * FROM ".PFIX."_events WHERE id=".$id.";");
	$e = $dbinfo[0];
	$matchnb = $e['match_nb'];
	$point++;
	$alphabet = array('0', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k');
	$subpoint++;


	$header = $lang['eventinfo_rules'].
		'<br/><br/><a href="javascript: changeFloatingLayer(\''.$id.'_stake\')">'.$lang['eventinfo_stake'].'</a> | 
		<a href="javascript: changeFloatingLayer(\''.$id.'_tips\')">'.$lang['mytips_tips'].'</a> | '; 
	if($e['stake_mode']=='permatch') $header .= '<a href="javascript: changeFloatingLayer(\''.$id.'_gain\')">'.$lang['ranking_gain'].'</a> | ';
	if($e['stake_mode']!='none') $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_pointsjp\')">'.$lang['ranking_points'].' & '.$lang['overview_jackpot'].'</a> | ';
	if($e['stake_mode']=='none') $header.='<a href="javascript: changeFloatingLayer(\''.$id.'_pointsjp\')">'.$lang['ranking_points'].'</a> | ';
	$header.='<a href="javascript: changeFloatingLayer(\''.$id.'_end\')">'.$lang['eventinfo_finalaccount'].'</a>';


	//STAKE
	$to_sub1 = array($e['stake'].' '.$e['currency']);
	$to_sub2 = array($matchnb, $e['stake']*$matchnb.' '.$e['currency']);

	$ei['stake'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>';

	switch($e['stake_mode']){
		case 'none':
			$ei['stake'] .= '<td>'.$lang['eventinfo_nostake'].'</td>';
			break;
		case 'fix':
			$ei['stake'] .= '<td>'.substitute($lang['eventinfo_fixstake'], $to_sub1).'</td>';
			break;
		case 'permatch': 
			$ei['stake'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_stakepermatch'], $to_sub1).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_staketotal'], $to_sub2).'</td>';
	}
	$ei['stake'] .= '</tr></table>'; 

	//TIPS
		//prep
	$to_sub_toto = array ($lang['eventinfo_beton_toto_1'].', ', $lang['eventinfo_beton_toto_2'], ' & '.$lang['eventinfo_beton_toto_x']);
	if($e['bet_on'] != 'toto') array_pop($to_sub_toto);
	$to_sub_matchtypes = array ($lang['eventinfo_tournamentmatches'], $lang['eventinfo_komatches']);

	$subpoint = 1;
	$ei['tips'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>';
			if($e['bet_on']=='results') $ei['tips'] .= '<td>'.$lang['eventinfo_precise_tip'].'</td>';
			if($e['bet_on']=='toto') $ei['tips'] .= '<td>'.substitute($lang['eventinfo_beton_toto'], $to_sub_toto).'</td>';
		$ei['tips'] .= '</tr><tr>
			<td/>';
			if($e['ko_matches']=='no'){
					$ei['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_inallmatches'].' '.substitute($lang['eventinfo_allpossible'], 
					Array($lang['general_victory'], $lang['general_defeat'], $lang['general_tie'])).'</td>
				</tr><tr>';
			}elseif($e['ko_matches']=='only' && $e['enable_tie']=='no'){
					$ei['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_inallmatches'].' '.substitute($lang['eventinfo_tieno'], 
					array($lang['general_victory'], $lang['general_defeat']));
					$ei['tips'] .= ' '.$lang['eventinfo_afterpenalties'].' ';
					$ei['tips'] .= ($e['ap_score'] == 'addone') ? 
											$lang['eventinfo_afterpenalties_one'] : 
											$lang['eventinfo_afterpenalties_all'];
					$ei['tips'] .= '</td>
				</tr><tr>';
			}elseif($e['ko_matches']=='only' && $e['enable_tie']=='yes'){
					$ei['tips'] .= '<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_inallmatches'].' '.substitute($lang['eventinfo_allpossible'], 
					array($lang['general_victory'], $lang['general_defeat'], $lang['general_tie'])).' '.substitute($lang['eventinfo_tietough'], array()).'</td>
				</tr><tr>';
			}elseif($e['ko_matches']=='yes' && $e['enable_tie']=='yes'){
					$ei['tips'] .= '<td>'.substitute($lang['eventinfo_matches_both_types'], $to_sub_matchtypes).'</td></tr><tr>
					<td/><td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_tournamentmatches']
					.' '.substitute($lang['eventinfo_allpossible'],	array($lang['general_victory'], $lang['general_defeat'], 
					$lang['general_tie'])).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_komatches']
					.' '.substitute($lang['eventinfo_allpossible'],	array($lang['general_victory'], $lang['general_defeat'], 
					$lang['general_tie'])).' '.substitute($lang['eventinfo_tietough'], ' '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_komatches']).'</td>
				</tr><tr>';

			}elseif($e['ko_matches']=='yes' && $e['enable_tie']=='no'){
					$ei['tips'] .= '<td>'.substitute($lang['eventinfo_matches_both_types'], $to_sub_matchtypes).'</td></tr><tr>
					<td/><td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_tournamentmatches']
					.' '.substitute($lang['eventinfo_allpossible'],	array($lang['general_victory'], $lang['general_defeat'], 
					$lang['general_tie'])).'</td>
				</tr><tr>
					<td/>
					<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_in_matches'].' '.$lang['eventinfo_komatches']
					.' '.substitute($lang['eventinfo_tieno'], array($lang['general_victory'], $lang['general_defeat'])); 
					$ei['tips'] .= ' '.$lang['eventinfo_afterpenalties'].' ';
					$ei['tips'] .= ($e['ap_score'] == 'addone') ? 
											$lang['eventinfo_afterpenalties_one'] : 
											$lang['eventinfo_afterpenalties_all'];
					$ei['tips'] .='</td>
				</tr><tr>';
			}

			$bu = preg_split('/:/', $e['bet_until']);
			if ($bu[0] > 1){
				$plural = 's';
			}
			if ($bu[1] == 'm') $bu[1] = $lang['general_minute'.$plural];
			if ($bu[1] == 'h') $bu[1] = $lang['general_hour'.$plural];
			if ($bu[1] == 'd') $bu[1] = $lang['general_day'.$plural];
			$before = ($bu[2] == 'm') ? $lang['eventinfo_match'] : $lang['eventinfo_thefirstmatch'];
			$to_sub_betuntil = Array($bu[0].' '.$bu[1], $before);
			$ei['tips'] .= '<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.substitute($lang['eventinfo_bet_until'], $to_sub_betuntil).' '.$lang['eventinfo_deadline_toolate'].'</td>
		</tr><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.$lang['eventinfo_overview'].'<td>
		</tr></table>';

	//GAIN
	if ($e['stake_mode'] == 'permatch'){
		$subpoint = 1;
		$to_sub3 = array($lang['general_victory'], $lang['general_tie'], $lang['general_defeat'], $e['stake'].' '.$e['currency']);
		$ei['gain'] = $header.'<table class="eventinfo"><tr>
				<td class="topalign"><b>'.$point++.'.</b></td>
				<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_gain_correcttip'], $to_sub1).'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_gain_correcttips'].'</td>
			</tr><tr>';
		if ($e['stake_back'] == 'yes'){
			$ei['gain'] .= '<td class="topalign"><b>'.$point++.'.<b/></td>
				<td>'.$lang['eventinfo_gain_nobodycorrect'].' '.substitute($lang['eventinfo_gain_stakeback'], $to_sub3).'</td>
			</tr></table>';
		}else{
			$ei['gain'] .= '<td class="topalign"><b>'.$point++.'.<b/></td>
				<td>'.$lang['eventinfo_gain_nobodycorrect'].' '.$lang['eventinfo_gain_tojackpot'].'</td>
			</tr></table>';
		}
	}

	//POINTS & JACKPOT
	$subpoint = 1;
	$to_sub5 = ($e['jp_fraction_or_fix'] == 'fix') ? $e['jp_fix'] : ($e['jp_fraction']*100).'% ('.$lang['eventinfo_jackpot_floored'].')';
	$to_sub_exp = array($e['jp_distr_exp_value'], ($e['jp_distr_exp_value']*100).'%');
	$ei['pointsjp'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>'.$lang['eventinfo_points'].'</td>
		</tr><tr>';
		if($e['p_correct'] != NULL){
			$ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_correct'], $e['p_correct']).'</td>
		</tr><tr>';
		}
		if($e['p_diff'] != NULL){
			$ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_diff'], array($lang['general_eg'], $e['p_diff'])).'</td>
		</tr><tr>';
		}
		if($e['p_almost'] != NULL){
			$ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_almost'], array($lang['general_eg'], $e['p_almost'])).'</td>
		</tr><tr>';
		}
		if($e['p_wrong'] != NULL){
			$ei['pointsjp'] .= '<td/>
			<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_points_wrong'], $e['p_wrong']).'</td>
		</tr>';
		}
		if($e['stake_mode']!='none'){
			$subpoint = 1;
			$ei['pointsjp'].='<tr><td class="topalign"><b>'.$point++.'.<b/></td>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_jackpot'].'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.$lang['eventinfo_jackpot_samerank'].'</td>
			</tr><tr>
				<td/>
				<td><b>'.$alphabet[$subpoint++].')</b> '.substitute($lang['eventinfo_jackpot_distributeon'], $to_sub5);
					if($e['jp_distr_algorithm']=='exp'){
						$ei['pointsjp'] .= substitute($lang['eventinfo_jackpot_expformula'], $to_sub_exp);
					}elseif($e['jp_distr_algorithm']=='lin'){
						$ei['pointsjp'] .= $lang['eventinfo_jackpot_linformula'];
					}else{
						$ei['pointsjp'] .= $lang['eventinfo_jackpot_fixformula'];
						$percents = preg_split('/:/',$e['jp_distr_fix_shares']);
						array_pop($percents);
						foreach ($percents as $p){ 
							$rankcounter++;
							$ei['pointsjp'] .= '<br/>'.$rankcounter.'. '.$lang['ranking_rank']
										.': '.$p.'%';
						}
					}
				$ei['pintsjp'].= '</td>
			</tr></table>';
		}else{
			$ei['pointsjp'] .= '</table>';	
		}

	$to_sub7 = array($lang['general_bettingOffice'].' '.$settings['name']);
	$ei['end'] = $header.'<table class="eventinfo"><tr>
			<td class="topalign"><b>'.$point++.'.</b></td>
			<td>';
				if($e['stake_mode'] == 'permatch'){
					$ei['end'] .= $lang['eventinfo_finalaccount_gainplusjp'];
				}elseif($e['stake_mode'] == 'fix'){
					$ei['end'] .= $lang['eventinfo_finalaccount_jp'];
				}else{
					$ei['end'] .= $lang['eventinfo_finalaccount_points'];
				}
			$ei['end'] .= '</td>
		</tr>';
		if(file_exists('data/bo_img/seal@thumb')){ $ei['end'] .=  '</tr>
			<td/>
			<td>'.substitute($lang['eventinfo_sealofapproval'], $to_sub7).'<br/><img padding="10px;"  src="data/bo_img/seal@thumb"/></td>
		</tr>';
		}
		$ei['end'] .= '</table>';
		
	return $ei;

}


function user2column ($playerid, $ev){
	global $events;
	
	
	$userarray = explode(':', $events['p']['e'.$ev]['a']);

	while ($player = current($userarray)) {
		if ($player == $playerid) {
			 return key($userarray)+1;
		}
		next($userarray);

	}
}


function closestGame($ev, $time, $offset=0){

	global $db;

	$data = $db->query("SELECT time FROM ".PFIX."_event_".$ev." ORDER BY time ASC;"); 

	$timetable = $data[0];
	foreach ($data as $row){
			$counter ++;
			$closest[$counter] = abs( $row['time'] - $time);
	}
	asort($closest);
	return key($closest);	

}

function getNewMessage($userid){
	if ($userid == NULL) return "";
	global $db, $lang;
	$data = $db->query("SELECT * FROM ".PFIX."_messages WHERE `receivers` not like \"\" ORDER BY `time` ASC");
	$usersraw = $db->query("SELECT id,login FROM ".PFIX."_users");
	foreach ($usersraw as $u)
		$users[$u['id']] = $u['login'];
	$message = "";
	foreach ($data as $row){
		$receivers = preg_split('/:/', $row['receivers']);
		if(in_array($userid, $receivers)){
			$message['id'] = $row['id'];
			$message['title'] = $row['title'];

			$intro = array( $users[$row['author']], $lang['general_time_at'], date('H:i, Y.m.d', $row['time']));
			$message['content'] .= nl2br($row['content']);

            //$message['footer'] = '<input type="submit" class="btn btn-primary" value="'.$lang['general_readparticipe'].'"/>';
            $message['footer'] .= '<h6>'.substitute($lang['admin_messages_newmessage'], $intro).'</h6>';
            $message['footer'] .=
                '<form name="msg_read" id="msg_read" method="POST" action="">
						<input type="hidden" name="hf_read" value="'.$row['id'].'"/>
		        </form>
		        <a onclick="document.forms[\'msg_read\'].submit();" class="btn btn-primary"> '.$lang['general_readparticipe'].'</a>
		         <button class="btn pull-left" data-dismiss="modal" data-target="#modal_'.$row['id'].'">
                    close
               </button>
               ';
		}
        if ($message != "")
            return $message;
	}
    return $message;
}

function messageRead($msg, $user){
	global $db;
	$data = $db->query("SELECT `receivers`, `read` FROM ".PFIX."_messages WHERE id=".$msg.";");	
	$receivers = preg_split('/:/', $data[0]['receivers']);
	array_pop($receivers);
	$receivers_new = "";
	foreach ($receivers as $r)
		if ($r != $user)
			$receivers_new .= $r.':';
	$read_new = $data[0]['read'].$user.':';
	$db->query("UPDATE ".PFIX."_messages SET `receivers` = '".$receivers_new."', `read` = '".$read_new."' WHERE id=".$msg.";");
}




///found on http://paws.de/blog/2006/12/25/export-php-variables-to-javascript/ 
// licence not clear 
  function php2js($dta) {
    if(is_object($dta))
      $dta = get_object_vars($dta);
    if(is_array($dta)) {
      foreach($dta AS $k=>$d)
        $dta[$k] = php2js($k).":".php2js($d);
      return '{'.implode(',',$dta).'}';
    }
    elseif(is_numeric($dta))
      return "$dta";
    elseif(is_string($dta))
      return '"'.str_replace('"','\\"',$dta).'"';
    else
      return 'null';
  }



function betUntil($bet, $event){
	global $db,$events;
	$data=$db->query("SELECT bet_until FROM ".PFIX."_events WHERE id=".$event.";");
	$bu = preg_split('/:/',$data[0]['bet_until']);
	$min = 60;
	$hour = $min*60;
	$day = $hour*24;

	if($bu[2]=='t'){
		$data=$db->query("SELECT time FROM ".PFIX."_event_".$event." ORDER BY time ASC LIMIT 1;");
		$bet = $data[0]['time'];
	}

	if($bu[1]=='m'){ $before = $min * $bu[0];}
	elseif($bu[1]=='h'){ $before = $hour * $bu[0];}
	elseif($bu[1]=='d'){
		$bet = mktime(0,0,0,date('m',$bet),date('d',$bet),date('Y',$bet));
		$before = $day * ($bu[0]-1);
	}
	return $bet - $before;
}

function isCorrect($points,$a,$b, $c,$d){
	if (($points == NULL) && $points == '') return false;
	if ($a == '') return false;
	//RESULT
	if($d != 'toto'){
		if($a==$c && $b==$d){
			return true;
		}else{
			return false;
		}
	//TOTO
	}else{
		if($c == 'toto'){
			if($a == $b && $a != NULL){
				return true;
			}else{
				return false;
			}
		}else{
			if($a>$b && $c == 1
				|| $a==$b && $c == 3
				|| $a<$b && $c == 2){
				return true;
			}else{
				return false;
			}
		}
	}
}
function isDiff($points,$a,$b,$c,$d){
	if (($points == NULL) || $points == '') return false;
	if ($a == '') return false;
	if($a-$b == $c-$d && $c != '' && $d !=''){
		return true;
	}else{
		return false;
	}
}
function isAlmost($points,$a,$b,$c,$d){
	if (($points == NULL) || $points == '') return false;
	if ($a == '') return false;
	if ($d == 'toto') return false; //invoked from isWrong, toto
	if (($a>$b && $c>$d  && $c!='' && $d!='')
		|| ($a<$b && $c<$d  && $c!='' && $d!='')
		|| ($a==$b && $c==$d  && $c!='' && $d!='')	){

		return true;
	}else{
		return false;
	}
}
function isWrong($points,$a,$b,$c,$d){
	if (($points == NULL) || $points == '') return false;
	if ($a == '') return true;
	if(!isCorrect($points,$a,$b,$c,$d) && !isAlmost($points,$a,$b,$c,$d)){
		return true;
	}else{
		return false;
	}
}

function getResultCSSClass($evinfo,$a,$b,$c,$d) {
	if (isCorrect($evinfo['p_correct'],$a,$b,$c,$d)) {
		return 'correct';	
	}
	elseif (isDiff($evinfo['p_diff'],$a,$b,$c,$d)) {
		return 'diff';	
	}
	elseif (isAlmost($evinfo['p_almost'],$a,$b,$c,$d)) {
		return 'almost';	
	}
	elseif (isWrong($evinfo['p_wrong'],$a,$b,$c,$d)) {
		return 'wrong';
	}
	else {
		return '';
	}
}



function initMail() {
	global $settings, $my_smtp;
	if (!class_exists('PHPMailer')){
		include("src/opensource/phpmailer/class.phpmailer.php");
	}
	$mail = new PHPMailer();
//	$mail->SMTPDebug = true;
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = "ssl";
	$mail->Host = $my_smtp['host'];
	$mail->Port = 465;
	$mail->Username = $my_smtp['username'];
	$mail->Password = $my_smtp['pw'];
	$mail->WordWrap = 50;
	return $mail;
}


function notify($what, $txt){
	global $db, $settings, $my_smtp, $lang;


	$title = $lang['general_notification'].': '.$what;

	if($settings['notification_system'] == 'internal' || $settings['functionalSMTP'] != 'true'){

		$query = "INSERT INTO ".PFIX."_messages (author, receivers, time, title, content) 
						VALUES ('0', 
						'".$settings['admins']."',
						'".time()."',
						'".$title."',
						'".$txt."')";
		$db->query($query);
	}else{
			$mail  = initMail();
			$mail->AddReplyTo($settings['email'], $lang['general_bettingOffice']." ".$settings['name']);
			$mail->From = $my_smtp['from']; 
			$mail->FromName =  $lang['general_bettingOffice']." ".$settings['name'];
			$mail->Subject = $title;
			$mail->IsHTML(true);
			$mail->MsgHTML($txt);
			$mail->AddAddress($settings['email'], $lang['general_bettingOffice']." ".$settings['name']);
			$mail->Send();
	}
}

function weekday($unixtime, $short=0){
	global $lang;
	if ($short) $sh = '_short';
	$digit = date('w', $unixtime);
	
	switch ($digit){
		case 0: return $lang['general_sunday'.$sh];
		case 1: return $lang['general_monday'.$sh];
		case 2: return $lang['general_tuesday'.$sh];
		case 3: return $lang['general_wednesday'.$sh];
		case 4: return $lang['general_thursday'.$sh];
		case 5: return $lang['general_friday'.$sh];
		case 6: return $lang['general_saturday'.$sh];
	}
}

function infoBarEventCreation($p,$s=0){
	global $lang, $events;

	$phase[$p]= 'class="actualphase"';

	$infoBarString = '';
	$infoBarString .=  '<div class="infoBar">';
	$infoBarString .=  '<b>'.$lang['admin_events_createevent'].':</b><br/>';
	$infoBarString .=  '<font '.$phase['1'].'>1. '.$lang['admin_events_createtemplate'].'</font> / 
		<font '.$phase['2'].'>2. '.$lang['admin_events_modeselection'].'</font> / 
		<font '.$phase['3'].'>3. '.$lang['admin_events_scheduleactivation'].'</font>';

	if ($s==2){
		$infoBarString .=  '<br/>'.$lang['admin_events_nextstepdialog'];
		$infoBarString .=  ' <a href="javascript: verify(\'1\')">'
		.$lang['admin_events_nextstep'].'</a>';
	}
	if ($s==3){
/*confirm activation because it's an important irreversible step
		and prevent any apostrophs in the dialog which would turn down the javascript function*/
		$dialog = preg_replace('/\'/', '\\\'', $lang['admin_events_activatedialog']);
		$infoBarString .=  '<br/><a href="javascript: activate(\''.$_REQUEST['ev'].'\', \''.$dialog.'\')">'
			.$lang['admin_events_activate'].'</a>';

		/*confirm activation because it's an important irreversible step
			and prevent any apostrophs in the dialog which would turn down the javascript function*/
	}
	$infoBarString .=  '</div>';
	return $infoBarString;
}

function getStyleInfo($name){
	include('src/style_'.$name.'/info.php');
	return $style;
}

function installLang($langid){
	global $db, $my_db;
	
	$langName = lookupLangName($langid);
	$sql = "INSERT INTO ".PFIX."_langs (`short`,`long`) 
				VALUES ('".$langid."','".$langName."');";
	$db->query($sql);


}

function lookupLangName($sh){
	$filename="data/langs/lang_$sh.xml";
    $xml = simplexml_load_file($filename);
    foreach($xml->zock_lang as $entry) {
        $lab = (string) $entry->label;
		if ($lab == 'general_thislanguage') {
			return (string) $entry->$sh;
		}
	}
}

function osort(&$array, $prop)
{
    usort($array, function($a, $b) use ($prop) {
        return $a->$prop > $b->$prop ? 1 : -1;
    });
}

?>
