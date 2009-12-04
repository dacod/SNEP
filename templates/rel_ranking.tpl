{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_ranking.tpl - Ranking das Ligacoes
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
<table>
   <form method="post" name="relatorio">
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
                  <input type="text" size="9" maxlength="10" class="campos" name="dia_ini" value="{$dt_ranking.dia_ini}" onKeyUp="mascara_data(this,this.value)"/>&nbsp;dd/mm/aaaa
                  &nbsp;&nbsp;&nbsp;
                  <input type="text" size="4" maxlength="5" class="campos" name="hora_ini" value="{$dt_ranking.hora_ini}" onKeyUp="mascara_hora(this,this.value)"/>&nbsp;hh:mm
               </td>
            </tr>
            <tr>
               <td class="subtable">
                  {$LANG.ate} :
               </td>
               <td class="subtable">
                  <input type="text" size="9" maxlength="10" class="campos" name="dia_fim" value="{$dt_ranking.dia_fim}" onKeyUp="mascara_data(this,this.value)"/>&nbsp;dd/mm/aaaa
                  &nbsp;&nbsp;&nbsp;
                  <input type="text" size="4" maxlength="5" class="campos" name="hora_fim" value="{$dt_ranking.hora_fim}" onKeyUp="mascara_hora(this,this.value)"/>&nbsp;hh:mm
               </td>
            </tr>
         </table>
      </td>
   </tr>
   <tr>
       <td class="esq">
          {$LANG.rank_type}
       </td>
       <td class="esq">
          {html_radios name="rank_type" checked="$rank_type" options=$OPCOES_RANK}
       </td>
   </tr>
   <tr>
       <td class="esq">
          {$LANG.rank_viewsrc}
       </td>
       <td class="esq">
          <input type="text" size="4" maxlength="5" class="campos" name="rank_num" value="{$rank_num}" />
       </td>
   </tr>
   <tr>
       <td class="esq">
          {$LANG.rank_viewtop}
       </td>
       <td class="esq">
          <select name="viewtop" class="campos">
             {html_options options=$VIEWTOP selected=$viewtop}
          </select>
       </td>
    </tr>         
 
   <tr class="cen">
      <td colspan="3" height="40">     
         <input type="hidden" id="acao" name="acao" value="">
         <input class="button" type="submit" name="submit" id="submit" value="{$LANG.viewreport}" OnClick="document.relatorio.acao.value='relatorio';document.getElementById('frescura').style.display='block'">
         <div class="buttonEnding"></div>
            &nbsp;&nbsp;&nbsp;
         <input class="button" type="submit" name="csv" id="csv" value="{$LANG.viewcsv}" OnClick="document.relatorio.acao.value='csv';document.getElementById('frescura').style.display='block'">
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
