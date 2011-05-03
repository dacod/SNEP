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
 {if $HEADER}
    {include file="cabecalho.tpl"}
 {/if}
 <table align="center" class="mensagem" cellpadding="0" cellspacing="0">
    <thead>
       <tr>
          <td class="mensagem">{$LANG.warning}</td>
       </tr>
    </thead>
    <tr>
       <td height="50" class="mensagem">
          {$ERROR}
       </td>
    </tr>
    <tr>
       <td class="subtable" align="right" style="padding: 5px;">
          <form name="ok" id="ok">
          {if $RET < 0}
             <input type="button" class="button" value="Ok" onClick="history.go({$RET});"  />
          {/if}

          {if $RET == 0}
             <input type="button" class="button" value="Ok" onClick="javascript:self.close();" />
          {/if}

          {if $RET > 0}
             <input type="button" class="button" value="Ok" onClick="location.reload(true);" />
          {/if}

          <div class="buttonEnding"></div>
          </form>
       </td>
    </tr>
 </table>