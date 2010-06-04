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
{config_load file="../includes/setup.conf" section="ambiente"}
{config_load file="../includes/setup.conf" section="cores"}
{math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >
   <thead>
      <tr>
         <td class="cen">{$LANG.operadora}</td>
         <td class="cen" width="10%">{$LANG.country}</td>
         <td class="cen" width="20%">{$LANG.city}</td>
         <td class="cen" width="5%">{$LANG.state}</td>
         <td class="cen">{$LANG.prefix}</td>
         <td class="cen" width="5%">{$LANG.ddd}</td>
         <td class="cen" width="15%">{$LANG.valid_date}</td>
         <td class="cen" width="10%">{$LANG.vlrbase_fix}</td>
         <td class="cen" width="10%">{$LANG.vlrbase_cel}</td>
         <td class="cen" colspan="2" width="100px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=tarifas loop=$DADOS max=#linelimit# start=$INI}
      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         {assign var="operadora" value=$DADOS[tarifas].operadora}
         <td class="cen">{$OPERADORAS.$operadora}</td>
         <td class="esq">{$DADOS[tarifas].pais}</td>
         <td class="esq">{$DADOS[tarifas].cidade}</td>
         <td class="cen">{$DADOS[tarifas].estado}</td>
         <td class="cen">{$DADOS[tarifas].prefixo}</td>
         <td class="cen">{$DADOS[tarifas].ddd}</td>
         <td class="cen">{$DADOS[tarifas].data}</td>         
         <td class="dir">{$DADOS[tarifas].vfix|string_format:"%.2f"}</td>
         <td class="dir">{$DADOS[tarifas].vcel|string_format:"%.2f"}</td>       
         <form name="acao" method="post" action="../tarifas/tarifas.php">
            <td align="center" valign="middle" width="30px;">
               <acronym title="{$LANG.change}">
                    <a href="../tarifas/tarifas.php?acao=alterar&amp;codigo={$DADOS[tarifas].codigo}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
               </acronym>
            </td>
            <td valign="middle" align="center" width="30px;">
               <acronym title="{$LANG.exclude}">
                  <a href="../tarifas/tarifas.php?acao=excluir&amp;codigo={$DADOS[tarifas].codigo}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
               </acronym>
            </td>
            <input type="hidden" name="codigo" value="{$DADOS[tarifas].codigo}" />
         </form>
      </tr>
   {/section}
   <tr class="dir">
      <td colspan="12" class="links" >
         {include file="paginacao.tpl"}
      </td>
   </tr>
</table>
{ include file="rodape.tpl }
