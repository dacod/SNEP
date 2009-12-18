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
{config_load file="../includes/setup.conf" section="ambiente"}
{math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="10px">{$LANG.tronco}</td>
         <td class="cen" width="25%">{$LANG.desc}</td>
         <td class="cen" width="20%">{$LANG.technologies}</td>
         <td class="cen" width="15%">{$LANG.trunktype}</td>
         <td class="esq" width="20%">{$LANG.trunkredund}</td>
         <td class="cen" colspan="2" width="10px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=troncos loop=$DADOS max=#linelimit# start=$INI}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[troncos].name}</td>
         <td>{$DADOS[troncos].callerid}</td>
         <td class="cen">{$DADOS[troncos].tecnologias}</td>
         {assign var="tt" value=$DADOS[troncos].trunktype}
         <td class="cen">{$OPCAO_TTRONCO.$tt}</td>
         <td class="esq">{$DADOS[troncos].trunkredund}</td>
         <form name="acao" method="post" action="../src/troncos.php">
         <td align="center" valign="middle" width="30px;">
            <acronym title="{$LANG.change}">
               <a href="../src/troncos.php?acao=alterar&amp;id={$DADOS[troncos].id}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center" width="30px;">
            <acronym title="{$LANG.exclude}">
                <a href="./troncos.php?acao=excluir&id={$DADOS[troncos].id}&name={$DADOS[troncos].name}" onclick="return confirm('{$LANG.confirm_remocao_tronco}')" ><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
            </acronym>          
         </td>
      </tr>
   {/section}
   <tr class="dir">
      <td colspan="9" class="links" >
         {include file="paginacao.tpl"}
      </td>
   </tr>

</table>
{ include file="rodape.tpl }