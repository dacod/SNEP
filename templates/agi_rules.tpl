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
<div id="main_container">
    {if $ERROR}
    <p class="error_box">
        Um ou mais campos não foram preenchidos corretamente. Por favor, verifique os campos indicados.
    </p>
    {/if}
    <form name="formulario" method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}" onsubmit="atualizaValues()">
        <table class="tabela_regra_negocio">
            <tr>
                <th>{$LANG.desc}:</th>
                <td>
                    <input name="descricao" size="70" class="required campos" value="{$dt_agirules.descricao}" />
                </td>
            </tr>
            <tr>
                <th>{$LANG.origin}:</th>
                <td>
                    <input type="hidden" name="srcValue" id="srcValue" value="--" />
                    <ul id="orig" style="list-style:none; padding: 0px; margin: 0px;"></ul>
                </td>
            </tr>
            <tr>
                <th>{$LANG.destination}:</th>
                <td class="nb subtable">
                    <input type="hidden" name="dstValue" id="dstValue" value="--" />
                    <ul id="dst" style="list-style:none; padding: 0px; margin: 0px;"></ul>
                </td>
            </tr>
            <tr>
                <th>Dias da semana:</th>
                <td>
                    <input type="checkbox" {if $weekDays.mon} checked="checked" {/if} name="mon" id="mon" /><label for="mon">Segunda</label>
                    <input type="checkbox" {if $weekDays.tue} checked="checked" {/if} name="tue" id="tue" /><label for="tue">Terça</label>
                    <input type="checkbox" {if $weekDays.wed} checked="checked" {/if} name="wed" id="wed" /><label for="wed">Quarta</label>
                    <input type="checkbox" {if $weekDays.thu} checked="checked" {/if} name="thu" id="thu" /><label for="thu">Quinta</label>
                    <input type="checkbox" {if $weekDays.fri} checked="checked" {/if} name="fri" id="fri" /><label for="fri">Sexta</label>
                    <input type="checkbox" {if $weekDays.sat} checked="checked" {/if} name="sat" id="sat" /><label for="sat">Sabado</label>
                    <input type="checkbox" {if $weekDays.sun} checked="checked" {/if} name="sun" id="sun" /><label for="sun">Domingo</label>
                </td>
            </tr>
            <tr>
                <th>{$LANG.actiontime}:</th>
                <td>
                    <input type="hidden" name="timeValue" id="timeValue" value="--" />
                    <ul id="time" style="list-style:none; padding: 0px; margin: 0px;"></ul>
                </td>
            </tr>
            <tr>
                <th>Gravação:</th>
                <td>
                    <input type="checkbox" {if $dt_agirules.record} checked="checked" {/if} name="record" id="record" /><label for="record">Habilitar Gravação</label>
                </td>
            </tr>
            <tr>
                <th>{$LANG.execorder}:</th>
                <td>
                    <select name="prioridade" class="campos">
                        {html_options options=$OPCOES_ORDER selected=$dt_agirules.prioridade}
                    </select>
                </td>
            </tr>
        </table>

        <p>
            <label for="action-name">Ação:</label>
            <select class="campos" id="action-name">
                {foreach from=$ACTIONS key=action_id item=action}
                <option id="{$action_id}" value="{$action_id}" label="{$action}">{$action}</option>
                {/foreach}
            </select>
            <input class="new_button" type="button" id="addActionButton" value="Adicionar Ação" />
            <input class="new_button" type="button" id="cleanActionsButton" value="Remover Todas" />
        </p>
        <p class="info_box">
            Você pode reordenar as ações arrastando e soltando o item da ação.
        </p>

        <table id="actions">
            <thead>
                <tr>
                    <th>Ações</th>
                    <th id="action-config-title"></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="actions-list-container">
                        <div id="actions-list-scrollable">
                            <ol id="actions_list"></ol>
                        </div>
                    </td>
                    <td id="actions-config"></td>
                </tr>
            </tbody>
        </table>
        <input name="actions_order" type="hidden" id="actions-order" value="" />
        <p style="text-align: center;">
            <input class="new_button" type="submit" id="acao" value="{$LANG.save}" />
            <input class="new_button" type="button" id="acao" value="Cancelar" onClick="location.href='../gestao/agi_rules.php'" />
        </p>
    </form>
</div>
    { include file="rodape.tpl }
    <script scr="../includes/javascript/functions.js" type="text/javascript"></script>
    <script scr="../includes/javascript/functions_smarty.js" type="text/javascript"></script>
    <script language="javascript" type="text/javascript">
        {$RULE_ACTIONS}
        {* DEFININDO ARRAY COM OS GRUPOS *}
        var group_list = new Array({foreach from=$OPCOES_GRUPOS key=key item=grupo name=grupos}new Array("{$key}","{$grupo}"){if !$smarty.foreach.grupos.last},{/if}{/foreach});
        var trunk_list = new Array({foreach from=$OPCOES_TRONCOS key=key item=tronco name=troncos}new Array("{$tronco.id}","{$tronco.name}"){if !$smarty.foreach.troncos.last},{/if}{/foreach});
        var contacts_group_list = new Array({foreach from=$OPCOES_CONTACTS_GROUPS key=key item=grupo name=grupo}new Array("{$key}","{$grupo}"){if !$smarty.foreach.grupo.last},{/if}{/foreach});

        var orig = new Array();
        var dst = new Array();

        var str_any = "{$LANG.any}";
        var str_regex = "{$LANG.regex}";
        var str_group = "{$LANG.group}";
        var str_contacts_group = "{$LANG.contacts_group}";

        var str_ramal = "{$LANG.ramal}";
        var str_trunk = "{$LANG.trunk}";
        var str_s     = "{$LANG.no_destiny}";

        window.onload = function() {ldelim}
        origObj = new MultiWx('orig', SrcField);
        {$dt_agirules.src}
        origObj.render();

        dstObj = new MultiWx('dst', DstField);
        {$dt_agirules.dst}
        dstObj.render();

        timeObj = new MultiWx('time', TimeField);
        {$dt_agirules.time}
        timeObj.render();
        {rdelim}

        function atualizaValues() {ldelim}
        $('srcValue').value  = origObj.getValue();
        $('dstValue').value  = dstObj.getValue();
        $('timeValue').value = timeObj.getValue();
        {rdelim}
    </script>
    {$JS}