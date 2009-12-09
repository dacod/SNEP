/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

function addEvent(elm, evType, fn, useCapture){if(elm.addEventListener){elm.addEventListener(evType, fn, useCapture);return true;}else if (elm.attachEvent){var r = elm.attachEvent('on' + evType, fn);return r;}else{elm['on' + evType] = fn;}}
var newWindow = null;

function closeWin(){
	if (newWindow != null){
		if(!newWindow.closed)
			newWindow.close();
	}
}

function popUpWin(url, type, strWidth, strHeight){
	
	closeWin();
		
	type = type.toLowerCase();
	
	if (type == "fullscreen"){
		strWidth = screen.availWidth;
		strHeight = screen.availHeight;
	}
	var tools="";
	if (type == "standard") tools = "resizable,toolbar=yes,location=yes,scrollbars=yes,menubar=yes,width="+strWidth+",height="+strHeight+",top=0,left=0";
	if (type == "console" || type == "fullscreen") tools = "resizable,toolbar=no,location=no,scrollbars=yes,titlebar=no,status=no,width="+strWidth+",height="+strHeight+",left=0,top=0";
	newWindow = window.open(url, 'newWin', tools);
	newWindow.focus();
}

function doPopUp(e)
{
//set defaults - if nothing in rel attrib, these will be used
var t = "standard";
var w = "780";
var h = "580";
//look for parameters
attribs = this.rel.split(" ");
if (attribs[1]!=null) {t = attribs[1];}
if (attribs[2]!=null) {w = attribs[2];}
if (attribs[3]!=null) {h = attribs[3];}
//call the popup script
popUpWin(this.href,t,w,h);
//cancel the default link action if pop-up activated
if (window.event) 
	{
	window.event.returnValue = false;
	window.event.cancelBubble = true;
	} 
else if (e) 
	{
	e.stopPropagation();
	e.preventDefault();
	}
}

function findPopUps()
{
var popups = document.getElementsByTagName("a");
for (i=0;i<popups.length;i++)
	{
	if (popups[i].rel.indexOf("popup")!=-1)
		{
		// attach popup behaviour
		popups[i].onclick = doPopUp;
		// add popup indicator
		if (popups[i].rel.indexOf("noicon")==-1)
			{
			popups[i].style.backgroundImage = "url(pop-up.gif)";
			popups[i].style.backgroundPosition = "0 center";
			popups[i].style.backgroundRepeat = "no-repeat";
			popups[i].style.paddingLeft = "15px";
			}
		// add info to title attribute to alert fact that it's a pop-up window
		popups[i].title = popups[i].title + " [Opens in pop-up window]";
		}
	}
}

addEvent(window, 'load', findPopUps, false);
