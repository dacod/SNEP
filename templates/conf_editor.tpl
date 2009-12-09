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
<table cellspacing="0" align="center" class="contorno">
   <form method="post" action="{$SUBMIT_FILE}?acao=salvar">
   <input type="hidden" name="conf_file" value="{$CONF_FILE}"  />
   <tr>
      <td width="60%">
         <textarea style="width: 100%; height: 360px; border: 1px solid #999; color: white; background-color:#333;" name="text">{$CONF_CONTENT}</textarea>
      </td>
      <td>
         <iframe src="app_help.php" style="width:100%; height:360px; border: 1px solid #999;" ></iframe>
      </td>
   </tr>
   <tr>
      <td colspan="2" style="text-align: center; padding:5px;">
         <input type="submit" value="{$LANG.save}" class="button">
         <div class="buttonEnding"></div>
         &nbsp;&nbsp;
         <input class="button" type="button" name="voltar" value="{$LANG.discard}" onClick="location.href='../src/sistema.php'" />
          <div class="buttonEnding"></div>
      </td>
   </tr>
   </form>
</table>
{ include file="rodape.tpl }