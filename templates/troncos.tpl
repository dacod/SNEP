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
 <form name="formulario" id="formulario" method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">
 <table cellspacing="0" align="center" style="border-bottom: none;">
    <tr>
       <td class="formlabel">{$LANG.name}:</td>
       <td class="subtable">
          <input name="name" type="hidden" value="{$dt_troncos.name}" />
          <input name="callerid" type="text" size="40" maxlength="80" class="campos" value="{$dt_troncos.callerid}" />
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.trunktype}:</td>
       <td class="subtable">
          {assign var="tt_SIP" value=""}
          {assign var="tt_IAX2" value=""}
          {assign var="tt_KHOMP" value=""}
          {assign var="tt_VIRTUAL" value=""}
          {assign var="tt_SNEPSIP" value=""}
          {assign var="tt_SNEPIAX2" value=""}

          {if $dt_troncos.trunktype == "SIP"}
             {assign var="tt_SIP" value="selected"}
          {elseif $dt_troncos.trunktype == "IAX2"}
             {assign var="tt_IAX2" value="selected"}
          {elseif $dt_troncos.trunktype == "KHOMP"}
             {assign var="tt_KHOMP" value="selected"}
          {elseif $dt_troncos.trunktype == "VIRTUAL"}
             {assign var="tt_VIRTUAL" value="selected"}
          {elseif $dt_troncos.trunktype == "SNEPSIP"}
             {assign var="tt_SNEPSIP" value="selected"}
          {elseif $dt_troncos.trunktype == "SNEPIAX2"}
             {assign var="tt_SNEPIAX2" value="selected"}
          {/if}

          <select onchange="show_tab(this.value)" name="trunktype" {if $ACAO == "grava_alterar"} disabled="true" {/if}>
              <option value="SIP" {$tt_SIP} >SIP</option>
              <option value="IAX2" {$tt_IAX2} >IAX2</option>
              <option value="KHOMP" {$tt_KHOMP} >Khomp</option>
              <option value="VIRTUAL" {$tt_VIRTUAL} >{$LANG.trunktype_TDM}</option>
              <option value="SNEPSIP" {$tt_SNEPSIP} >Snep SIP</option>
              <option value="SNEPIAX2" {$tt_SNEPIAX2} >Snep IAX2</option>
          </select>
    </tr>
