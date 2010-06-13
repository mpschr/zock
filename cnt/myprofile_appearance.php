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

global $db, $events;

//load the user specific settings
$mysettings = loadSettings($_SESSION['userid']);

if(isset($_REQUEST['ac'])){

	$data = $_POST;

	//update the text the user put in the field anyway
	if($_REQUEST['ac'] == 'app_text' && $data['text'] != NULL)
		$db->query("UPDATE ".PFIX."_users SET text = '".$data['text']."' WHERE id = '".$_SESSION['userid']."'");

	//picture uplaod
	if($_REQUEST['ac'] == 'app_pic'){
		$fotoupIn = $_FILES['fotoup'];
		$file_upload = 'ok';

		//Check Size

		if ($fotoupIn['size'] > 200000){
			echo errorMsg('myprofile_appearance_picsize');
			$file_upload="toobig";
		}

		//Check File-extension
		if (!($fotoupIn['type'] == "image/jpeg" || $fotoupIn['type'] == "image/png" || $fotoupIn['type'] == "image/gif")){
			echo errorMsg('myprofile_appearance_jpgonly').'huhu';
			$file_upload="false";
		}
		$fileexts["image/jpeg"] = ".jpg";
		$fileexts["image/png"]  = ".png";
		$fileexts["image/gif"]  = ".gif";
		$fext = $fileexts[$fotoupIn['type']];

		//1. generate picture name,2. save the pictures,3. delete old pictures, 4. save pic-name in db
		$picid = generatePassword(12); //1.
		// add file extension
		$add='./data/user_img/';
		if (is_writeable('./data/user_img/')) {
    
			if($file_upload == 'ok'){
				while(file_exists($add.$picid.$fext)){
					$picid = generatePassword(12);	
				}
				if(move_uploaded_file ($fotoupIn['tmp_name'], $add.$picid.$fext) 
					&& thumbnailer($add.$picid.$fext, $add.$picid.'@thumb'.$fext)){	//2.
					if ($mysettings['picture'] != NULL){
						$idx = strrpos($mysettings['picture'],'.');
						$fextOld = substr($mysettings['picture'],$idx);
						$fn = substr($mysettings['picture'],0,$idx);
						chmod ($add.$fn.$fextOld, 0777);
						chmod ($add.$fn.'@thumb'.$fextOld, 0777);
						@unlink($add.$fn.$fextOld);	//3.
						@unlink($add.$fn.'@thumb'.$fextOld);
					}
					
					//4.
					$query = "UPDATE ".PFIX."_users SET picture = '".$picid.$fext."' WHERE id = '".$_SESSION['userid']."'";
					$db->query($query);
//					$db->query("UPDATE ".PFIX."_users SET picture = '".$picid.$fext."' WHERE id = '".$_SESSION['userid']."'");
					chmod ($add.$picid, 0777); 

					echo $lang['general_savedok'];
				}else{
					echo $lang['general_savednotok'];
				}
			}
			
		}
	}

	//go back
	echo ' '.$lang[general_redirect];
	redirect($rlink, 3);

}else{
	
	//display the appearance of the user
	$imgsrc = 'data/user_img/'.$mysettings['picture'];
	@$imgsize = getimagesize($imgsrc);
	if($imgsize[0] > 270){
		$origwidth = $imgsize[0];
		$imgsize[0] = 270;
		$change = $imgsize[0]/$origwidth;
		$imgsize[1] = $imgsize[1]*$change;
	}
	echo '<div class="appearance">
		<img title="'.$details[0]['login'].'" src="'.$imgsrc.'" alt="'.$lang['myprofile_appearance_nopicture'].'" width="'.$imgsize['0'].'px" height="'.$imgsize[1].'px">
		<br/>
		<font class="piccomment">"'.$mysettings['text'].'</font>
	</div>';
		
	
	echo '<p><hr><p>';

	//error handlin
	if (isset($_REQUEST['error'])) echo '<font class=error>'.$lang['error_myprofile_settings'].'</font><p>';

	echo $lang['myprofile_appearance_content'];
	
	//the form for the pic
	echo '<form name="app_pic" action="'.$link.'ac=app_pic" method="POST" enctype="multipart/form-data">
		<table class="showform">
			<tr>
				<td class="title">'.$lang['myprofile_appearance_picture'].'</td>
			</tr><tr>
				<td class="input"><input name="fotoup" size="40" type="file"></td>
			</tr><tr>
				<td class="input"><input type="submit" value='.$lang['general_upload'].'></td>
			</tr>
		</form>';
	
	//the form for the text
	echo '<form name="app_text" action="'.$link.'ac=app_text" method="POST">
			<tr>
				<td class="title">'.$lang['myprofile_appearance_text'].'</td>
			</tr><tr>
				<td class="input"><textarea name="text" rows="10" cols="30">'.$mysettings['text'].'</textarea></td>
			</tr><tr>
				<td class="input"><input type="submit" value="'.$lang['general_savechanges'].'"></td>
			</tr>
		</table>
	</form>';
}
?>
