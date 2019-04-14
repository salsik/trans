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



function getData(Holder,DataSource)
{ 

xmlHttp=GetXmlHttpObject()
if (xmlHttp==null)
 {
 alert ("Browser does not support HTTP Request")
 return
 }
xmlHttp.onreadystatechange=function () 
{ 
if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
 {
 document.getElementById(Holder).innerHTML=xmlHttp.responseText 
 } 
}

xmlHttp.open("GET",DataSource,true)

xmlHttp.send(null)

}

function getit(Holder,DataSource)
{
	getData(Holder,DataSource);
}