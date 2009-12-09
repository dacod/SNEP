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
         <td class="cen" width="15%">{$LANG.name}</td>
         <td class="esq">{$LANG.type}</td>
         <td class="cen" colspan="3" width="20%">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=grupos loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[grupos].name}</td>
         <td>{$DADOS[grupos].inherit}</td>
         
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/groups.php?acao=alterar&amp;cod_grupo={$DADOS[grupos].name}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>

         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <a href="../src/groups.php?acao=excluir&amp;cod_grupo={$DADOS[grupos].name}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
            </acronym>
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }