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
<table cellspacing="0" cellpadding="0" border="0" align="center"  >   
    <thead>
        <tr>
            <td class="cen" width="10px">ID</td>
            <td class="esq">{$LANG.name}</td>
            <td class="cen" colspan="2" width="10px">{$LANG.actions}</td>
        </tr>
    </thead>
    <tbody>
        {foreach from=$ALIASES item=alias}
        <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
            <td class="cen">{$alias.id}</td>
            <td>{$alias.name}</td>
            <td align="center" valign="middle" width="30px" >
                <acronym title="{$LANG.change}">
                    <a href="../gestao/expression_alias.php?action=edit&amp;id={$alias.id}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
                </acronym>
            </td>
            <td valign="middle" align="center" width="30px">
                <acronym title="{$LANG.exclude}">
                    <img src="../imagens/delete.png" alt="{$LANG.exclude}" onclick="remove_grupo('{$alias.id}')"/>
                </acronym>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{ include file="rodape.tpl }

{literal}
<script type="text/javascript">
    /* Confirmacao e remocao de regras de negocio */
    function remove_grupo(id) {
        if(confirm("{/literal} {$LANG.confirm_remocao_grupo} {literal}")) {
            window.location.href="../gestao/expression_alias.php?action=delete&id="+id;
        }
    }    
</script>
{/literal}