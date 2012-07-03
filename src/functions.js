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
Function index:

>languageChange
>activate
>manageUser
>userPaid
>manageEvent
>addNewMatch
>editResult
>menuMM
>replyToCmt
>scrollSynch
>setOverBG
>unsetOverBG
>switchToActivatedBG
>addReceivers
>removeReceiver
>checkMessage
>findPos
>floatingLayer
>Clock
>filterFunctions
>SVGLoader
*/

function languageChange(url, dialog){
// gets the language from the select form in the footer & sends to change the language
	//=> called from every site

	lang=document.getElementById('langCh').value
	document.location = url + "&langchange=" + lang
}

function activate(event, dialog){
//a little dialog, because activating an event is a very important step
	//=> called in admin_event_settings

	if (confirm(dialog)){
		document.location = "index.php?menu=admin&submenu=events&evac=activate&ev=" + event
	}
}

function manageUser(what, id){
//sets the hiddenfield of the user to wathever value it should be changed to
	//=> called in admin_events_settings
	
	var user = "uw_" + id
	var hiddenfield = "hf_" + id
	switch (what){
		case "a":
			fcolor = "green"
			fweight = "bold"
			hfvalue = "1"
			break;
		case "d":
			fcolor = "red"
			fweight = "bold"
			hfvalue = "-1"
			break;
		case "w":
			fcolor = "black"
			fweight = "normal"
			hfvalue = "0"
			break;
	}
	document.getElementById(user).style.color = fcolor 
	document.getElementById(user).style.fontWeight = fweight
	document.getElementById(hiddenfield).value = hfvalue
}

function userReimbursed(pos,neg,id) {
    var hiddenfield = document.getElementById(id + "_reimbursedhf");
    var elementToSwap = document.getElementById(id+"_reimbursed");
    var toClass = '';
    var toString = '';

    if (elementToSwap.innerHTML==pos) {
        elementToSwap.innerHTML = neg;
        elementToSwap.className = 'negative';
        hiddenfield.value = "notreimbursed";
    } else {
        elementToSwap.innerHTML = pos;
        elementToSwap.className = 'positive';
        hiddenfield.value = "reimbursed";
    }

}


function userPaid(pos,neg,id) {
    var hiddenfield = document.getElementById(id + "_paidhf");
    var elementToSwap = document.getElementById(id+"_paid");
    var toClass = '';
    var toString = '';

    if (elementToSwap.innerHTML==pos) {
        elementToSwap.innerHTML = neg;
        elementToSwap.className = 'negative';
        hiddenfield.value = "notpaid";
    } else {
        elementToSwap.innerHTML = pos;
        elementToSwap.className = 'positive';
        hiddenfield.value = "paid";
    }

}

function manageEvent(what, id, text){
//sets the hiddenfield of the event to wathever value it should be changed to
	//=> called in myprofile_settings

	var event = "ue_" + id
	var hiddenfield = "hf_" + id
	switch (what){
		case "p":
			fcolor = "green"
			fweight = "bold"
			if (window.confirm(text)){
				hfvalue = "1";
				setTimeout("document.bettinggames.submit()", 1000);
			}else{
				manageEvent('x', id)
			}
			break;
		case "r":
			fcolor = "red"
			fweight = "bold"
			if (window.confirm(text)){
				hfvalue = "-1";
				setTimeout("document.bettinggames.submit()", 1000);
			}else{
				manageEvent('x', id)
			}
			break;
		case "x":
			fcolor = "white"
			fweight = "normal"
			hfvalue = "0"
			break;
	}
	document.getElementById(event).style.color = fcolor 
	document.getElementById(event).style.fontWeight = fweight
	document.getElementById(hiddenfield).value = hfvalue
}

function addNewMatch (){
//make a unvisible new match row visible
	//=> called in admin_events_matches
	
	var rownb = document.getElementById('showmatches').rows.length - 1
	var adds = document.getElementById('adds').value
	if(++adds > 20){
			alert(document.getElementById('enoughadds').value)
	}else{
		document.getElementById('newtr_' + adds).className = ""
		document.getElementById('adds').value = adds
	}
	
}


