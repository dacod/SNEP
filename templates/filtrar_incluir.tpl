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
<table align="center" class="bgfiltro">
   <tr>
      {if $view_filter}
         <form name="filtro" method="post" action="{$smarty.server.SCRIPT_NAME}">
         <td class="subtable" width="60%" height="35">
            {$LANG.fieldtofilter}
            <select name="field_filter" class="campos">
               {html_options options=$OPCOES}
            </select>
            &nbsp;&nbsp;&nbsp;
            {$LANG.filter}: <input type="text" name="text_filter" class="campos">
            &nbsp;&nbsp;&nbsp;
            <input type="submit" name="filtrar" value="{$LANG.apply}" class="button"/>
            <div class="buttonEnding"></div>
            &nbsp;&nbsp;&nbsp;
            <input type="submit" name="limpar" value="{$LANG.cancel}"  class="button"/>
            <div class="buttonEnding"></div>
         </td>
         </form>
      {/if}
      {if $debugger_btn}
        <td class="subtable"  height="35">
           <a href="./debugger.php" class="links_debug" >
              {$LANG.debugger}
           </a>
         </td>
      {/if}
      {if $view_include_buttom}
         <td class="subtable"  height="35">
           <a href="#" class="links_include" onclick="location.href='{$array_include_buttom.url}'"  >
              {$array_include_buttom.display}
           </a>
         </td>         
      {/if}
      {if $view_include_buttom2}
         <td class="subtable" width="15%" height="35">
           <a href="#" class="links_include_various" onclick="location.href='{$array_include_buttom2.url}'"  >
              {$array_include_buttom2.display}
           </a>
         </td>
      {/if}
   </tr>
</table>