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
 <form method="get" action="./debugger.php">
    <table>
        <tr>
            <td style="border-bottom:none; padding: 5px;">
                <input type="hidden" name="acao" value="simulate" />
                <label for="srcType">{$LANG.deb_caller}:</label>
                <select id="srcType" name="srcType" onchange="if(this.value == 'trunk') $('trunk').show(); else $('trunk').hide();">
                    <option {php}echo isset($_GET['srcType']) && $_GET['srcType'] == 'exten' ? "selected" : ""{/php} value="exten">Ramal</option>
                    <option {php}echo isset($_GET['srcType']) && $_GET['srcType'] == 'trunk' ? "selected" : ""{/php} value="trunk">Tronco</option>
                    <option {php}echo isset($_GET['srcType']) && $_GET['srcType'] == 'undefined' ? "selected" : ""{/php} value="undefined">Indefinido</option>
                </select>
                <select id="trunk" name="trunk" style="{php}echo isset($_GET['srcType']) && $_GET['srcType'] == 'trunk' ? "" : "display:none;"{/php}">
                    {html_options options=$TRUNKS selected=$dt_ramais.trunk}
                </select>
                <input type="text" name="caller" id="caller" class="campos" value="{php}echo isset($_GET['caller']) ? $_GET['caller'] : "";{/php}" />
                <label for="dst">{$LANG.deb_dst}:
                    <input type="text" name="dst" class="campos" id="dst" value="{php}echo isset($_GET['dst']) ? $_GET['dst'] : "";{/php}" />
                </label>
                <label for="time">{$LANG.deb_time}:
                    <input type="text" size="5" name="time" id="time" class="campos" value="{php}echo isset($_GET['time']) ? $_GET['time'] : "";{/php}" /> (hh:mm)
                </label>
            </td>
        </tr>
        <tr>
            <td style="border-top:none; padding: 5px;">
                <input type="submit" value="{$LANG.deb_submit}" class="button">
                <div class="buttonEnding"></div>
                &nbsp;&nbsp;
                <input class="button" type="button" name="voltar" value="{$LANG.back}" onClick="location.href='../gestao/rel_agi_rules.php'" />
                <div class="buttonEnding"></div>
            </td>
        </tr>
        {if $deb_ERROR == "norule"}
        <tr>
            <td>
                <p class="error">{$LANG.deb_norule}</p>
            </td>
        </tr>
        {/if}
        {if $deb_result}
        <tr>
            <td>
                <ul id="input">
                    <li><strong>{$LANG.deb_caller}</strong>: {$deb_input.caller}</li>
                    <li><strong>{$LANG.deb_dst}</strong>: {$deb_input.dst}</li>
                    <li><strong>{$LANG.deb_time}</strong>: {$deb_input.time}</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;">
                <ul id="results">
                    {section name=rule loop=$deb_result}
                    <li class="rule {$deb_result[rule].state}">
                        <ul class="info">
                            <li class="execute"><strong>{$LANG.deb_actions_torun}</strong>:
                                <ul>
                                    {section name=action loop=$deb_result[rule].actions}
                                    <li>{$deb_result[rule].actions[$smarty.section.action.index]}</li>
                                    {/section}
                                </ul>
                            </li>
                            <li class="desc">{$LANG.rule}: {$deb_result[rule].id} {$deb_result[rule].desc}</li>
                            <li class="caller">{$LANG.deb_caller}: {$deb_result[rule].caller}</li>
                            <li class="dst">{$LANG.deb_dst}: {$deb_result[rule].dst}</li>
                            <li class="valid">{$LANG.deb_valid}: {$deb_result[rule].valid}</li>
                        </ul>
                    <li>
                    {/section}
                </ul>
            </td>
        </tr>
        <tr>
            <td>
                <ul id="legenda">
                    <li class="torun">{$LANG.deb_leg_torun}</li>
                    <li class="outdated">{$LANG.deb_leg_outdated}</li>
                    <li class="ignored">{$LANG.deb_leg_ignored}</li>
                </ul>
            </td>
        </tr>
        {/if}
    </table>
</form>
{ include file="rodape.tpl }