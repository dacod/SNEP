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
<script src="../includes/javascript/prototype.js"></script>
<script src="../includes/javascript/cookie.js"></script>
<script src="../includes/javascript/jsvalidate.js"></script>
<form name="formulario" method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}" onsubmit="atualizaValues()">
         <table cellspacing="0" cellpadding="0" border="0" class="nb" >
            <tr>
                 <td style="vertical-align:top;padding: 8px;">
                     <table cellspacing="0" >
                        <tr  class="nb">
                           <td class="nb" style="width:100px;vertical-align: top;"><strong> {$LANG.desc}: </strong> </td>
                           <td class="nb">                               
                              <textarea name="descricao" cols="85"  rows="2" class="required campos">{$dt_agirules.descricao}</textarea>
                           </td>
                        </tr>
                     </table>
                     <table cellspacing="0" class="nbl">
                        <tr class="nb">
                           <td class="nb" style="width:100px; vertical-align: top;"><strong>{$LANG.origin}:</strong></td>
                           <td class="nb subtable" >
                              <input type="hidden" name="srcValue" id="srcValue" value="--" />
                              <ul id="orig" style="list-style:none; padding: 0px; margin: 0px;"></ul>
                           </td>
                        </tr>

                        <tr class="bty" style="border-top: none;">
                           <td class="nb" style="width:100px;vertical-align: top;"><strong>{$LANG.destination}:</strong></td>
                           <td class="nb subtable">
                              <input type="hidden" name="dstValue" id="dstValue" value="--" />
                              <ul id="dst" style="list-style:none; padding: 0px; margin: 0px;"></ul>
                           </td>
                        </tr>
                     </table>
                     <table cellspacing="0" >
                        <tr>
                           <td class="nb">
                             <strong> {$LANG.actiontime}:</strong><br />
                             <input type="checkbox" {if $weekDays.mon} checked="checked" {/if} name="mon" id="mon" /><label for="mon">Segunda</label>
                             <input type="checkbox" {if $weekDays.tue} checked="checked" {/if} name="tue" id="tue" /><label for="tue">Terça</label>
                             <input type="checkbox" {if $weekDays.wed} checked="checked" {/if} name="wed" id="wed" /><label for="wed">Quarta</label>
                             <input type="checkbox" {if $weekDays.thu} checked="checked" {/if} name="thu" id="thu" /><label for="thu">Quinta</label>
                             <input type="checkbox" {if $weekDays.fri} checked="checked" {/if} name="fri" id="fri" /><label for="fri">Sexta</label>
                             <input type="checkbox" {if $weekDays.sat} checked="checked" {/if} name="sat" id="sat" /><label for="sat">Sabado</label>
                             <input type="checkbox" {if $weekDays.sun} checked="checked" {/if} name="sun" id="sun" /><label for="sun">Domingo</label>
                             <input type="hidden" name="timeValue" id="timeValue" value="--" />
                             <ul id="time" style="list-style:none; padding: 0px; margin: 0px;"></ul>
                           </td>
                        </tr>
                        <tr>
                           <td class="nb">
                           <strong>{$LANG.autorized}:</strong>
                           {html_radios name="autorizado" checked=$dt_agirules.autorizado options=$OPCOES_SN }
                           </td>
                        </tr>
                        <tr>
                           <td class="nb">
                           <strong>{$LANG.mixmonitor}:</strong>
                           {html_radios name="gravacao" checked=$dt_agirules.gravacao options=$OPCOES_SN }
                           </td>
                        </tr>
                        <tr>
                           <td class="nb"><strong>{$LANG.execorder}:</strong>
                              <select name="prioridade" class="campos">
                                 {html_options options=$OPCOES_ORDER selected=$dt_agirules.prioridade}
                              </select>
                           </td>
                        </tr>
                     </table>
                     <br />

                    <div class="seta">
                        
                        {$LANG.tit_action_rules}: <br /><br />

                        <span id="titulo" class="tit">{$LANG.redirect_to} :</span>
                        
                        <a href="#" class="links_include_left" onclick="x.newnode('exten','','','','','','');">{$LANG.ramal} </a>
                        <a href="#" class="links_include_left" onclick="x.newnode('trunk','','','','','','','');">{$LANG.tronco} </a>
                        <a href="#" class="links_include_left" onclick="x.newnode('context','','','','','','');">{$LANG.context}</a>
                        <a href="#" class="links_include_left" onclick="x.newnode('queue','','','','','','');">{$LANG.row}</a>
                        <br /><br /><br />

                        <span id="titulo" class="tit">{$LANG.src_dst} :</span>
                        

                        <a href="#" class="links_include_left" onclick="x.newnode('alterar','','','','','','');">{$LANG.edit} </a>
                        <a href="#" class="links_include_left" onclick="x.newnode('define','','');">{$LANG.define} </a>
                        <a href="#" class="links_include_left" onclick="x.newnode('restore','','');">{$LANG.restore}</a>
                        <br /><br /><br />

                        <span id="titulo" class="tit">{$LANG.actions} :</span>
                        
                        <a href="#" class="links_include_left" onclick="x.newnode('padlock','','','','','','');">{$LANG.padlock}</a>
                        <a href="#" class="links_include_left" onclick="x.newnode('loop','','','','','','');">{$LANG.loop}</a>
                    </div>
                    <div style="width:650px;">
                        <input type="hidden" id="indice" name="indice" value="">
                        <input type="hidden" id="ids" name="ids" value="">

                        <div id="semacao"  style="background-color: #FFFA7C;padding:5px;">
                            <img src="../imagens/ico_info.png" style="float:right;">
                            {$LANG.no_action}
                        </div>

                        <div id="acoes">
                           <ol id="myList" class="myList">
                           </ol>
                        </div>
                    </div>
            </td>
         </tr>
     </table>
    <table>
        <tr>
         <td colspan="2" class="subtable" align="center" height="38px" valign="middle">
            <input type="hidden" name="codigo" value="{$codigo}" />
            <input class="button" type="submit" id="acao" value="{$LANG.save}">
            <div class="buttonEnding"></div>
            &nbsp;&nbsp;&nbsp;
            <input class="button" type="button" id="acao" value="{$LANG.back}" onClick="location.href='../gestao/rel_agi_rules.php'" />
            <div class="buttonEnding"></div>
         </td>
      </tr>
    </table>
    
</form>
{ include file="rodape.tpl }

<script scr="../includes/javascript/functions.js"></script>
<script scr="../includes/javascript/functions_smarty.js"></script>
<script src="../includes/javascript/prototype.js"></script>
<script src="../includes/javascript/snep.js"></script>
<script language="javascript" type="text/javascript">

    {* DEFININDO ARRAY COM OS GRUPOS *}
    var ccusto_list = new Array({foreach from=$OPCOES_CC key=key item=ccusto name=ccusto} new Array("{$key}","{$ccusto}"){if !$smarty.foreach.ccusto.last},{/if}{/foreach});
    var group_list = new Array({foreach from=$OPCOES_GRUPOS key=key item=grupo name=grupos}new Array("{$key}","{$grupo}"){if !$smarty.foreach.grupos.last},{/if}{/foreach});
    var trunk_list = new Array({foreach from=$OPCOES_TRONCOS key=key item=tronco name=troncos}new Array("{$tronco.id}","{$tronco.name}"){if !$smarty.foreach.troncos.last},{/if}{/foreach});
    var filas_list = new Array({foreach from=$OPCOES_FILAS key=key item=fila name=fila}new Array("{$key}","{$fila}"){if !$smarty.foreach.fila.last},{/if}{/foreach});
    
    var orig = new Array();
    var dst = new Array();

    var str_any = "{$LANG.any}";
    var str_regex = "{$LANG.regex}";
    var str_group = "{$LANG.group}";

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
    
<script src="../includes/javascript/agi_rules.js"></script>
{$JS}