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
         <td class="subtable" width="60%" height="35">
            <form name="filtro" method="post" action="{$smarty.server.SCRIPT_NAME}">
            {$LANG.fieldtofilter}
                <select name="field_filter" class="campos">
                    {html_options options=$OPCOES}
                </select>
                &nbsp;&nbsp;&nbsp;
                {$LANG.filter}: <input type="text" name="text_filter" class="campos">
                &nbsp;&nbsp;&nbsp;
                <input type="submit" name="filtrar" value="{$LANG.apply}" class="new_button"/>
                &nbsp;&nbsp;&nbsp;
                <input type="submit" name="limpar" value="{$LANG.cancel}"  class="new_button"/>
            </form>
         </td>
      {/if}
      <td class="subtable"  height="35">
      {if $view_include_buttom}
           <a href="#" class="links_include" onclick="location.href='{$array_include_buttom.url}'"  >
              {$array_include_buttom.display}
           </a>
      {/if}
      {if $view_include_buttom2}
           <a href="#" class="links_include_various" onclick="location.href='{$array_include_buttom2.url}'"  >
              {$array_include_buttom2.display}
           </a>
      {/if}
      {if $debugger_btn}
           <a href="./debugger.php" class="links_debug" >
              {$LANG.debugger}
           </a>
      {/if}
      </td>
   </tr>
</table>