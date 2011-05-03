/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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

function setCookie(c_name,value,expiresecs) {
    var exdate = new Date();
    exdate.setTime(exdate.getTime()+ ((expiresecs) ? expiresecs*1000: 0));
    document.cookie = c_name+ "=" +escape(value)+
    ((expiresecs==null) ? "" : ";expires="+exdate.toGMTString());
}

function getCookie(c_name) {
    if (document.cookie.length > 0)  {
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) c_end = document.cookie.length;
            return unescape(document.cookie.substring(c_start,c_end));
        }
    }
    return "";
}

function delCookie() {

    var cookies = unescape(document.cookie.split(";"));

    alert(cookies);

}
