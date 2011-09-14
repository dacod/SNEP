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
 <table>
    <form method="post" name="filas">
 <input type="hidden" name="vinculos" id="vinculos" value="{$VINCULOS}"/>
    <tr style="background-color: #f1f1f1;">
       <td class="esq" width="30%">
               {$LANG.access_level} :
       </td>
       <td class="esq">
           {if $NIVEL == ""}
               {$LANG.stnone}
           {elseif $NIVEL == 1}
               {$LANG.vinculos_todos}
           {else}
               {$NIVEL}
           {/if}
       </td>
    </tr>
   <tr>
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
