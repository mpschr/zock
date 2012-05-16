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

echo '<h2>'.$lang['comments_title'].'</h2>';


global $db, $settings, $events;


//event handling ;) => estimate if user is registerd to events
//=> in how many events is user participating, and which ones?
$nb =  UserEventNumber();
$userevents = loadUserEvents();
if($nb < 1){
	//none...give some info
	echo $lang['comments_participatefirst'];
	
}elseif($nb == 1){
	//display his only event
	$thisevent = preg_replace('/([0-9]+):$/', '\\1', $userevents['approved']);
	
}elseif($nb > 1){
	//allow the user to navigate between his events
	createVerticalMenu(NULL, 'ueventlist');
	createVerticalMenu(NULL, 'mmopen');
	createVerticalMenu(NULL, 'mmclose');
	//the session variable currevent must either a public event or the user participates. It can be in the session
	//after having looked at a public event in the overview section
	(isset($_SESSION['currevent']) && userParticipates($_SESSION['currevent'], $_SESSION['userid'])) ? 
			$thisevent = $_SESSION['currevent'] : $thisevent = preg_replace('/.*:([0-9]+):$/', '\\1', $userevents['approved']);
}


//$_REQUEST['ev'] overrules the insight of the event handling :)
if (!(isset($_REQUEST['ev']))) $_REQUEST['ev'] = $thisevent;
//update the current event variable in Session
$_SESSION['currevent'] = $_REQUEST['ev'];

