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
<table cellspacing="0" cellpadding="0" border="0" align="center" >   
   <thead>
      <tr>
         <td class="cen">{$LANG.id}</td>
         <td class="cen" width="15%">{$LANG.name}</td>
         <td class="cen">{$LANG.firstmin}</td>
         <td class="cent">{$LANG.outmin}</td>
         <td class="cen">{$LANG.vlrbase_fix}</td>
         <td class="cent">{$LANG.vlrbase_cel}</td>
         <td class="cen" colspan="2" width="100px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=operadoras loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[operadoras].codigo}</td>
         <td>{$DADOS[operadoras].nome}</td>
         <td class="cen">{$DADOS[operadoras].tpm}</td>
         <td class="cen">{$DADOS[operadoras].tdm}</td>
         <td class="dir">{$DADOS[operadoras].tbf|string_format:"%.2f"}</td>
         <td class="dir">{$DADOS[operadoras].tbc|string_format:"%.2f"}</td>
         <td align="center" valign="middle" width="30px">
            <acronym title="{$LANG.change}">
               <a href="../tarifas/operadoras.php?acao=alterar&amp;id={$DADOS[operadoras].codigo}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center"  width="30px">
            <acronym title="{$LANG.exclude}">
               <a href="../tarifas/operadoras.php?acao=excluir&amp;id={$DADOS[operadoras].codigo}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
            </acronym>          
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }
