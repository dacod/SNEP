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
         <td class="cen">{$LANG.ramal}</td>
         <td class="cen" width="24%">{$LANG.extendname}</td>
         <td class="cen">{$LANG.channel}</td>
         <td class="cen">{$LANG.group}</td>
         <td class="cen">{$LANG.vinculo}</td>
         <td class="cent">{$LANG.usevoicemail}</td>
         <td class="cen" colspan="3" width="20px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=ramais loop=$DADOS max=#linelimit# start=$INI}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[ramais].name}</td>
         <td>{$DADOS[ramais].callerid}</td>
         <td class="cen">{$DADOS[ramais].canal}</td>
         <td class="esq">{$DADOS[ramais].group}</td>
         <td class="esq">{$DADOS[ramais].vinculo}</td>
         {assign var="usa_vc" value=$DADOS[ramais].usa_vc}
         <td class="cen">{$OPCAO_YN.$usa_vc}</td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/ramais.php?acao=alterar&amp;id={$DADOS[ramais].id}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center">
         {if $DADOS[ramais].name != 'admin'}
            <acronym title="{$LANG.exclude}">
                <a href="ramais.php?acao=excluir&id={$DADOS[ramais].name}" onclick="return confirm('{$LANG.confirm_remocao_ramal}')"><img src="../imagens/delete.png" alt="{$LANG.exclude}"/></a>
            </acronym>
         {/if}
         </td>
         <form name="formulario" method="post"  action="../configs/permissoes.php" enctype="multipart/form-data">
         <input type="hidden" name="id" value="{$DADOS[ramais].id}" />
         <input type="hidden" name="nome" value="{$DADOS[ramais].callerid}" />
         <input type="hidden" name="name" value="{$DADOS[ramais].name}" />
         <td valign="middle" align="center">
         {if $DADOS[ramais].name != 'admin'}
            <acronym title="{$LANG.permitions}">
               <input type="image" src="../imagens/permitions.png" border="0" alt="{$LANG.permitions}"  name="acao" value="permissoes"/>
            </acronym>
         {/if}
         </td>       
         </form>
      </tr>
   {/section}
   <tr class="dir">
      <td colspan="9" class="links" >
         {include file="paginacao.tpl"}
      </td>
   </tr>
</table>
{ include file="rodape.tpl }