//when a curious user modiefies the url...
if(!userParticipates($_REQUEST['ev']) && $nb > 0){
	//if the user is registered to an event and tries to view the comments of another event
	errorPage('notinevent');
}else{
	
	//finally, the comments
	if($nb >= 1 && !(isset($_REQUEST['coac']))){
		echo '<h3>'.$events['u']['e'.$_REQUEST['ev']]['name'].'</h3>';

		//error handling
		if(isset($_SESSION['err'])){
			echo errorMsg($_SESSION['err']);
			unset ($_SESSION['err']);
		}

		//get user names to display them
		$users_raw = $db->query("SELECT id, login, picture  FROM ".PFIX."_users;");
		foreach ($users_raw as $u){
			$user[$u['id']] = $u['login'];
            $idx = strrpos($u['picture'],'.');
            $fext = substr($u['picture'],$idx);
            $fn = substr($u['picture'],0,$idx);
            $picture[$u['id']] = $fn.'@thumb'.$fext;
		}

		//choose all top-level comments (parent_id = 0)
		$query="SELECT * FROM ".PFIX."_comments WHERE event = '".$_REQUEST['ev']."' AND parent_id = '0' ORDER BY time DESC;";
		if(!$db->row_count($query)>0){
			$lang['comments_nocomments'];
		}else{
			$done['dummy'] = 'dummy';
			
			//get comments
			$comments_raw = $db->query($query);
			

			//skip pages
			echo '<div align="center">';
			$cnb = $_REQUEST['cnb'];
			if ($cnb == 0) $cnb =1;
			$cmts = sizeof($comments_raw);
			if (!(isset($err))){
				$filter = preg_replace( '/cnb=([0-9]+)([& ])/', '', $link_query);
				if($cnb > 1){
					$gonb = $cnb-5;
					if ($gonb < 1) $gonb = 1;
					echo '<a href="'.$link.$filter.'cnb='.$gonb.'">'.$lang['general_goback'].'</a> | ';
				}
		
				echo $lang['general_page'];
				for($x=1 ; $x <= $cmts; $x += 5){
					$y++;
					if ($x!=$cnb){
						echo '  <a href="'.$link.$filter.'cnb='.$x.'">'.$y.'</a>';
					}else{
						echo '  '.$y;
					}
				}
			}
			if($cnb + 5 < $cmts){
				$gonb = $cnb+5;
				if ($gonb > $cmts) $gonb = $cmts;
				echo ' | <a href="'.$link.$filter.'cnb='.$gonb.'">'.$lang['general_goforward'].'</a>';
			}
			echo ' | <a href="#comment">'.$lang['general_write'].'</a></div><p/>';


			//show comments
			//comment depth (threading)
			$depth = 7;
			$counter = 1;
			foreach ($comments_raw as $cmt){
				if ($counter >= $_REQUEST['cnb'] && $counter < $_REQUEST['cnb'] + 5)
					cmtDisplay($cmt, $user, $depth,$picture);
				$counter++;
			}
		}

		echo '<hr />';

		//the form
		echo '<div class=showform>';
		echo '<form method="POST" action="?menu=comments&coac=savecomment">';

		//hiddenfields for javascript function
		echo '<input type="hidden" id="hf_comment" value="'.$lang['comments_comment'].'">';
		echo '<input type="hidden" id="hf_answer" value="'.$lang['comments_answer'].'">';
		echo '<input name="parent_id" type="hidden" id="hf_parentid" value="0">';

		//comment title
		echo '<div class="title">'.$lang['comments_commenttitle'].'</div>';
		echo '<div class="input"><input name="title" id="title" value=""></div>';
		//comment text
		echo '<div class="title" id="comment">'.$lang['comments_comment'].'</div>';
		echo '<div class="input"><textarea name="text" cols="40" rows="7"></textarea></div>';
		
		echo '<div class="submit"><input type=submit value="'.$lang['general_save'].'"></div>';
		echo '</form>';
		echo '</div>';

//=== save a comment		
	}elseif($_REQUEST['coac'] == 'savecomment'){
		$data = $_POST;
		
		//only one case of comment refusing
		if ($data['text'] == ''){
			$_SESSION['err'] = 'commentsave';
			redirect($rlink);
			
		}else{
			
			//update
			if ($data['title'] == '') $data['title'] = '-';
			$db->query("INSERT INTO ".PFIX."_comments (time, title, text, user, event, parent_id)
					VALUES ('".time()."',
						'".$data['title']."',
						'".$data['text']."',
						'".$_SESSION['userid']."',
						'".$_REQUEST['ev']."',
						'".$data['parent_id']."');");
			redirect($rlink); //go back
		}


	}
}

function cmtDisplay($cmt, $user, $depth, $pics){
	//this function's necessary to be able to thread comments.
	
	global $db, $lang;

	//regulate the depth, which is set to 7
	$depth--;
	
	echo '<a name="'.$cmt['id'].'"><div class="comment"></a>';
	//tilteline
	echo '<div class="cmttitle"';
	echo '<div class="cmttitler">'.$lang['general_by'].' '.$user[$cmt['user']].'</div>';
	echo '<div class="cmttitlel">'.$cmt['title'].'</div>';
	echo '</div>';
	//comment	
    $userpic = $pics[$cmt['user']];
	//echo '<div class="cmttextbg">';
	echo '<div class="cmttextbg" style="background-image: url(\'data/user_img/'.$userpic.'\')">';
	echo '<div class="cmttext">';
    echo nl2br($cmt['text']).'</div>';
   // echo '<img src="data/user_img/'.$userpic.'" />';
    //echo '<br/> <br/>';
    echo '</div>';
	echo '<div class="cmtfooter">';

	//is it still possible to answer this comment?
	if($depth > 0)
		echo '<div class="cmtfooterr"><a href="javascript: 
			replyToCmt(\''.$cmt['id'].'\', \''.$cmt['title'].'\')">'
			.$lang['comments_reply'].'</a></div>';
			
	echo '<div class="cmtfooterl">'.weekday($cmt['time']).', '.date("d.m.Y - H:i" ,$cmt['time']).'</div>';
	echo '</div>';

	//threading
	$query =  "SELECT * FROM `".PFIX."_comments` WHERE `parent_id` = ".$cmt['id']." ORDER BY time ASC;";
	$parentcmt = $db->query($query);
	foreach($parentcmt as $cmt){
		cmtDisplay($cmt, $user, $depth,$pics);
	}
	echo '</div>';
}



?>
