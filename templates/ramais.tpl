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
{if $khomp_error}
    <div class="error">{$LANG.khomp_exten_error}</div>
    {/if}
    <form name="formulario" id="formulario" method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">
        <table style="border-bottom: none;" cellspacing="0" align="center">
            <tr>
                <td class="formlabel">{$LANG.ramal}:</td>
                <td class="subtable">
                    <input name="name" id="name" type="text" size="5" maxlength="50"  value="{$dt_ramais.name}" {if $ACAO == "grava_alterar"} readonly="true"  class="campos_disable" {else}  class="campos" {/if} />
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.secret}:</td>
                <td class="subtable">
                    <input name="password" type="password" size="25" maxlength="50" class="campos" value="{$dt_ramais.password}">{$LANG.onlynumbers}
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.extendname}:</td>
                <td class="subtable">
                    <input name="callerid" type="text" size="40" maxlength="80" class="campos" value="{$dt_ramais.callerid}" {if $ACAO != "grava_alterar"} onBlur="this.value=this.value+' <'+this.form.elements['name'].value+'>'" onFocus="this.value='';" {/if} />
                </td>
            </tr>
            <tr>
                <td class="formlabel">Grupo de Ramais:</td>
                <td class="subtable">
                    <select name="group" class="campos">
                        {html_options options=$OPCOES_USERGROUPS selected=$dt_ramais.group}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.pickupgroup}:</td>
                <td class="subtable">
                    <select name="pickupgroup" size="1" class="campos">
                        {html_options options=$OPCOES_GRUPOS selected=$dt_ramais.pickupgroup}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="formlabel" style="vertical-align:top;">Voicemail:</td>
                <td class="subtable">
                    {assign var="vc" value=""}
                    {if $dt_ramais.usa_vc == "yes"}
                    {assign var="vc" value="checked"}
                    {/if}
                    <input id="voicemail" onchange="this.checked?$('email').enable():$('email').disable();" type="checkbox" name="usa_vc" {$vc} /><label for="voicemail">Habilitar</label>
                </td>
            </tr>
            <tr>
                <td class="formlabel" style="vertical-align:top;">E-Mail:</td>
                <td class="subtable">
                    <input {if $dt_ramais.usa_vc != "yes"}disabled{/if} name="email" id="email" type="text" size="40" maxlength="150" class="campos" value="{$dt_ramais.email}" />
                </td>
            </tr>
            <tr>
                <td class="formlabel">Bloqueio:</td>
                <td class="subtable">
                    {assign var="auth" value=""}
                    {if $dt_ramais.usa_auth == "yes"}
                    {assign var="auth" value="checked"}
                    {/if}
                    <input type="checkbox" name="usa_auth" {$auth} id="auth" /><label for="auth">Bloquear Ramal</label>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold; text-align:center;">Opções Avançadas</td>
            </tr>
            <tr>
                <td class="formlabel">Tecnologia:</td>
                <td class="subtable">
                    <select name="canal" id="canal" onchange="show_tab(this.value);">
                        <option value="sip" {if $dt_ramais.channel_tech == "SIP"}selected{/if}>SIP</option>
                        <option value="iax2" {if $dt_ramais.channel_tech == "IAX2"}selected{/if}>IAX2</option>
                        <option value="khomp" {if $dt_ramais.channel_tech == "KHOMP"}selected{/if}>Khomp</option>
                        <option value="virtual" {if $dt_ramais.channel_tech == "VIRTUAL"}selected{/if}>Virtual</option>
                        <option value="manual" {if $dt_ramais.channel_tech == "MANUAL"}selected{/if}>Manual</option>
                    </select>
                </td>
            </tr>
        </table>
        <!-- SIP E IAX2  -->
        <table id="ip" cellspacing="0" align="center" style="display:{if $dt_ramais.channel_tech == "SIP" || $dt_ramais.channel_tech == "IAX2"}block{else}none{/if}; border-top: none; border-bottom: none;">
            <tr>
                <td class="formlabel">{$LANG.secret}:</td>
                <td class="subtable">
                    <input name="secret" type="password" class="campos" value="{$dt_ramais.secret}" >
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.calllimit}:</td>
                <td class="subtable">
                    <input name="calllimit" type="text" size="2" maxlength="2" class="campos" value="{$dt_ramais.call_limit}" >
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.nat}:</td>
                <td class="subtable">
                    {html_radios name="nat" checked=$dt_ramais.nat options=$OPCOES_YN}
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.qualify}:</td>
                <td class="subtable">
                    {html_radios name="qualify" checked=$dt_ramais.qualify options=$OPCOES_YN}
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.peer_type}:</td>
                <td class="subtable">
                    {html_radios name="type" checked=$dt_ramais.type options=$TYPES}
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.dtmf}:</td>
                <td class="subtable">
                    {html_radios name="dtmfmode" checked=$dt_ramais.dtmfmode options=$OPCOES_DTMF}
                </td>
            </tr>
            <tr>
                <td class="formlabel">{$LANG.allow_codecs}:</td>
                <td class="subtable">
                    <select name="cod1" size="1" class="campos">
                        {html_options options=$OPCOES_CODECS selected=$dt_ramais.cod1}
                    </select>
                    <select name="cod2" size="1" class="campos">
                        {html_options options=$OPCOES_CODECS selected=$dt_ramais.cod2}
                    </select>
                    <select name="cod3" size="1" class="campos">
                        {html_options options=$OPCOES_CODECS selected=$dt_ramais.cod3}
                    </select>
                    <select name="cod4" size="1" class="campos">
                        {html_options options=$OPCOES_CODECS selected=$dt_ramais.cod4}
                    </select>
                    <select name="cod5" size="1" class="campos">
                        {html_options options=$OPCOES_CODECS selected=$dt_ramais.cod5}
                    </select>
                </td>
            </tr>
        </table>
        <table id="khomp" style="display:{if $khomp_channel}block{else}none{/if}; border-top: none; border-bottom: none;" >
            <tr>
                {if $no_khomp}
                <td class="formlabel"></td>
                <td class="subtable">{$LANG.no_khomp}</td>
                {else}
                <td class="formlabel">{$LANG.board}:</td>
                <td class="subtable">
                    <select class="campos" onchange="update_channel_list()" id="khomp_boards" name="khomp_boards">
                        <option></option>
                        {foreach from=$khomp_boards key=placa item=foo}
                        <option value="{$placa}" {if $khomp_board===$placa}selected="true"{/if}>{$placa}</option>
                        {/foreach}
                    </select>
                </td>
                <td class="formlabel">{$LANG.channel}:</td>
                <td class="subtable">
                    <select class="campos" id="khomp_channels" name="khomp_channels">
                        {if $khomp_channel}
                        {foreach from=$khomp_channels key=channel item=foo}
                        <option value="{$channel}" {if $khomp_channel===$channel}selected="true"{/if}>{$channel+1}</option>
                        {/foreach}
                        {/if}
                    </select>
                </td>
                {/if}
            </tr>
        </table>

        <table id="virtual" style="display:{if $dt_ramais.channel_tech == "VIRTUAL"}block{else}none{/if}; border-top: none; border-bottom: none;">
               <tr>
                         <td class="formlabel">{$LANG.trunk}:</td>
                         <td class="subtable">
                             <select id="trunk" name="trunk">
                                 {html_options options=$TRUNKS selected=$dt_ramais.trunk}
                             </select>
                         </td>
                     </tr>
                 </table>

                 <table id="manual" style="display:{if $dt_ramais.channel_tech == "MANUAL"}block{else}none{/if}; border-top: none; border-bottom: none;">
                        <tr>
                             <td class="formlabel"><label for="manual_field">Manual:</label></td>
                             <td class="subtable"><input class="campos" type="text" id="manual_field" name="manual" value="{$dt_ramais.manual}" /></td>
                         </tr>
                     </table>

                     <table style="border-top: none;">
                     <tr>
                         <td class="formlabel"><strong>{$LANG.minute_control}:</strong></td>
                         <td class="subtable">
                             <label><input type="radio" name="tempo" value="s" {if $dt_ramais.time == 's'}checked="checked"{/if} />{$LANG.yes}</label>
                             <label><input type="radio" name="tempo" value="n" {if $dt_ramais.time == 'n'}checked="checked"{/if} />{$LANG.no}</label>
                         </td>
                     </tr>
                     <tr>
                         <td class="formlabel">{$LANG.total_time}:</td>
                         <td class="subtable">
                             <input name="time_total" type="text" size="10" maxlength="50"  class="campos" value="{$dt_ramais.time_total}"  /> {$LANG.minutes}
                         </td>
                     </tr>
                     <tr>
                         <td class="formlabel">{$LANG.time_type}:</td>
                         <td class="subtable">
                             <label><input type="radio" name="time_chargeby" value="Y" {if $dt_ramais.time_chargeby == 'Y'}checked="checked"{/if} />{$LANG.yearly}</label>
                             <label><input type="radio" name="time_chargeby" value="M" {if $dt_ramais.time_chargeby == 'M'}checked="checked"{/if} />{$LANG.monthly}</label>
                             <label><input type="radio" name="time_chargeby" value="D" {if $dt_ramais.time_chargeby == 'D'}checked="checked"{/if} />{$LANG.diary}</label>
                         </td>
                     </tr>
                     <tr>
                         <td class="formlabel">
                             {$LANG.view_queues_select}:
                         </td>
                         <td class="subtable">
                             <table  class="subtable">
                                 <tr>
                                     <td rowspan="2" class="subtable" width="40%">
                                         <strong>{$LANG.availables}</strong><br />
                                         <select name="filas_disp[]" id="filas_disp" multiple="true" size="4" class="campos" style="width: 170px;">
                                         {html_options options=$FILAS_DISP}
                                         </select>
                                     </td>
                                     <td class="subtable"  align="center">
                                         <a href="#"  onclick="movimento('filas_selec', 'passar', 'filas_disp')">
                                             <img src="../imagens/go-next.png" border="0" />
                                         </a>
                                     </td>
                                     <td  class="subtable" rowspan="2" width="40%">
                                         <strong>{$LANG.selecteds}</strong><br />
                                         <select  class="campos" name="filas_selec[]" multiple="true" id="filas_selec" size="4" style="width: 170px;" >
                                             {html_options options=$FILAS_SELEC}
                                         </select>
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="subtable" align="center">
                                         <a href="#" onclick="movimento('filas_disp', 'passar','filas_selec')">
                                             <img src="../imagens/go-previous.png" border="0" />
                                         </a>
                                     </td>
                                 </tr>
                             </table>
                         </td>
                     </tr>
                     <tr>
                         <td colspan="2" class="subtable" align="center" height="40px" valign="middle" style="border-top: 1px solid #ccc;">
                             <input class="new_button" type="submit" id="gravar" value="{$LANG.save}" />
                             &nbsp;&nbsp;&nbsp;
                             <input class="new_button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/extensions.php'" />
                         </td>
                     </tr>
                 </table>
        <input type="hidden" name="old_name" value="{$dt_ramais.old_name}" >
        <input type="hidden" name="old_vinculo" value="{$dt_ramais.old_vinculo}" >
        <input type="hidden" name="old_authenticate" value="{$dt_ramais.old_authenticate}" >
        <input type="hidden" name="no_vc" value="{$dt_ramais.no_vc}" >
        <input type="hidden" name="id" value="{$dt_ramais.id}" >
        </form>

        { include file="rodape.tpl" }

        <script language="javascript" type="text/javascript">
            document.forms[0].elements[0].focus() ;

            {literal}
            var tabs = new Array(
            $('ip'),
            $('khomp'),
            $('virtual'),
            $('manual')
        );

            function show_tab(tab) {
                tab = tab.toLowerCase();
                for(element=0;element < tabs.length; element++) {
                    tabs[element].hide();
                }

                if(tab == 'sip' || tab == 'iax2') {
                    tab = 'ip';
                }

                $(tab).show();
            }
            {/literal}
            // Checa campos do formularuio
            function check_form() {ldelim}
            var listBox = document.formulario.filas_selec;
            var len = listBox.length;
            for(var x=0;x<len;x++){ldelim}
            listBox.options[x].selected= true;
            {rdelim}
            var campos = new Array() ;
            campos[0]="{$LANG.extendname};"+document.formulario.callerid.value+";NAME_PEER;)"  ;
            campos[1]="{$LANG.name};"+document.formulario.name.value+";NOT_NULL;";
            campos[2]="{$LANG.secret};"+document.formulario.secret.value+";NOT_NULL;";
            campos[3]="{$LANG.calllimit_in};"+document.formulario.incominglimit.value+";NUM;";
            campos[4]="{$LANG.calllimit_out};"+document.formulario.outgoinglimit.value+";NUM;";
            if(document.formulario.canal[2].checked) {ldelim}
            campos[5]="{$LANG.khomp_channel};"+document.formulario.khomp_channels.value+";NOT_NULL;";
            {rdelim}
            var ctd = 5 ;
            if ( document.formulario.usa_vc[0].checked ) {ldelim}
            campos[ctd]="{$LANG.voicemail_passwd};"+document.formulario.senha_vc.value+";NUM;";
            ctd ++ ;
            {rdelim}
            if (document.formulario.usa_auth[0].checked) {ldelim}
            if (document.formulario.authenticate.value != document.formulario.old_authenticate.value)
                campos[ctd] = "{$LANG.authenticate_passwd};"+document.formulario.authenticate.value+";NUM;";

            {rdelim}
            return valida_formulario(campos) ;
            {rdelim}
            { include file="../includes/javascript/functions_smarty.js" }
            {if $khomp_board}
            window.onload = function() {ldelim}
            load_khomp('{$khomp_board}','{$khomp_channel}');
            {rdelim}
            {/if}
        </script>

        <script type="text/javascript" src="../includes/javascript/fselects.js"></script>
        <script type="text/javascript" src="../includes/javascript/ramais.js"></script>
