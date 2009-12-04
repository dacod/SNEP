{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_abandono.tpl - Relatorio de movimenta��o nas filas.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Rafael Bozzetti <rafael@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
 {include file="cabecalho.tpl"}
 <table>
    <form method="post" name="log">
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
                       <input type="text" size="9" maxlength="10" class="campos" name="dia_ini" value="{$dados.dataini}" onKeyUp="mascara_data(this,this.value)"/>&nbsp;dd/mm/aaaa
                       &nbsp;&nbsp;&nbsp;
                       <input type="text" size="4" maxlength="5" class="campos" name="hora_ini" value="{$dados.horaini}" onKeyUp="mascara_hora(this,this.value)"/>&nbsp;hh:mm
                    </td>
                 </tr>
                 <tr>
                    <td class="subtable">
                      {$LANG.ate} :
                    </td>
                    <td class="subtable">
                       <input type="text" size="9" maxlength="10" class="campos" name="dia_fim" value="{$dados.datafim}" onKeyUp="mascara_data(this,this.value)"/>&nbsp;dd/mm/aaaa
                       &nbsp;&nbsp;&nbsp;
                       <input type="text" size="4" maxlength="5" class="campos" name="hora_fim" value="{$dados.horafim}" onKeyUp="mascara_hora(this,this.value)"/>&nbsp;hh:mm
                    </td>
                 </tr>
              </table>
           </td>
        </tr>

        <tr>
           <td class="esq">
              {$LANG.msg_logstats}
           </td>
           <td class="esq">
              <input type="checkbox" onclick="toggleall();" name="status_all" {if $status.all != "" } checked {else} checked {/if} value="ALL" /> {$LANG.all}
              <input type="checkbox" name="status_alert" {if $status.alert != "" } checked {else} {/if} value="ALERT" /> {$LANG.log_stat_alert}
              <input type="checkbox" name="status_cri" {if $status.cri != "" } checked {else} {/if} value="CRIT" /> {$LANG.log_stat_cri}
              <input type="checkbox" name="status_err" {if $status.err != "" } checked {else} {/if} value="ERR" /> {$LANG.log_stat_err}
              <input type="checkbox" name="status_inf" {if $status.inf != "" } checked {else} {/if} value="INFO" /> {$LANG.log_stat_inf}
              <input type="checkbox" name="status_deb" {if $status.deb != "" } checked {else} {/if} value="DEBUG" /> {$LANG.log_stat_deb}
           </td>
        </tr>

        <tr>
           <td class="esq">
              {$LANG.origin}
           </td>
           <td class="esq">
              <input type="text" name="src" id="src" class="campos" value="{$src}"  >
           </td>
        </tr>

        <tr>
           <td class="esq">
              {$LANG.destination}
           </td>
           <td class="esq">
              <input type="text" name="dst" id="dst" class="campos" value="{$dst}" >
           </td>
        </tr>

        <tr class="cen">
           <td colspan="3" height="40">
              <input type="hidden" id="acao" name="acao" value="">

              <input class="button" type="submit" name="relatorio" id="relatorio" value="{$LANG.logger_view}" OnClick="document.log.acao.value='relatorio';document.getElementById('frescura').style.display='block'">
              <div class="buttonEnding"></div>
              &nbsp;&nbsp;&nbsp;

              <input class="button" type="submit" name="relatorio" id="relatorio" value="{$LANG.logger_tail}" OnClick="document.log.acao.value='tail';document.getElementById('frescura').style.display='block'">
              <div class="buttonEnding"></div>


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