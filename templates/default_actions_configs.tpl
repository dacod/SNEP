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
{config_load file="../includes/setup.conf" section="cores"}
{config_load file="../includes/setup.conf" section="ambiente"}
<table>
    <thead>
        <tr>
            <th>{$LANG.name}</th>
            <th>{$LANG.desc}</th>
            <th>{$LANG.actions}</th>
        </tr>
    </thead>
    <tbody>
        {section name=acao loop=$ACOES}
        
        {if $cor_bg == #COR_GRID_A#}
           {assign var="cor_bg" value=#COR_GRID_B#}
        {else}
           {assign var="cor_bg" value=#COR_GRID_A#}
        {/if}
        
        <tr bgcolor="{$cor_bg}" >
            <td>{$ACOES[acao].name}</td>
            <td>{$ACOES[acao].description}</td>
            <td><a href="./configure_action.php?id={$ACOES[acao].id}">{$LANG.configure}</a></td>
        </tr>
        
        {/section}
    </tbody>
</table>
{include file="rodape.tpl"}
