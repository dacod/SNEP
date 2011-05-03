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
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="15%">{$LANG.id}</td>
         <td class="cen">{$LANG.desc}</td>
         <td class="cen">{$LANG.type}</td>
         <td class="cen" colspan="2" width="20%">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=contas loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[contas].codigo}</td>
         <td>{$DADOS[contas].nome}</td>
         {assign var="tipo_conta" value=$DADOS[contas].tipo}
         <td>{$tipos_contas.$tipo_conta}</td>
         <form name="acao" method="post" action="../src/contas.php">
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <input type="image" src="../imagens/edit.png" border="0" alt="{$LANG.change}" name="acao" value="alterar"  />
            </acronym>
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <input type="image" src="../imagens/delete.png" border="0" alt="{$LANG.exclude}"  name="acao" value="excluir" />
            </acronym>          
         </td>       
         <input type="hidden" name="id" value="{$DADOS[contas].codigo}" />
         </form>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }