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
		$fotoup = $_FILES['fotoup'];
		$file_upload = 'ok';

		//Check Size

		if ($fotoup['size'] > 200000){
			echo errorMsg('myprofile_appearance_picsize');
			$file_upload="toobig";
		}

		//Check File-extension
		if (!($fotoup['type'] == "image/jpeg" || $fotoup['type'] == "image/png")){
			echo errorMsg('myprofile_appearance_jpgonly').'huhu';
			$file_upload="false";
		}


		//1. generate picture name,2. save the pictures,3. delete old pictures, 4. save pic-name in db
		$picid = generatePassword(12); //1.
		$add='data/user_img/';
		if (is_writeable('data/user_img/')){
			if($file_upload == 'ok'){
				while(file_exists($add.$picid)){
					$picid = generatePassword(12);	
				}
				
				if(move_uploaded_file ($fotoup['tmp_name'], $add.$picid) 
					&& thumbnailer($add.$picid, $add.$picid.'@thumb')){	//2.
					if ($mysettings['picture'] != NULL){ 
						chmod ($add.$mysettings['picture'], 0777);
						chmod ($add.$mysettings['picture'].'@thumb', 0777);
						@unlink($add.$mysettings['picture']);	//3.
						@unlink($add.$mysettings['picture'].'@thumb');
					}
					//4.
					$query = "UPDATE ".PFIX."_users SET picture = '".$picid."' WHERE id = '".$_SESSION['userid']."'";
					$db->query($query);
//					$db->query("UPDATE ".PFIX."_users SET picture = '".$picid."' WHERE id = '".$_SESSION['userid']."'");
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
