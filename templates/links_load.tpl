{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: links_form.tpl - Monitoramento de Links Khomp.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Rafael Bozzetti <rafael@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
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
