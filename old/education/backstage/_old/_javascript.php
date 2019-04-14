<script type="text/javascript" language="javascript">

var xmlHttp1

function GetXmlHttpObject1()
{
var xmlHttp1=null;
try
 {
 // Firefox, Opera 8.0+, Safari
 xmlHttp1=new XMLHttpRequest();
 }
catch (e)
 {
 //Internet Explorer
 try
  {
  xmlHttp1=new ActiveXObject("Msxml2.XMLHTTP");
  }
 catch (e)
  {
  xmlHttp1=new ActiveXObject("Microsoft.XMLHTTP");
  }
 }
return xmlHttp1;
}


////////////////////////////////////

var xmlHttp

function GetXmlHttpObject()
{
var xmlHttp=null;
try
 {
 // Firefox, Opera 8.0+, Safari
 xmlHttp=new XMLHttpRequest();
 }
catch (e)
 {
 //Internet Explorer
 try
  {
  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
  }
 catch (e)
  {
  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
 }
return xmlHttp;
}

function getData(DataSource,Holder)
{ 
xmlHttp=GetXmlHttpObject()
if (xmlHttp==null)
 {
 alert ("Browser does not support HTTP Request")
 return
 }
DataSource=DataSource+"&sid="+Math.random()
xmlHttp.open("GET",DataSource,true)
xmlHttp.onreadystatechange=function () 
{ 
if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
 {
 document.getElementById(Holder).innerHTML=xmlHttp.responseText;
 
/* alert(xmlHttp.responseText);
 alert(document.getElementById(Holder).innerHTML);*/
 
 } 
}

xmlHttp.send(null)
}

function edit(menu, Action, isHidden){
 i=0;
if( !Action )
{
	Action = 'edit';
}
 var onechecked;
 var del = document.getElementById('del');
 while (temp=del.elements[i++]){
  if (temp.name.substr(0,3)=="ids")
   if(temp.checked==true || isHidden)
   		onechecked=1;
}
   if(onechecked==1){
	  document.getElementById('del').action='<? echo $filename; ?>'+'?menu='+menu+'&action=' + Action; 
	  document.getElementById('del').submit(); 
  }
}
function edit2(menu){
	 i=0;
	 var onechecked;
	 var del = document.getElementById('del');
	 while (temp=del.elements[i++]){
	  if (temp.name.substr(0,3)=="ids")
	   if(temp.checked==true)
	   		onechecked=1;
	}
	   if(onechecked==1){
		  document.getElementById('del').action='<? echo $filename; ?>'+'?'+menu+'action=edit'; 
		  document.getElementById('del').submit(); 
	  }
	}

function conf(){
 i=0;
 var onechecked;
 var del = document.getElementById('del');
 while (temp=del.elements[i++]){
  if (temp.name.substr(0,3)=="ids")
   if(temp.checked==true)
   		onechecked=1;
}
   if(onechecked==1)
	 if (confirm("Are you sure you want to delete this/these record(s)?"))
	  document.getElementById('del').submit(); 
}

function checkall(){
 i=0;
 //var del = document.getElementById('del');
 var del = document.getElementById('del');
 while (temp=del.elements[i++])
 {
	if (temp.name.substr(0,3)=="ids")
	{
		temp.checked=del.main.checked;
	}
 }
}

var CONTENT = new Array();
CONTENT[0] = "While logging on, use the <input type='checkbox' checked disabled> so that<br>next time you will log-in directly."
CONTENT[1] = "Don't forget to logout when you finished."
CONTENT[2] = "Check the website after each modification to assure<br>that what you intended to show is showing right."
CONTENT[3] = "Contact <a href='mailto:<?=EMAIL_ADMIN?>' class='info'><?=EMAIL_ADMIN?></a> for any question<br>you have about this administration."
CONTENT[4] = "For errors, send an e-mail to <a href='mailto:support@cre8mania.com' class='info'>support@cre8mania.com</a> and include<br>the error message you got and the steps to reproduce this error."

function getinfo(){
	var INDEX_NUMBER = Math.floor(Math.random()*CONTENT.length);
	document.getElementById('info').innerHTML=CONTENT[INDEX_NUMBER];
}

</script>