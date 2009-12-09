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
   <form method="post" name="gerar">
   <tr>
      <td class="esq" width="30%">
         {$LANG.periodgenerated}:
      </td>
      <td>
         <strong>{$LANG.apartir}</strong>:&nbsp;{$dt_tarifacao.atual_ini|default:"00/00/0000 00:00:00"}
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         <strong>{$LANG.ate}</strong>:&nbsp;{$dt_tarifacao.atual_fim|default:"00/00/0000 00:00:00"}
      </td>
    </tr>
    <tr>
      <td class="esq" width="30%">
         {$LANG.new_periodgenerate}:
      </td>
      <td class="esq">
         <table class="subtable">
            <tr>
               <td class="subtable" width="15%">
                  {$LANG.apartir} :
               </td>
               <td class="subtable">
                  <input type="text" size="9" maxlength="10" class="campos_disable"  name="dia_ini" value="{$dt_tarifacao.dia_ini}" />
                  dd/mm/aaaa
                  &nbsp;&nbsp;&nbsp;
                  <input type="text" size="4" maxlength="5" class="campos_disable"  readonly="true" name="hora_ini" value="{$dt_tarifacao.hora_ini}" />
                  &nbsp;hh:mm
                </td>
            </tr>
            <tr>
               <td class="subtable">
                  {$LANG.ate} :
               </td>
               <td class="subtable">
                  <input type="text" size="9" maxlength="10" class="campos" name="dia_fim" value="{$dt_tarifacao.dia_fim}" onKeyUp="mascara_data(this,this.value)"/>&nbsp;dd/mm/aaaa
                  &nbsp;&nbsp;&nbsp;
                  <input type="text" size="4" maxlength="5" class="campos" name="hora_fim" value="{$dt_tarifacao.hora_fim}" onKeyUp="mascara_hora(this,this.value)"/>&nbsp;hh:mm
               </td>
            </tr>
         </table>
      </td>
   </tr>

   <tr class="cen">
      <td colspan="3" height="40">     
         <input type="hidden" id="acao" name="acao" value="">
         <input class="button" type="submit" name="submit" id="submit" value="{$LANG.generatedata}" OnClick="document.gerar.acao.value='gerar';document.getElementById('frescura').style.display='block'">
         <div class="buttonEnding"></div>
         <div align="center" id="frescura" style="display : none;">
            <img src="../imagens/ajax-loader2.gif" width="256" height="24" /><br />
            {$LANG.waitreport}            
         </div>
      </td>
   </tr>
</form>
</table>
{ include file="rodape.tpl }
<script type="text/javascript">
   document.forms[0].elements[0].focus() ;
</script>
