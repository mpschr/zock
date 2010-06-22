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
		$dtl = split(':', $_COOKIE['dtl']);
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
			$styles = split(':', $setting['style_forusers']);
			array_pop($styles);
			$usets = loadSettings($_SESSION['userid']);
			if ($usets['style']!="" && in_array($usets['style'],$styles))
				$setting['style'] = $usets['style'];
		}else{
			if(isset($_COOKIE['syle'])){
				$styles = split(':', $setting['style_forusers']);
				array_pop($styles);
				if ($COOKIE['style']!="" && in_array($_COOKIE['style'],$styles))
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

function siteConstructor($action, $title_or_site=NULL, $desc_or_menu=NULL, $keys=NULL){
	//creates the site from top to bottom...a forwarder function
	global $lang;
	switch($action){
		case 'header':
			$title = $title_or_site;
			$desc = $desc_or_menu;
			if($keys == NULL); $keys = $desc;
			include_once('src/header.php');
			break;
		case 'body':
			$site = $title_or_site;
			$menu = $desc_or_menu;
			include_once('src/body.php');
			break;
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
$lang_raw = $langObj->query("SELECT * FROM ".PFIX."_lang");
foreach($lang_raw as $entry){
	$lang[$entry['label']] = $entry[$standard];
	//If this entry is not available in the standard language
	if ($entry[$standard] == NULL){
		if($entry['en'] != NULL){
			$lang[$entry['label']] = $entry['en'];
		}else{
			$lang[$entry['label']] = 'EMPTY: '.$entry['label'];
		}
	}
}
return $lang;	
}

function languagesReader(){
	//returns an array with all the languages as short & long form

	$langObj = new bDb;	
	
	//the long ones with short ones as label
	$languages = $langObj->query("SELECT * FROM ".PFIX."_lang WHERE label = 'general_thislanguage'");
	
	$shorts = array_keys($languages[0]);
	$i = 0;
	foreach ($languages[0] as $el){
		if ($i > 0){
			$prelangs['long'][] = $el;
			$prelangs['short'][] = $shorts[$i];
		}
		$i++;
	}
	
	$flip = $copy = $prelangs['long'];
	sort($copy);
	$flip = array_flip($flip);
	foreach($copy as $c){
		$langs['short'][] = $prelangs['short'][$flip[$c]];
		$langs['long'][] = $c;
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
			redirect('loginhome', 3);
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
					'myprofile',
					'loginhome',
					'logout');
					
	$menus['admin'] = array(	'settings',
					'events',
					'lang',
					'messages');
					
	$menus['events'] = array (	'settings',
					'matches',
					'results');
					
	$menus['myprofile'] = array(	'settings',
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



	//if just a menu is demanded or the submenus are ok. Logged users should not
	//call the unlogged menus (as login, register....)
	
	//visitor calls visitor menu
	if (!($_SESSION['logged']) && in_array($requested_menu, $menu['normal']))
		return TRUE;	
		
	//user calls user menu (or admin calls user menu)
	elseif($_SESSION['logged'] && in_array($requested_menu, $menu['logged']))
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

	//creat the main Horizontal menu
	if($submenu==NULL){

		//add the admin button for the admin
		if ($admin){
			$linktype = ($_REQUEST['menu'] == 'admin') ? 'menulinksel' : 'menulink';
			echo '<a class="'.$linktype.'" id="admin" href="index.php?menu=admin">
				<img src="src/style_'.$stl.'/btn_admin.'.$style['btn_format'].'" alt="'.$lang['admin_title'].'"/>
				</a>';
		}
		//normal menu (@ page entry)
		if (!($logged)){
			foreach($menus['normal'] as $nm){
				$linktype = 'menulink';
				if ($_REQUEST['menu'] == $nm) $linktype = 'menulinksel';
				echo '<a class="'.$linktype.'" id="'.$nm.'" href="index.php?menu='.$nm.'">
					<img src="src/style_'.$stl.'/btn_'.$nm.'.'.$style['btn_format'].'" alt="'.$lang[$nm.'_title'].'"/>
					</a>';
			}
		//the logged user menu
		}else{
			foreach($menus['logged'] as $nm){
				$linktype = 'menulink';
				if ($_REQUEST['menu'] == $nm) $linktype = 'menulinksel';
				echo '<a class="'.$linktype.'" id="'.$nm.'" href="index.php?menu='.$nm.'">
					<img src="src/style_'.$stl.'/btn_'.$nm.'.'.$style['btn_format'].'" alt="'.$lang[$nm.'_title'].'"/>
					</a>';
			}
		}
	//make the submenu line
	}else{
		foreach($menus[$submenu] as $nm){
			$linktype = 'menulink';
			if ($_REQUEST['submenu'] == $nm) $linktype = 'menulinksel';
			echo '<a class="'.$linktype.'" id="'.$nm.'" href="index.php?menu='.$submenu.'&submenu='.$nm.'">
				<img src="src/style_'.$stl.'/btn_'.$submenu.'_'.$nm.'.'.$style['btn_format'].'" alt="'.$lang[$submenu.'_'.$nm.'_title'].'"/>
				</a>';
		}
	}
}

function createVerticalMenu($vmenu=NULL, $option=NULL){
	//the content of this menu is quite variable
	$menus = menus();
	global $lang, $link, $link_query, $ssm, $events;
	if ($vmenu != NULL){
		echo '<div id="menu_v">';
		foreach($menus[$vmenu] as $vm){
			// the $link has already a ssmenu in it.. so we have to replace it for creating links
			$sslink = ereg_replace('ssubmenu='.$ssm, 'ssubmenu='.$vm, $link.$link_query);
			echo '-<a class="vmenulink" href="'.$sslink.'">'.$lang['admin_'.$vmenu.'_'.$vm.'_title'].'</a>';
			echo '<br>';
		}
		echo '</div>';
	}
	//ueventlist lists events where user is registered to
	//peventlist lists events which are set public (minus userevents)
	elseif (preg_match("(ueventlist|peventlist)", $option )){
		$possibleEventsArray = LoadUserEvents();
		echo '<div id="menu_v">';
		if(isset($_SESSION['userid'])){
			$possibleEvents = explode(':', $possibleEventsArray['approved']);
			array_pop($possibleEvents);
		
			if(UserEventNumber() > 0) echo $lang['general_yourevents'].'<br />';
			foreach($possibleEvents as $e){
				$eventlink = $link.'ev='.$e;
				echo '-<a class="vmenulink" href="'.$eventlink.'">'.$events['u']['e'.$e]['name'].'</a>';
				echo '<br>';
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
				echo (UserEventNumber > 0) ? $lang['general_furtherevents'] : $lang['general_publicevents'];
				echo '<br />';
				foreach($possibleEvents as $e){
					$eventlink = $link.'ev='.$e;
					echo '-<a class="vmenulink" href="'.$eventlink.'">'.$events['p']['e'.$e]['name'].'</a>';
					echo '<br>';
				}
			}
		}	
		echo '</div>';
	}

	//the following 2 div's serve for mini-/maximizing the v_menu (both are necessary);
	elseif($option == 'mmclose'){
		echo '<div id="menu_v_mmc" class="menu_v_mm">';
			echo '<a class="vmenulink" href="javascript: menuMM()" title="'.$lang['general_minimize'].'">></a>';
		echo '</div>';
	}
	elseif($option == 'mmopen'){
		echo '<div id="menu_v_mmo" class="menu_v_mm">';
			echo '<a class="vmenulink" href="javascript: menuMM()" title="'.$lang['general_maximize'].'"><</a>';
		echo '</div>';
	}
}


function phpManageUser($user, $what, $event){
	//if the admin aproves/refuses users, the different strings in the db have to be changed
		//=> a string is of the form 1:2:3: (for user 1, 2 & 3)
	global $db;
	$data = $db->query("SELECT users_approved, users_waiting, users_denied FROM ".PFIX."_events WHERE id='$event'");
	$approved = $data[0]['users_approved'];
	$waiting = $data[0]['users_waiting'];
	$denied = $data[0]['users_denied'];
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
	}
	return "UPDATE ".PFIX."_events SET users_approved = '$approved', users_waiting = '$waiting', users_denied = '$denied' WHERE id = '$event';";
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

function orderIt($what, $o, $lq){
	//this function cleans the links for the links where you can order a table	
		//=> before using this function, the array $orderby must've been created!
	$str = eregi_replace('(orderby=)(.*)(ASC|DESC)[& ]', '', $lq);
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

function substitute($string,$subarray){
	preg_match_all('(\$[0-9]+)', $string, $nb);
	return str_replace($nb[0], $subarray, $string);
}

function styleLogo(){
	global $style, $settings;
	if (isset($style['logo']))
		echo '<div id="style_logo"><a href="'.$settings['site_url'].'"><img src="src/style_'.$settings['style'].'/img/'.$style['logo'].'" /></a></div>';
}

function styleComment(){
	global $style;
	if ($style['comment'])
		echo '<div id="style_comment">'.$style['comment'].'</div>';
}

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

			$bu = split(':', $e['bet_until']);
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
						$percents = split(':',$e['jp_distr_fix_shares']);
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
	$data = $db->query("SELECT * FROM ".PFIX."_messages");
	$usersraw = $db->query("SELECT id,login FROM ".PFIX."_users");
	foreach ($usersraw as $u)
		$users[$u['id']] = $u['login'];
	$message = "";
	foreach ($data as $row){
		$receivers = split(':', $row['receivers']);
		if(in_array($userid, $receivers)){
			$message['id'] = $row['id'];
			$message['title'] = $row['title'];

			$intro = array( $users[$row['author']], $lang['general_time_at'], date('H:i, Y.m.d', $row['time']));
			$message['content'] = '<div class="message_intro">'.substitute($lang['admin_messages_newmessage'], $intro).'</div>';
			$message['content'] .= '<p/>'.nl2br($row['content']);
			$message['content'] .= '<form name="msg_read" method="POST" action="">
						<input type="hidden" name="hf_read" value="'.$row['id'].'"/>
						<input type="submit" value="'.$lang['general_readparticipe'].'"/>
						</form>';
		}
	}
	return $message;
}

function messageRead($msg, $user){
	global $db;
	$data = $db->query("SELECT `receivers`, `read` FROM ".PFIX."_messages WHERE id=".$msg.";");	
	$receivers = split(':', $data[0]['receivers']);
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
	$bu = split(':',$data[0]['bet_until']);
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
	if ($points == '') return false;
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
	if ($points == '') return false;
	if ($a == '') return false;
	if($a-$b == $c-$d && $c != '' && $d !=''){
		return true;
	}else{
		return false;
	}
}
function isAlmost($points,$a,$b,$c,$d){
	if ($points == '') return false;
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
	if ($points == '') return false;
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


function rankingCalculate ($ev, $until=""){
global $db, $events;

$queryfield = ($events['u']['e'.$ev]['score_input_type'] == 'results') ? 'score_h' : 'score';

//select from db ordered by time and only matches with results!
//*simulation*/ /*
$query = "SELECT * FROM ".PFIX."_event_".$ev." WHERE ".$queryfield." IS NOT NULL ORDER BY time ASC;";
if($until!="") {
	$u = split(':',$until);
	if($u[0] == 'matchday_id') 
		$query = "SELECT * FROM ".PFIX."_event_".$ev." 
			WHERE matchday_id <= '".$u[1]."' 
			AND ".$queryfield." IS NOT NULL 
			ORDER BY matchday_id ASC, time ASC;";
	elseif($u[0] == 'date')
		$query = "SELECT *,FROM_UNIXTIME(time, '%Y%m%d') as vdate 
			FROM ".PFIX."_event_".$ev." 
			WHERE FROM_UNIXTIME(time, '%Y%m%d') <= ".$u[1]."
			AND ".$queryfield." IS NOT NULL 
			ORDER BY time ASC ;";
	elseif($u[0] == 'match')
		$query = "SELECT * FROM ".PFIX."_event_".$ev." 
			WHERE ".$queryfield." IS NOT NULL 
			ORDER BY time ASC LIMIT 0, ".$u[1].";";
}
$pastmatches =  $db->query($query);
/*$rows =  $db->row_count($query);
//if there are no matches yet for this event
if ($rows == 0) {
	echo $lang['general_nomatches'];
}else{*/
	$evUsNb = eventUserNumber($ev);
	foreach($pastmatches as $pm){
			foreach($pm as $label => $info){
				// accumulate points and money for each user and the jackpot
				if(substr($label, -7) == '_points') {
					$nick = substr($label,0,-7);
					$evdet = $events['u']['e'.$ev];
					$points[$nick] += $info;
					if($info == $evdet['p_correct']) $correct[$nick]++;
					if($info == $evdet['p_diff']) $diff[$nick]++;
					elseif($info == $evdet['p_almost']) $almost[$nick]++;
					elseif($info == $evdet['p_wrong']) $wrong[$nick]++;
					if(!isset($correct[$nick])) $correct[$nick] = 0;
					if(!isset($diff[$nick])) $diff[$nick] = 0;
					if(!isset($almost[$nick])) $almost[$nick] = 0;
					if(!isset($wrong[$nick])) $wrong[$nick] = 0;
				}
				if(substr($label, -6) == '_money') $money[substr($label, 0, -6)] += $info;
				if($label == 'jackpot') $jackpot += $info;
				// evaluate rank (everytime overwritten, last is acutal);
				if(substr($label, -8) == '_ranking') $rank[substr($label, 0, -8)] = $info;
			}
	}
//*simulation*/ $evUsNb = 10; 
//*simulation*/ $jackpot = 189; 
//*simulation*/ $points = array( 50,  99, 10, 0, 5, 45, 12, 15, 66, 50);
//*simulation*/ $money = array( 50,  99, 10, 0, 5, 45, 12, 15, 66, 50);
//*simulation*/ $rank = array( 3,  1, 8, 10, 9, 5, 7, 6, 2, 3);
 

	// get further info for the event
	$event_info = $db->query("SELECT * FROM ".PFIX."_events WHERE id='".$ev."'");
	if($event_info[0]['stake_mode']=='fix') $jackpot = $evUsNb*$event_info[0]['stake'];


//*simulation*/ $event_info[0]['jp_fraction_or_fix'] = 'fix';
//*simulation*/ $event_info[0]['jp_fraction'] = 1;
//*simulation*/ $event_info[0]['jp_fix'] = 5;
//*simulation*/ $event_info[0]['jp_distr_algorithm'] = 'fix';
//*simulation*/ $event_info[0]['jp_distr_exp_value'] = '0.6';
//*simulation*/ $event_info[0]['jp_distr_fix_shares'] = '0.5:0.3:0.1:0.07:0.03';

	//estimate the number of players sharing the jackpot: either fraction or fix number
	$jackpotters = ($event_info[0]['jp_fraction_or_fix'] == 'fraction') ? 
				floor($evUsNb*$event_info[0]['jp_fraction']) :
				$event_info[0]['jp_fix'];
	//make a linear,exponential or fix distribution: divide jackpot into tiny pieces for distribution
		//!fix is only possible if also a fix number of jackpot sharers are set!

	$round = $event_info[0]['round'];
	$reciprocal = 1/$round;
	$jackpots[] = array();
	$counter = 0;

	switch ($event_info['0']['jp_distr_algorithm']){
		case 'lin': //linear distribution
			for($j = $jackpotters; $j > 0; $j--) $jackpotparts += $j;
			$singlepart = $jackpot/$jackpotparts;
			for($j = $jackpotters; $j > 0; $j--){
				$counter++;
				$jackpots[$counter] = (round($reciprocal*($singlepart * $j))/$reciprocal);
			}
			break;
		case 'exp': //exponential distribution
			while(true){
				for($j = $jackpotters; $j > 0; $j--){
					$counter++;
					$jackpots[$counter] += (round($reciprocal*($event_info[0]['jp_distr_exp_value']*($jackpot-array_sum($jackpots))))/$reciprocal);
					$difference = ($jackpot-array_sum($jackpots)) - $round;
					//because $difference is a floating point binary, we have to check for a range, not an absolute value!
					if ($difference <= 0.001){
						if ($difference > -0.001)
							if($counter!=$jackpotters) {
								$jackpots[$counter+1] += $round;
							}else{
								break;
							}
						if ($counter == $jackpotters) break 2;
					}
				}
				$counter = 0;
			}
			break; //end of case 'exp'
		case 'fix': //fix shares
			$fix_shares = split(':', $event_info[0]['jp_distr_fix_shares']);
			for($j = $jackpotters; $j > 0; $j--){
				$counter++;
				$jackpots[$counter] = (round($reciprocal*(($fix_shares[$counter-1]/100)*$jackpot))/$reciprocal);
			}
			break;
	}

	//gather info for check if multiple users on the same rank
	//and divde their jackpots equally plus forward the undividable part;
	foreach($rank as $r) $rank_quantity[$r]++;
	for($p=1; $p<=$jackpotters; $p++){
		if ($rank_quantity[$p] > 1){
			$accumulate = 0;
			for($q = $p; $q < $p + $rank_quantity[$p]; $q++) {
				if ($p<=$q) $accumulate += $jackpots[$q]; //add jackpots of the empty ranks to concerning one
				if ($q > $p) unset($jackpots[$q]);  //no jackpot for the ranks where's *nobody on*
			}
			$jackpots[$p] = floor(($reciprocal* $accumulate) / $rank_quantity[$p]) /$reciprocal;
			$undividable = $accumulate - $rank_quantity[$p]*$jackpots[$p];	
			if ($jackpots[$p] != pos($jackpots)) while (next($jackpots) != $jackpots[$p]);
			while($undividable > 0.0001){
				$next = next($jackpots);
				if(array_search($next, $jackpots) != $p){
					$player = (int)array_search($next, $jackpots);
					$jackpots[$player] = (float)$jackpots[$player] + (float)$undividable;
					$undividable = 0;
				}
			}
		}
	}
	$info = Array();
	$info['money'] = $money;
	$info['jackpots'] = $jackpots;
	$info['r_quant'] = $rank_quantity;
	$info['rank'] = $rank;
	$info['points'] = $points;
	$info['correct'] = $correct;
	$info['diff'] = $diff;
	$info['almost'] = $almost;
	$info['wrong'] = $wrong;
	$info['pastmatches'] = sizeof($pastmatches);
	$info['totalmatches'] = $db->row_count("SELECT id FROM ".PFIX."_event_".$ev."; ");
	return $info;
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

	echo '<div class="infoBar">';
	echo '<b>'.$lang['admin_events_createevent'].':</b><br/>';
	echo '<font '.$phase['1'].'>1. '.$lang['admin_events_createtemplate'].'</font> / 
		<font '.$phase['2'].'>2. '.$lang['admin_events_modeselection'].'</font> / 
		<font '.$phase['3'].'>3. '.$lang['admin_events_scheduleactivation'].'</font>';

	if ($s==2){
		echo '<br/>'.$lang['admin_events_nextstepdialog'];
		echo ' <a href="javascript: verify(\'1\')">'
		.$lang['admin_events_nextstep'].'</a>';
	}
	if ($s==3){
/*confirm activation because it's an important irreversible step
		and prevent any apostrophs in the dialog which would turn down the javascript function*/
		$dialog = ereg_replace('\'', '\\\'', $lang['admin_events_activatedialog']);
		echo '<br/><a href="javascript: activate(\''.$_REQUEST['ev'].'\', \''.$dialog.'\')">'
			.$lang['admin_events_activate'].'</a>';

		/*confirm activation because it's an important irreversible step
			and prevent any apostrophs in the dialog which would turn down the javascript function*/
	}
	echo '</div>';
}

function getStyleInfo($name){
	include('src/style_'.$name.'/info.php');
	return $style;
}

function installLang($sh){
	global $db, $my_db;
	$db->query("ALTER TABLE ".$my_db['prefix']."_lang ADD COLUMN `".$sh."` longtext collate utf8_unicode_ci");
	$handle = fopen("data/langs/lang_".$sh.".xml", "r");
	if ($handle) {
		while (!feof($handle)) {
			$xmlCNT .= fgets($handle, 4096);
		}
		fclose($handle);
	}
	$p = xml_parser_create();
	xml_parse_into_struct($p, $xmlCNT, $vals, $index);
	$langcode = strtoupper($sh);
	foreach($vals as $v){
		if($v['tag']=='LABEL') $l = $v['value'];
		if($v['tag']==$langcode) {
			$i = $v['value'];
			$db->query("UPDATE ".$my_db['prefix']."_lang 
					SET `".$sh."`= '".mysql_real_escape_string($i)."' 
					WHERE label LIKE '".$l."';");
		}
	}

}

function lookupLangName($sh){
	$handle = fopen("data/langs/lang_".$sh.".xml", "r");
	if ($handle) {
		while (!feof($handle)) {
			$xmlCNT .= fgets($handle, 4096);
		}
		fclose($handle);
	}
	$p = xml_parser_create();
	xml_parse_into_struct($p, $xmlCNT, $vals);
	xml_parser_free($p);
	$next = false;
	$langcode = strtoupper($sh);
	foreach($vals as $v){
		if($v['value']=='general_thislanguage') $next = true;
		if($next && $v['tag']==$langcode) {
			return $v['value'];
		}
	}

}

?>