</table>
<div id="ip" {if $dt_troncos.trunktype != 'SIP' && $dt_troncos.trunktype != 'IAX2'} style="display:none;" {/if}>
    <table cellspacing="0" align="center" style="border-top: none; border-bottom: none;">
        <tr>
            <td class="formlabel">{$LANG.dialmethod}</td>
            <td class="subtable">
                <input type="radio" name="dialmethod" value="NORMAL" {if $dt_troncos.dialmethod != 'DTMF' && $dt_troncos.dialmethod != 'NOAUTH' } checked="true" {/if} onclick="withauth()" />{$LANG.normal}
                <input type="radio" name="dialmethod" value="DTMF" {if $dt_troncos.dialmethod == 'DTMF'} checked="true" {/if} onclick="withauth()" />{$LANG.dtmf}
                <input type="radio" name="dialmethod" value="NOAUTH" {if $dt_troncos.dialmethod == 'NOAUTH'} checked="true" {/if} onclick="noauth()" />{$LANG.noauth}
            </td>
        </tr>
    </table>
    <table id="noauth" cellspacing="0" align="center" style="border-top: none; border-bottom: none; {if $dt_troncos.dialmethod != 'NOAUTH'} display:none; {/if}">
        <tr>
           <td class="formlabel">{$LANG.host}</td>
           <td class="subtable">
              <input maxlength="50" size="20" type="text" name="host" value="{$dt_troncos.host_trunk}" class="campos" />
           </td>
        </tr>
    </table>
    <table id="withauth" cellspacing="0" align="center" style="border-top: none; border-bottom: none; {if $dt_troncos.dialmethod == 'NOAUTH'} display:none; {/if}">
        <tr>
           <td class="formlabel">{$LANG.user}:</td>
           <td class="subtable">
              <input name="username" type="text" size="25" maxlength="50" class="campos" value="{$dt_troncos.username}">
           </td>
        </tr>
        <tr>
           <td class="formlabel">{$LANG.secret}:</td>
           <td class="subtable">
              <input name="secret" type="password" size="25" maxlength="50" class="campos" value="{$dt_troncos.secret}">
           </td>
        </tr>
        <tr>
           <td class="formlabel">{$LANG.host}</td>
           <td class="subtable">
              <input maxlength="50" size="20" type="text" name="host_trunk" value="{$dt_troncos.host_trunk}" class="campos" />
           </td>
        </tr>
        <tr>
           <td class="formlabel">{$LANG.from_user}</td>
           <td class="subtable">
              <input maxlength="50" size="20" type="text" name="fromuser" value="{$dt_troncos.fromuser}" class="campos" />
           </td>
        </tr>
        <tr>
           <td class="formlabel">{$LANG.from_domain}</td>
           <td class="subtable">
              <input maxlength="50" size="20" type="text" name="fromdomain" value="{$dt_troncos.fromdomain}" class="campos" />
           </td>
        </tr>
        <tr>
           <td class="formlabel">{$LANG.allow_codecs}:</td>
           <td class="subtable">
              <select name="cod1" size="1" class="campos" >
                 {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod1}
              </select>
              &nbsp;&nbsp;
              <select name="cod2" size="1" class="campos">
                 {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod2}
              </select>
              &nbsp;&nbsp;
              <select name="cod3" size="1" class="campos">
                 {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod3}
              </select>
              &nbsp;&nbsp;
              <select name="cod4" size="1" class="campos">
                 {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod4}
              </select>
              &nbsp;&nbsp;
              <select name="cod5" size="1" class="campos">
                 {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod5}
              </select>
           </td>
        </tr>
        <tr>
           <td class="formlabel">{$LANG.dtmf}:</td>
           <td class="subtable">
              {html_radios name="dtmfmode" checked=$dt_troncos.dtmfmode options=$OPCOES_DTMF}
           </td>
        </tr>
        <tr>
           <td class="formlabel">{$LANG.qualify}:</td>
           <td class="subtable">
               <input type="radio" onclick="quality(this);" name="qualify" id="qualify" value="yes" {if $qualify == 's'}{if $dt_troncos.qualify == 'yes'} checked {/if}{/if} > {$LANG.yes}
               <input type="radio" onclick="quality(this);" name="qualify" id="qualify" value="no"  {if $qualify == 's'}{if $dt_troncos.qualify == 'no'} checked {/if}{/if} > {$LANG.no}
               <input type="radio" onclick="quality(this);" name="qualify" id="qualify" value="specify" {if $qualify == 'e'} checked {/if} > {$LANG.specify}
               <input type="text" name="qualify_time" id="qualify_time" style="width:30px;margin-left: 10px;" class="campos" value="{if $qualify == 'e'} {$dt_troncos.qualify} {/if}" /> millisegundos
           </td>
        </tr>
        <tr>
           <td class="formlabel"><strong>{$LANG.advancedoptions}:</strong></td>
           <td class="subtable">
               <input type="checkbox" name="reverseAuth" id="reverseAuth" {if $dt_troncos.reverseAuth}checked="checked"{/if} /> <label for="reverseAuth">{$LANG.force_reverse_auth}</label>
           </td>
        </tr>

    </table>
</div>
<table id="khomp" cellspacing="0" align="center" style="border-top: none; border-bottom:none; {if $dt_troncos.trunktype != 'KHOMP'} display:none; {/if}" >
    <tr >
       <td class="formlabel">{$LANG.board}:</td>
       <td class="subtable">
            <select name="khomp_board">
                <option value="-1"> - - </option>
                {html_options options=$khomp_boards selected=$dt_troncos.khomp_board}
            </select>
       <td class="subtable">
       </td>
    </tr>