function editResult(id, row){
//changes result fields from readonly to not readonly and vice versa 
//for this, the image & the value in the hiddenfield has to change as well.
	//=> called in admin_events_results
	
	var special = '#special_'+ id
	var hf = document.getElementById('ro_' + id)
	var img = document.getElementById('im_'+ id)
	var style = document.getElementById('style').value
	var edit = document.getElementById('edit').value
	var cancel = document.getElementById('cancel').value
	var score_input_type = document.getElementById('score_input_type').value
    var ro = ""
	if(score_input_type == 'results'){
        var h = '#h_' + id;
		var v = '#v_'+ id
		ro = $(h).attr('readonly');
        $(h).attr('readonly', !$(h).attr('readonly'));
        $(v).attr('readonly', !$(v).attr('readonly'));
        $(special).attr('readonly', !$(special).attr('readonly'));
	}else{
		
        var s1 = '#s1_' + id;
		var sX = '#sX_' + id;
		var s2 = '#s2_' + id;

		ro = $(h).attr('disabled');

        $(s1).attr('readonly', !$(s1).attr('readonly'));
        $(sX).attr('readonly', !$(sX).attr('readonly'));
        $(s2).attr('readonly', !$(s2).attr('readonly'));
        $(special).attr('readonly', !$(special).attr('readonly'));
	}
	if(ro=='readonly'){
		hf.value = "false";
		img.src = "src/"+ style +"/img/edit_cancel.png"
		img.alt = cancel
		img.title = cancel
	}else{
		hf.value = "true";
		img.src = "src/"+ style +"/img/edit.png"
		img.alt = edit
		img.title = edit
	}

}

function menuMM(){
//two little divs make a minimize/maximize button for the vertical menu
	//=> called in overview & maybe other files
	
	var menu = document.getElementById("menu_v")
	var open = document.getElementById("menu_v_mmo")
	var close = document.getElementById("menu_v_mmc")
	if(open.style.display!="block"){
		close.style.display="none"
		right = howmuch = menu.offsetParent.offsetWidth - menu.offsetWidth - menu.offsetLeft;
		if(menu.offsetLeft < menu.offsetParent.offsetWidth-15){
			menu.style.right = (howmuch-12) + "px";
			howmuch = howmuch - 12;
			window.setTimeout(function (){ menuMM();}, 10); 
		}else{
			open.style.display="block";
			menu.style.display="none";
		}
	}else{
		menu.style.display="block"
		if(menu.offsetLeft + menu.offsetWidth + 10 > menu.offsetParent.offsetWidth - open.offsetWidth){
			menu.style.right = "-10px";
			menu.style.display="block"
			menu.style.right = (howmuch+12) + "px";
			howmuch = howmuch + 12;
			window.setTimeout(function (){ menuMM();}, 10); 
		}else{
			open.style.display="none"
			close.style.display="block"
		}
	}
}

