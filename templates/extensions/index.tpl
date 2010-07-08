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
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >
   <thead>
      <tr>
         <td class="cen" width="15%">{$LANG.ramal}</td>
         <td class="cen" width="40%">{$LANG.extendname}</td>
         <td class="cen" width="15%">{$LANG.channel}</td>
         <td class="cen" width="15%">{$LANG.group}</td>
         <td class="cen" colspan="3" width="15%">{$LANG.actions}</td>
      </tr>
   </thead>
   <tbody>
       {foreach name=exten_loop from=$extensions item=exten}
       <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
             <td class="cen">{$exten.exten}</td>
             <td>{$exten.name}</td>
             <td class="cen">{$exten.channel}</td>
             <td class="esq">{$exten.group}</td>
             <td align="center" valign="middle">
                <acronym title="{$LANG.change}">
                   <a href="extensions.php?action=edit&amp;id={$exten.exten}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
                </acronym>
             </td>
             <td valign="middle" align="center">
             {if $exten.exten != 'admin'}
                <acronym title="{$LANG.exclude}">
                    <a href="extensions.php?action=delete&id={$exten.exten}" onclick="return confirm('{$LANG.confirm_remocao_ramal}')"><img src="../imagens/delete.png" alt="{$LANG.exclude}"/></a>
                </acronym>
             {/if}
             </td>
             <td valign="middle" align="center">
             {if $exten.exten != 'admin'}
             <form name="formulario" method="post"  action="../configs/permissoes.php" enctype="multipart/form-data">
                  <input type="hidden" name="id" value="{$exten.id}" />
                  <input type="hidden" name="nome" value="{$exten.callerid}" />
                  <input type="hidden" name="name" value="{$exten.exten}" />
                <acronym title="{$LANG.permitions}">
                   <input type="image" src="../imagens/permitions.png" border="0" alt="{$LANG.permitions}"  name="acao" value="permissoes"/>
                </acronym>
             </form>
             {/if}
             </td>
       </tr>
       {/foreach}
   </tbody>
</table>
{include file="zend_paginator.tpl"}
{ include file="rodape.tpl }