</table>
<table id="virtual" cellspacing="0" align="center" style="border-top: none; border-bottom:none; {if $dt_troncos.trunktype != 'VIRTUAL'} display:none; {/if}" >
    <tr >
       <td class="formlabel">{$LANG.technologies}:</td>
       <td class="subtable">
          <input name="channel" type="text" size="25" maxlength="50" class="campos" value="{$dt_troncos.channel}">
       <td class="subtable">
       </td>
    </tr>
    <tr >
       <td class="formlabel">{$LANG.trunk_regex}:</td>
       <td class="subtable">
          <input name="trunk_regex" type="text" size="25" maxlength="50" class="campos" value="{$dt_troncos.id_regex}"> {$LANG.optional}
       <td class="subtable">
       </td>
    </tr>
</table>
<table id="snepiax2" cellspacing="0" align="center" style="border-top: none; border-bottom:none; {if $dt_troncos.trunktype != 'SNEPIAX2'} display:none; {/if}" >
    <tr >
       <td class="formlabel">{$LANG.snep_username}:</td>
       <td class="subtable">
          <input name="snep_username" type="text" size="25" maxlength="50" class="campos" value="{$dt_troncos.username}"> <small>{$LANG.same_in_both_machines}</small>
       </td>
    </tr>
</table>
<table id="snep" cellspacing="0" align="center" style="border-top: none; border-bottom:none; {if $dt_troncos.trunktype != 'SNEPSIP' && $dt_troncos.trunktype != 'SNEPIAX2'} display:none; {/if}" >
    <tr >
       <td class="formlabel">{$LANG.host}:</td>
       <td class="subtable">
          <input name="snep_host" type="text" size="25" maxlength="50" class="campos" value="{$dt_troncos.host}">
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.allow_codecs}:</td>
       <td class="subtable">
          <select name="snep_cod1" size="1" class="campos" >
             {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod1}
          </select>
          &nbsp;&nbsp;
          <select name="snep_cod2" size="1" class="campos">
             {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod2}
          </select>
          &nbsp;&nbsp;
          <select name="snep_cod3" size="1" class="campos">
             {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod3}
          </select>
          &nbsp;&nbsp;
          <select name="snep_cod4" size="1" class="campos">
             {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod4}
          </select>
          &nbsp;&nbsp;
          <select name="snep_cod5" size="1" class="campos">
             {html_options options=$OPCOES_CODECS selected=$dt_troncos.cod5}
          </select>
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.dtmf}:</td>
       <td class="subtable">
          {html_radios name="snep_dtmf" checked=$dt_troncos.dtmfmode options=$OPCOES_DTMF}
       </td>
    </tr>
</table>
<table cellspacing="0" align="center" style="border-top: none;" >
    <tr>
       <td class="subtable" colspan="2" ><hr /></td>
    </tr>
    <tr>
       <td class="formlabel"><strong>{$LANG.advancedoptions}:</strong></td>
       <td class="subtable">
           <input type="checkbox" name="extensionMapping" id="extensionMapping" {if $dt_troncos.extensionMapping}checked="checked"{/if} /> <label for="extensionMapping">{$LANG.allow_extension_mapping}</label>
       </td>
    </tr>
    {* CONTROLE DE MINUTOS *}
    <tr>
        <td class="formlabel"><strong>{$LANG.minute_control}:</strong></td>
        <td class="subtable">
            <label><input type="radio" name="tempo" value="s" {if $dt_troncos.time == 's'}checked="checked"{/if} />{$LANG.yes}</label>
            <label><input type="radio" name="tempo" value="n" {if $dt_troncos.time == 'n'}checked="checked"{/if} />{$LANG.no}</label>
        </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.total_time}:</td>
       <td class="subtable">
          <input name="time_total" type="text" size="10" maxlength="50"  class="campos" value="{$dt_troncos.time_total}"  /> {$LANG.minutes}
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.time_type}:</td>
       <td class="subtable">
            <label><input type="radio" name="time_chargeby" value="Y" {if $dt_troncos.time_chargeby == 'Y'}checked="checked"{/if} />{$LANG.yearly}</label>
            <label><input type="radio" name="time_chargeby" value="M" {if $dt_troncos.time_chargeby == 'M'}checked="checked"{/if} />{$LANG.monthly}</label>
            <label><input type="radio" name="time_chargeby" value="D" {if $dt_troncos.time_chargeby == 'D'}checked="checked"{/if} />{$LANG.diary}</label>
       </td>
    </tr>

    <tr>
       <td class="subtable" colspan="2" ><hr /></td>
    </tr>
    <tr>
        <td colspan="2" class="subtable" align="center" height="32px" valign="top">
           <input class="button" type="submit" id="gravar" value="{$LANG.save}">
           <div class="buttonEnding"></div>
           &nbsp;&nbsp;&nbsp;
           <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_troncos.php'" />
           <div class="buttonEnding"></div>      
     </td>
    </tr>
    {if $ACAO == "grava_alterar"} 
        <input type="hidden" name="trunktype" value="{$dt_troncos.trunktype}" >
    {/if}
 </table>
     <input type="hidden" name="old_name" value="{$dt_troncos.old_name}" >
    <input type="hidden" name="id" value="{$dt_troncos.id}" >
