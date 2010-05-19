{*
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
 *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
   <head>
      <TITLE>{$LANG.tit_sistema}</TITLE>
      <link rel="icon" href="favicon.ico" type="images/x-icon" />
      <link rel="shortcut icon" href="favicon.ico" />
      <link rel="stylesheet" href="../css/{$CSS_TEMPL}.css" type="text/css" />
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> 
      <meta name="copyright" content="Opens Tecnologia&reg;" />
      <script src="../includes/javascript/popup.js"></script>
   </head>
   <body>
       <div id="contentHelp">
           {$aviso}
           {include file="../doc/manual/$texto"}
       </div>
   <div id="footer" style="padding-top: 10px;padding-left: 5px;">
      <input style="float:right;" type="button" class="new_button" value="Fechar" onClick="parent.close()"/>
      {$LANG.goto}: <a  href="../src/ajuda.php?script=index">Índice</a>
   </div>
   
 </body>
</html>