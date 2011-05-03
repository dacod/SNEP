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
 <table>
    <thead>
       <tr>
          <td class="esq" >{$LANG.error_type}</td>
          {assign var="colunas" value=1}
          {foreach name=boards from=$CANAIS key=board_key item=board_item}
             {foreach name=links from=$CANAIS[$board_key] key=link_key item=link_item}
                <td>
                   {$LANG.board}: {$board_key} , {$LANG.link}: {$link_key}
                   <br />
                   {$link_item}
                </td>
                {assign var="colunas" value="`$colunas+1`"}
             {/foreach}
          {/foreach}

       </tr>
    </thead>
    {foreach name=status from=$STATUS key=sts_key item=sts_item}
       <tr>
          <td>{$sts_key}</td>
          {foreach name=sts_board from=$STATUS[$sts_key] key=sb_key item=sb_item}
             {foreach name=sts_link from=$STATUS[$sts_key][$sb_key] key=sl_key item=sl_item}
                <td class="cen">{$sl_item}</td>
             {/foreach}
          {/foreach}
       </tr>
    {/foreach}
    <tr>
     <td colspan="{$colunas}" class="subtable" align="center" height="38px" valign="middle">
        <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao=zerar" >
           <input class="button" type="submit" id="resetar"  value="{$LANG.zerocounter}">
           <div class="buttonEnding"></div>
        </form>
     </td>
  </tr>

 </table>
 { include file="rodape.tpl }

