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
    <form method="post" name="filas">
    <tr>
       <td class="esq" width="30%">
        {$LANG.select_link_khomp}
       </td>
       <td>
        {html_checkboxes name="listplacas" values="$PLACAS" output="$PLACAS" }
       </td>       
    </tr>

    <tr>
       <td class="esq">
          {$LANG.select_tipo_rel}
       </td>
       <td class="esq">
          <input type="radio" name="tiporel" checked="yes" value="yes"> {$LANG.analitico} <br />
          <input type="radio" name="tiporel" value="no"> {$LANG.sintetico}
       </td>
    </tr>

    
    <tr>
       <td class="esq">
          {$LANG.select_tipo_status}
       </td>
       <td class="esq">
          <input type="radio" name="statusk" value="yes"> {$LANG.yes} <br />
          <input type="radio" name="statusk"  checked="yes" value="no">  {$LANG.no}
       </td>
    </tr>
   
    <tr class="cen">
       <td colspan="3" height="40">     
          <input type="hidden" id="acao" name="acao" value="">

          <input class="button" type="submit" name="relatorio" id="relatorio" value="{$LANG.viewreport}" OnClick="document.filas.acao.value='relatorio';document.getElementById('frescura').style.display='block'">
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