function replyToCmt(id, title){
//sets the parent_id of a new comment as well as a proposition for the title
	//=> called in comments
	
	var hfc = document.getElementById("hf_comment").value
	var hfa = document.getElementById("hf_answer").value
	var hfid = document.getElementById("hf_parentid") 
	var cdiv = document.getElementById("comment")
	var tinput = document.getElementById("title")
	if(id > 0){
		cdiv.innerHTML = hfa + " '" + title + "' <a href=\"javascript: replyToCmt('0', '')\">x</a>"
		hfid.value = id
		tinput.value = "re: " + title
		var loc =  document.location.toString();
		if(loc.search(/\#/)>0){
			document. location = loc.replace(/(\#)(.+)/, "#comment");
		}else{
			document.location = loc + "#comment";
		}

		/* =  */
	}else{
		cdiv.innerHTML = hfc
		hfid.value = 0
		tinput.value = "-"
	}
}
function overviewArrange (){
/*
	row1 = document.getElementById("overviewtitletable").rows[0];
	row2 = document.getElementById("overviewcontenttable").rows[0];
	cellvar1 = row1.cells[0];
	cellvar2 = row1.cells[0];
	counter = 0;
	while(cellvar1 != row1.lastChild){
		if(parseInt(cellvar2.offsetWidth) > 0) {
			cellvar1.style.width = cellvar2.offsetWidth+"px";
			cellvar1.style.width = "100px";
			counter++;
		}
		cellvar1 = cellvar1.nextSibling;
		cellvar2 = cellvar2.nextSibling;
	}
	var otimer=null;
 	 if(otimer){clearInterval(otimer);otimer=null;}
	otimer=setInterval("scrollSynch('overview', 'overviewtitlerow');",4000);
*/
}
/*
function scrollSynch(obj1, obj2){
	scr1 = document.getElementById(obj1);
	scr2 = document.getElementById(obj2);
	

	scr1.scrollLeft = scr2.scrollLeft;
/*	alert ("under construction..ever 4 secs this: "+row2.length);
}

*/



function setOverBG(rowid, stylename){
	var myrow = document.getElementById(rowid);
	if( myrow.style.background == "" ){
		myrow.style.background = "url(/src/style_"+stylename+"/img/bg_onmouseover.png)";
	}
}
function unsetOverBG(rowid){
	var myrow = document.getElementById(rowid);
	if( myrow.style.background.search(/bg_onmouseover.png/) > 0 ){
		myrow.style.background =  "";
	}
}
function switchToActivatedBG(rowid, stylename){
	var myrow = document.getElementById(rowid);
	if( myrow.style.background.search(/bg_onactivated.png/) < 0 ){
		myrow.style.background = "url(/src/style_"+stylename+"/img/bg_onactivated.png)";
	}else{
		myrow.style.background = "url(/src/style_"+stylename+"/img/bg_onmouseover.png)";
	}
	
}

function addReceivers(rec, none){
	var hf_rec = document.getElementById("hf_receivers").value;
	var receivers = new Array();
	if(hf_rec == "") {
		receivers[0] = "" 
	}else{
		receivers = hf_rec.split(":")
		receivers.pop();
	}
	var userlist = document.getElementById("receivers");
	var users = rec.split(":");
	users.pop();
	for (var i = 0; i < users.length; i++) {
        var u = users[i];
		isin = false;
        for (var j = 0; j < receivers.length; j++) {
            var r = receivers[j];
			if (r == u) isin = true;
		}	
		if (!isin){
			hf_rec = hf_rec+u+":";
			name = document.getElementById("user_"+u).value;
			if (userlist.innerHTML == none) {
				userlist.innerHTML = name+"<a href=\"javascript: removeReceiver('"+u+"', '"+none+"')\">(-)</a><br/>";
			}else{
				userlist.innerHTML = userlist.innerHTML+name+"<a href=\"javascript: removeReceiver('"+u+"', '"+none+"')\">(-)</a><br/>";
			}
		}
	}
	document.getElementById("hf_receivers").value = hf_rec;
}


function removeReceiver(rec, none){
	hf_rec = document.getElementById("hf_receivers").value;
	receivers = hf_rec.split(":")
	receivers.pop();
	hf_rec = "";
	userlist = document.getElementById("receivers");
	document.getElementById("hf_receivers").value = "";
	userlist.innerHTML = "";
	for (var i = 0; i < receivers.length; i++) {
        var r = receivers[i];
		if (r != rec){
			hf_rec = hf_rec+r+":";
			name = document.getElementById("user_"+r).value;
			userlist.innerHTML = userlist.innerHTML+name+"<a href=\"javascript: removeReceiver('"+r+"', '"+none+"')\">(-)</a><br/>";
		}
	}
	document.getElementById("hf_receivers").value = hf_rec;
	if (userlist.innerHTML == "")
		userlist.innerHTML = none;
}

function searchUsers(userNb, none){
	var searchstring = document.getElementById("searchstr").value;
	var res = document.getElementById("results");
	res.innerHTML = "";
	if (searchstring != ""){
        var caseinsensitive = "/"+searchstring+"/i"; 
		for(i=1; i<=userNb; i++){
			var user = document.getElementById("user_"+i).value;
			if (user.search(searchstring) > -1){
				res.innerHTML = res.innerHTML  + "<b>" + user  + "</b><a href=\"javascript: addReceivers('"+i+":', '"+none+"')\"> (+)</a>" + "<br/>";
			}
		}
	}

}

function checkMessage(warningsstring){
	err = new Array("","","","");
	warnings = warningsstring.split(";");
	wardiv = document.getElementById("warning");
	title = document.getElementsByName("title")[0].value;
	content = document.getElementsByName("content")[0].value;
	receivers = document.getElementById("hf_receivers").value;
	var saveit = true;
	if(title == ""){
		err[0] = "title"
	}
	if(content == ""){
		err[1] = "content"
	}
	if(receivers == ""){
		err[2] = "receivers"
	}
	var counter = 0;
	var counter2 = 0;
	for(var e in err){
		counter++;
		if(err[counter-1] != ""){
			saveit = false;
			counter2++;
			if (counter2 == 1){
				wardiv.innerHTML = warnings[0] + " " + warnings[counter];
			}else if(counter2 > 1){
				wardiv.innerHTML  = wardiv.innerHTML + ", " + warnings[counter];
			}
		}
	}
	if (saveit)
		document.message.submit();
	else{
		scroll(0,0);
	}
	
}

/*
	Written by Jonathan Snook, http://www.snook.ca/jonathan
	Add-ons by Robert Nyman, http://www.robertnyman.com
*/

function getElementsByClassName(oElm, strTagName, strClassName){
	var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
	var arrReturnElements = new Array();
	strClassName = strClassName.replace(/\-/g, "\\-");
	var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)");
	var oElement;
	for(var i=0; i<arrElements.length; i++){
		oElement = arrElements[i];
		if(oRegExp.test(oElement.className)){
			arrReturnElements.push(oElement);
		}
	}
	return (arrReturnElements)
}

//found at http://www.quirksmode.org/js/findpos.html
function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		do {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
	}
	return [curleft,curtop];
}


//============================ floating menu.js

//===== try 2
/*
<!-- ALWAYS ON TOP FLOATING LAYER POP-UP -->

<script language="JavaScript" type="text/javascript">
<!-- Copyright 2003, Sandeep Gangadharan -->
<!-- For more free scripts go to http://sivamdesign.com/scripts/ -->*/


	var y1 = 50;   // change the # on the left to adjuct the Y co-ordinate
	(document.getElementById) ? dom = true : dom = false;

	function hideFloatingLayer(id) {
	  if (dom) {document.getElementById("floating_layer" + id).style.display='none';}
	}

	function showFloatingLayer(id) {
	  if (document.getElementById("floating_layer" + id)) {
		if (dom) {document.getElementById("floating_layer" + id).style.display='block';}
	  }
	}

	function changeFloatingLayer(id){
		els = getElementsByClassName(document, 'div', 'floating_layer');
		for (var i = 0; i < els.length; i++){
			els[i].style.display='none';
		}
		showFloatingLayer(id);
	}

	function placeFloatingLayers(){
		els1 = getElementsByClassName(document, 'div', 'floating_layer');
		els2 = getElementsByClassName(document, 'div', 'message_layer');
		for (var i = 0; i < els1.length; i++){
			placeFloatingLayer(els1[i]);
		}
		for (var i = 0; i < els2.length; i++){
			placeFloatingLayer(els2[i]);
		}
	}

	function placeFloatingLayer(i) {
		
		  if (i) {
			var w_height;
			w_height2 = window.innerHeight;
			w_height = document.documentElement.clientHeight;
			var i_height;
			i_height = i.offsetHeight;
			cntdiv = findPos(document.getElementById("cnt"));
			if(dom && !document.all) i.style.top = (window.pageYOffset + w_height/2 - i_height/2 - cntdiv[1]) + "px";
/*			if (dom && !document.all) {i.style.top = (window.pageYOffset + w_height2/2 - i_height/2) + "px";}*/
/*			  if (document.all) {i.style.top = document.documentElement.scrollTop + (document.documentElement.clientHeight - (document.documentElement.clientHeight-y1)) + "px";}*/
			  window.setTimeout(function (){ placeFloatingLayer(i);}, 500); }
	 }



//The Clock
<!-- Original:  Tomleung (lok_2000_tom@hotmail.com) This tag should not be removed-->
<!--Server time ticking clock v2.0 Updated by js-x.com-->
function MakeArrayday(size)
{
  this.length = size;
  for(var i = 1; i <= size; i++)
    this[i] = "";
  return this;
}
function MakeArraymonth(size)
{
  this.length = size;
  for(var i = 1; i <= size; i++)
    this[i] = "";
  return this;
}

var text;
var hours;
var minutes;
var seconds;
var timer=null;
function sClock(h,m,s,t)
{
  text=t;
  hours=h;
  minutes=m;
  seconds=s;
  if(timer){clearInterval(timer);timer=null;}
  timer=setInterval("work();",1000);
}

function twoDigit(_v)
{
  if(_v<10)_v="0"+_v;
  return _v;
}

function work()
{
  if (!document.layers && !document.all && !document.getElementById) return;
  var runTime = new Date();
  var shours = hours;
  var sminutes = minutes;
  var sseconds = seconds;
  sminutes=twoDigit(sminutes);
  sseconds=twoDigit(sseconds);
  shours  =twoDigit(shours  );
  movingtime = "" + text + ": " + shours + ":" + sminutes +":"+sseconds+"";
  if (document.getElementById)
    document.getElementById("servertime").innerHTML=movingtime;
  else if (document.layers)
  {
    document.layers.clock.document.open();
    document.layers.clock.document.write(movingtime);
    document.layers.clock.document.close();
  }
  else if (document.all)
    clock.innerHTML = movingtime;

  if(++seconds>59)
  {
    seconds=0;
    if(++minutes>59)
    {
      minutes=0;
      if(++hours>23)
      {
        hours=0;
      }
    }
  }
}


function showFilter(){
	document.getElementById("filterform").setAttribute("class","");
	document.getElementById("filterform").style.display = "inline";
}
function filter(url){
	if (document.getElementById("filter_on").value != "nofilter"){
		url = url + "filter=" + document.getElementById("filter_on").value + ":" + document.getElementById("filter_this").value;
	}
	document.location = url;
}
function filterChange (){
	is = document.getElementById("filter_is");
	contains = document.getElementById("filter_contains");
	on = document.getElementById("filter_on").value;
	if (on == "matchday"){
		is.setAttribute("class", "");
		contains.setAttribute("class", "notvisible");
	}else{
		is.setAttribute("class", "notvisible");
		contains.setAttribute("class", "");
	}
}
function filterUnset(){
	document.getElementById("filter_on").value = "nofilter";
	document.getElementById("filter_this").value = "";
	filterChange();
}


function debugAlert(string) {
    alert(string);
}
