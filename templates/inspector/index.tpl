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
{config_load file="../includes/setup.conf" section="cores"}
{include file="filtrar_incluir.tpl"}
<table cellpadding="0" cellspacing="0" border="0" >
    {foreach from=$inspects key=key item=value}

        <tr {if $value.error == 0} style="background-color: #8BE98A;" {else} style="background-color: #F46770;" {/if} >
            <td style="padding: 5px 5px;text-transform:uppercase;">
                {if $value.error == 0}
                    <img style="float:right;" src="../imagens/true.png" />
                {else}
                    <img style="float:right;" src="../imagens/false.png" />
                {/if}
                <b> {$key} </b>
            </td>
        </tr>

        {if $value.error == 1}
            <tr style="font-weight: bolder;background-color:#FFEDED;">
                <td>
                    {$value.message} 
                </td>
            </tr>
        {/if}

    {/foreach}
</table>

{ include file="rodape.tpl }
