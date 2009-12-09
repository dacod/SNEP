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
      <meta http-equiv="Content-Type" content="text/html; {$LANG.ISO}" />
      <meta http-equiv="Content-Script-Type" content="text/javascript" />
      <meta http-equiv="imagetoolbar" content="false" />
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> 
      <META http-equiv="Expires" content="Fri, 25 Dec 1980 00:00:00 GMT" />
      <META http-equiv="Last-Modified" content="{php}gmdate('D, d M Y H:i:s'){/php} GMT" />   
      <META http-equiv="Cache-Control" content="no-cache, must-revalidate" />
      <META http-equiv="Pragma" content="no-cache" />
      <meta name="copyright" content="Opens Tecnologia&reg;" />
      <script src="../includes/javascript/popup.js"></script>
   </head>
   <body>
       <div id="contentHelp">
           {$aviso}
           {include file="../doc/manual/$texto"}
       </div>
   <div id="footer" style="padding-top: 10px;padding-left: 5px;">
       
       <div style="width:100px;float:right;text-align: center">
          <input type="button" class="button" value="{$LANG.close}" onClick="parent.close()"/>
          <div class="buttonEnding"></div>
       </div>

      {$LANG.goto}: <a  href="../src/ajuda.php?script=index">&Iacute;ndice</a>


   </div>
   
 </body>
</html>