</form>
 { include file="rodape.tpl" }
 <script language="javascript" type="text/javascript">
   document.forms[0].elements[0].focus() ;
   /* Habilita solicitacao do host para campo Conta */
   /* --------------------------------------------- */
   function enable_type() {ldelim}
     document.formulario.host_trunk.className = 'campos' ;
     document.formulario.host_trunk.disabled =  false;
   {rdelim}
   /* DESabilita solicitacao do host para campo Conta */
   /* ----------------------------------------------- */
   function disable_type() {ldelim}
      document.formulario.host_trunk.className = 'campos_disable' ;
      document.formulario.host_trunk.disabled =  true;
   {rdelim}
   /* Verifica campos digitados */
   /* ------------------------- */
   function check_form() {ldelim}
       var campos = new Array() ;
       campos[0]="{$LANG.alert_desc};"+document.formulario.callerid.value+";NOT_NULL;";
       var ctd = 1 ;
       if ( (document.formulario.trunktype[0].checked || document.formulario.trunktype[1].checked) && !document.formulario.dialmethod[2].checked ) {ldelim}
          campos[ctd]="{$LANG.alert_user};"+document.formulario.username.value+";NOT_NULL;";
          ctd ++ ;
          campos[ctd]="{$LANG.secret};"+document.formulario.secret.value+";NOT_NULL;";
          ctd ++ ;
       {rdelim}
       
       return valida_formulario(campos) ;
   {rdelim}
   /* Determina o que exibir conforme tipo de tronco */
   /* ---------------------------------------------- */
    var tabs = new Array(
        $('ip'),
        $('khomp'),
        $('virtual'),
        $('snepiax2'),
        $('snep')
    );
   function show_tab(tab) {ldelim}
        tab = tab.toLowerCase();
        for(element=0;element < tabs.length; element++) {ldelim}
            tabs[element].hide();
        {rdelim}
        
        if(tab == "snepiax2" || tab == "snepsip") {ldelim}
            $('snep').show();
            if(tab == "snepiax2")
                $('snepiax2').show();
        {rdelim}
        else if(tab == "sip" || tab == "iax2") {ldelim}
            $('ip').show();
        {rdelim}
        else {ldelim}
            $(tab).show();
        {rdelim}
   {rdelim}

   function noauth() {ldelim}
        $('noauth').show();
        $('withauth').hide();
   {rdelim}
   function withauth() {ldelim}
        $('noauth').hide();
        $('withauth').show();
   {rdelim}
   { include file="../includes/javascript/functions_smarty.js" }

   {literal}
   function quality(obj) {
       if(obj.value == 'specify') {
           $('qualify_time').readOnly = false;
           $('qualify_time').removeClassName('campos_disable');
           $('qualify_time').value = 500;
       }else{
           if(obj.value == 'yes') {
               $('qualify_time').value = 2000;
           }
           if(obj.value == 'no') {
               $('qualify_time').value = '';
           }
           $('qualify_time').readOnly = true;
           $('qualify_time').addClassName('campos_disable');
       }
   }
   {/literal}
 </script>
 <script type="text/javascript" src="../includes/javascript/fselects.js"></script>
