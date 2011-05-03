{*
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
 *}
{include file="cabecalho.tpl"}
<form  action="{$smarty.server.SCRIPT_NAME}" METHOD="post">
<table  cellpadding="0" cellspacing="0" border="0" align="center">
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
         <input type="submit" value="Entrar" class="new_button" name="login" />
      </td>
   </tr>
   <tr>
      <td colspan="2" height="80" align="right" class="subtable" valign="top">
      {$LANG.msg_login}
      </td>
      <td class="subtable">&nbsp;</td>
   </tr>
   <tr>
     <td colspan="3" class="subtable" align="center">
     <br /><br /><br /><br /><br />
     </td>
   </tr>
</table>
</form>
{ include file="rodape.tpl }
<script>
 // <![CDATA[
 document.getElementById('user_login').focus();
 // ]]>
</script>