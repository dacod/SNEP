/* Programa: menus.js - funcao javascript para Menu Dropdowns
 *                      funcionar no IE
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
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