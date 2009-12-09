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

navHover = function() {
    if(document.getElementById("navmenu")){
        var lis = document.getElementById("navmenu").getElementsByTagName("LI");
        for (var i=0; i<lis.length; i++) {
            lis[i].onmouseover=function() {
                this.className+=" iehover";
            }
            lis[i].onmouseout=function() {
                this.className=this.className.replace(new RegExp(" iehover\\b"), "");
            }
        }
    }
}
if (window.attachEvent){
  window.attachEvent("onload", navHover);
}