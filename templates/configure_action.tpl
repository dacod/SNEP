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

<table>
    <tbody>
        {if $success}
        <tr>
            <td class="success">{$LANG.update_success}</td>
        </tr>
        {/if}
        <tr>
            <td>{$action_form->render()}</td>
        </tr>
    </tbody>
</table>

{include file="rodape.tpl"}
