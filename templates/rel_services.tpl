{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_abandono.tpl - Relatorio de movimentação nas filas.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Rafael Bozzetti <rafael@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
 {include file="cabecalho.tpl"}
 <table>
    <form method="post" name="filas">
    <tr>
       <td class="esq" width="30%">
        {$LANG.periodo}
       </td>
       <td class="esq">
          <table class="subtable">
             <tr>
                <td class="subtable" width="15%">
                   {$LANG.apartir} :
                </td>
                <td class="subtable">
                   <input type="text" size="9" maxlength="10" class="campos" name="dia_ini" value="{$dt_relchamadas.dia_ini}" onKeyUp="mascara_data(this,this.value)"/>&nbsp;dd/mm/aaaa
                   &nbsp;&nbsp;&nbsp;
                   <input type="text" size="4" maxlength="5" class="campos" name="hora_ini" value="{$dt_relchamadas.hora_ini}" onKeyUp="mascara_hora(this,this.value)"/>&nbsp;hh:mm
                </td>
             </tr>
             <tr>
                <td class="subtable">
                  {$LANG.ate} :
                </td>
                <td class="subtable">
                   <input type="text" size="9" maxlength="10" class="campos" name="dia_fim" value="{$dt_relchamadas.dia_fim}" onKeyUp="mascara_data(this,this.value)"/>&nbsp;dd/mm/aaaa
                   &nbsp;&nbsp;&nbsp;
                   <input type="text" size="4" maxlength="5" class="campos" name="hora_fim" value="{$dt_relchamadas.hora_fim}" onKeyUp="mascara_hora(this,this.value)"/>&nbsp;hh:mm
                </td>
             </tr>
          </table>
       </td>
    <tr>
       <td class="esq">
          {$LANG.envolved_callers}&nbsp;
       </td>
       <td class="esq">
            {$LANG.group} :
            <select name="groupsrc" class="campos" onChange="javascript:grupos('fls', this.value)">
                 {html_options options=$OPCOES_USERGROUPS selected=$groupsrc}
            </select>
          &nbsp;&nbsp;
            <input type="text" name="src" id="src" class="campos" value="{$dt_relchamadas.src}"  >
            {$LANG.morethatone}
       </td>
    </tr>

    <tr>
       <td class="esq">
          {$LANG.services_list}&nbsp;
       </td>
       <td class="esq">
            {foreach from=$SERVICES item=service name=services}
                <input name="services[]" type="checkbox" checked="yes" value="{$service}"> {$service}
            {/foreach}
       </td>
    </tr>

    <tr>
       <td class="esq">
          {$LANG.service_state}&nbsp;
       </td>
       <td class="esq">
                <input name="state[]" type="checkbox" checked="yes" value="1"> {$LANG.service_enable}
                <input name="state[]" type="checkbox" checked="yes" value="0"> {$LANG.service_disable}
       </td>
    </tr>


    <tr class="cen">
       <td colspan="3" height="40">     
          <input type="hidden" id="acao" name="acao" value="">

          <input class="button" type="submit" name="relatorio" id="relatorio" value="{$LANG.viewreport}" OnClick="document.filas.acao.value='relatorio';document.getElementById('frescura').style.display='block'">
          <div class="buttonEnding"></div>
          &nbsp;&nbsp;&nbsp;
          <input class="button" type="submit" name="csv" id="csv" value="{$LANG.viewcsv}"  OnClick="document.filas.acao.value='csv';document.getElementById('frescura').style.display='block'">
          <div class="buttonEnding"></div>
          &nbsp;&nbsp;&nbsp;

          <div align="center" id="frescura" style="display : none;">
              <img src="../imagens/ajax-loader2.gif" width="256" height="24" /><br />
            {$LANG.processing}
          </div>
       </td>
    </tr>
 </form>
 </table>
 { include file="rodape.tpl }
 <script type="text/javascript">
   document.forms[0].elements[0].focus() ;
 </script>
 <script  language="JavaScript" type="text/javascript" scr="../includes/javascript/functions.js"></script>
