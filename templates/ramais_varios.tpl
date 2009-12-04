{* Smarty *}
{* ----------------------------------------------------------------------------
 * Template: ramais_varios.tpl - Formulario para Cadastro de Varios Ramais
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}
 {include file="cabecalho.tpl"}
 <form name="formulario" id="formulario" method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();" style="margin:0px;padding:0px;">
 <table cellspacing="0" align="center" class="contorno" style="border-bottom:none;">    
    <tr>
       <td class="formlabel">{$LANG.menu_ramais}:</td>
       <td class="subtable">
           <input type="text" name="extensions_range" id="extensions_range" size="50" class="campos" /><br />
           <small>Ex: 1000-1050;1060;1063;1070-1100</small>
       </td>
    </tr>
     <tr>
       <td class="formlabel">{$LANG.ramaisgroups}:</td>
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
       <td class="formlabel">{$LANG.channel}:</td>
       <td class="subtable">
          <select name="tech" id="tech" class="campos" onchange="swap_tab(this.value.toLowerCase())">
             {html_options options=$OPCOES_CANAL}
          </select>
       </td>
    </tr>
</table>
<table id="virtual" cellspacing="0" align="center" style="display:none; border-top:none; border-bottom:none;">
    <tr>
       <td class="formlabel">{$LANG.trunk}:</td>
       <td class="subtable">
          <select name="trunk" id="trunk" class="campos">
             {html_options options=$TRUNKS}
          </select>
       </td>
    </tr>
</table>
<table id="khomp" cellspacing="0" align="center" style="display:none; border-top:none; border-bottom:none;">
    <tr>
       <td class="formlabel">Ocupar os canais disponíveis das placas:</td>
       <td class="subtable">
          {section name=fxs loop=$FXSS}
          <input type="checkbox" id="{$FXSS[fxs].serial}" name="fxs[{$FXSS[fxs].id}]" /> <label for="{$FXSS[fxs].serial}">B{$FXSS[fxs].id} {$FXSS[fxs].model} ({$FXSS[fxs].serial})</label>
          {cycle values=",<br />"}
          {/section}
       </td>
    </tr>
</table>
<table id="ip" cellspacing="0" align="center" style="border-top:none; border-bottom:none;">
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
<table cellspacing="0" align="center">
    <tr>
        <td colspan="2" class="subtable" align="center" height="32px" valign="top">
            <input class="button" type="submit" id="gravar" value="{$LANG.save}">
            <div class="buttonEnding"></div>
            &nbsp;&nbsp;&nbsp;
            <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_ramais.php'" />
            <div class="buttonEnding"></div>
            <input type="hidden" name="old_name" value="{$dt_ramais.old_name}" >
            <input type="hidden" name="old_vinculo" value="{$dt_ramais.old_vinculo}" >
            <input type="hidden" name="old_authenticate" value="{$dt_ramais.old_authenticate}" >
            <input type="hidden" name="no_vc" value="{$dt_ramais.no_vc}" >
            <input type="hidden" name="id" value="{$dt_ramais.id}" >
        </td>
    </tr>
</table>
</form>

 { include file="rodape.tpl" }
<script language="javascript" type="text/javascript">
    document.getElementById('extensions_range').focus();

    {literal}

    var tabs = new Array(
        $('ip'),
        $('khomp'),
        $('virtual')
    );
        
    function swap_tab( tab ) {
        for(element=0;element < tabs.length; element++) {
            tabs[element].hide();
        }
        
        if(tab == "sip" || tab == "iax2") {
            tab = "ip";
        }

        $(tab).show();
    }

    {/literal}
   // Checa campos do formularuio
   function check_form() {ldelim}
      var campos = new Array() ;
      var ini = document.formulario.ramal_ini.value ;
      var fim = document.formulario.ramal_fim.value ;
      var canal = $('canais').value;


      // Verifica se valor Inicial eh menor que final
      if (fim <= ini) {ldelim}
         alert('{$LANG.msg_inigreatend}') ;
         return false
      {rdelim}

      if (canal == '') {ldelim}
         alert('{$LANG.msg_notecnology}') ;
         return false
      {rdelim}

   {rdelim}   
 </script>
