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
{include file="cabecalho.tpl"}
<table  cellpadding="0" cellspacing="0" border="0" align="center">
   <FORM  action="{$smarty.server.SCRIPT_NAME}" METHOD="post">
   <tr>
      <td class="subtable" colspan="3" height="100" valign="top">
         <br />
         <div id="titulo">{$LANG.tit_login}</div>
      </td>
   </tr>
   <tr>           
      <td height="40" width="230" class="subtable" align="right" >{$LANG.login}:&nbsp;</td>
      <td width="20" class="subtable" align="right">
         <input TYPE="TEXT" size="30" id="user_login" name="user_login" class="campos">
      </td>
      <td width="550" class="subtable">&nbsp;</td>
   </tr>
   <tr>
      <td class="subtable" align="right">{$LANG.secret}:&nbsp;</td>
      <td class="subtable" align="right">
         <input type="PASSWORD" size="30" name="user_senha" class="campos" />
      </td>
      <td  class="subtable" align="left">
         &nbsp;&nbsp;&nbsp;
         <input type="submit" value="Entrar" class="button" name="login"></input>
         <div class="buttonEnding"></div>
      </td>
   </tr>
   <tr>
      <td colspan="2" height="80" align="right" class="subtable" valign="top">
      {$LANG.msg_login}
      </td>
      <td class="subtable">&nbsp;</td>
   </tr>
   </FORM>
   <tr>
     <td colspan="3" class="subtable" align="center">
     <br /><br /><br /><br /><br />
        <a href="http://www.php.net" class="links_disable" target="blank" >
        <img src="../imagens/php-power-white.png" width="88" height="31" style="border: none" />&nbsp;
        </a>
        <a href="http://www.smarty.net" class="links_disable" target="blank" >
        <img src="../imagens/smarty_icon.gif" width="88" height="31"  style="border: none" />&nbsp;
        </a>
        <a href="http://www.asternic.org" class="links_disable" target="blank" >
        <img src="../imagens/fop_logo.gif" width="183" height="31" style="border: none" />&nbsp;
        </a>
        <a href="http://www.aditus.nu/jpgraph/index.php" class="links_disable" target="blank" >        
        <img src="../imagens/JpGraph_Logo.png" width="44" height="31" style="border: none" />&nbsp;
        </a>
        <a href="http://www.mysql.com" class="links_disable" target="blank" >
        <img src="../imagens/powered-by-mysql-88x31.png" width="88" height="31" style="border: none" />&nbsp;
        </a>
        <a href="http://mozilla.com/firefox" class="links_disable" target="blank" >
        <img src="../imagens/firefox_logo.png" width="88" height="34" style="border: none" />&nbsp;
        </a>        
     </td>
     
   </tr>
</table>
{ include file="rodape.tpl }
</div>
</body>
</html>
<script>
 // <![CDATA[
 document.getElementById('user_login').focus();
 // ]]>
</script>