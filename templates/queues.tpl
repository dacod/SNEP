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
 <table cellspacing="0" align="center" class="contorno">
    <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return valida_formulario();">
    <tr>
       <td colspan="2">
          <div id="titulo">
            Dados da Fila de Atendimento
          </div>
       </td>
    </tr>
    <tr>
       <td class="formlabel" style="width: 50%;">{$LANG.q_name}:</td>
       <td class="subtable" >
         <input name="name" id="name" type="text" size="20" maxlength="20"   value="{$dt_queues.name}" {if $ACAO == "grava_alterar"} readonly="true"  class="campos_disable" {else}  class="campos" {/if} />
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.q_musiconhold}:</td>
       <td class="subtable">
        <select name="musiconhold" class="campos">
             {html_options selected=$dt_queues.musiconhold options=$OPCOES_SECAO}
          </select>
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.q_announce}:</td>
       <td class="subtable">
          <select name="announce" id="announce" class="campos">
             {html_options selected=$dt_queues.announce options=$OPCOES_SONS}
          </select>
          <!--
          <a href="#">
             <img src="../imagens/ouvir.png" alt="Ouvir" width="16" height="16" hspace="0" vspace="0" style="border: none; cursor : hand;"  onclick="DHTMLSound('announce')"/>
          </a>
          -->

       </td>
    </tr>   
    <tr>
       <td class="formlabel">{$LANG.q_context}:</td>
       <td class="subtable">
          <input type="text" name="context" class="campos" value="{$dt_queues.context}" />
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.q_timeout}:</td>
       <td class="subtable">   
          <input name="timeout" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.timeout|default:0}" />
          {$LANG.time_secs}
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.q_queue_youarenext}:</td>
       <td class="subtable">
          <select name="queue_youarenext" class="campos">
             {html_options selected=$dt_queues.queue_youarenext options=$OPCOES_SONS}
          </select>      
       </td>
    </tr>    
    <tr>
       <td class="formlabel">{$LANG.q_queue_thereare}:</td>
       <td class="subtable">
          <select name="queue_thereare" class="campos">
             {html_options selected=$dt_queues.queue_thereare options=$OPCOES_SONS}
          </select>
       </td>
    </tr>    
    <tr>
       <td class="formlabel">{$LANG.q_queue_callswaiting}:</td>
       <td class="subtable">
          <select name="queue_callswaiting" class="campos">
             {html_options selected=$dt_queues.queue_callswaiting options=$OPCOES_SONS}
          </select>
       </td>
    </tr>  
    <!--  
    <tr>
       <td class="formlabel">{$LANG.q_queue_holdtime}:</td>
       <td class="subtable">   
          {html_radios name="queue_holdtime" checked=$dt_queues.queue_holdtime options=$OPCOES_HOLDTIME}
       </td>
    </tr>        
    <tr>
       <td class="formlabel">{$LANG.q_queue_minutes}:</td>
       <td class="subtable">   
          <input name="queue_minutes" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.queue_minutes|default:0}" />
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.q_queue_seconds}:</td>
       <td class="subtable">   
          <input name="queue_seconds" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.queue_seconds|default:0}"  />
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.q_queue_lessthan}:</td>
       <td class="subtable">   
          <input name="queue_lessthan" type="text" size="30" maxlength="30"  class="campos" value="{$dt_queues.queue_lessthan}"  />
       </td>
    </tr>    
       
    <tr>
       <td class="formlabel">{$LANG.q_queue_reporthold}:</td>
       <td class="subtable">   
          <input name="queue_reporthold" type="text" size="30" maxlength="30"  class="campos" value="{$dt_queues.queue_reporthold}"  />
       </td>
    </tr>        
    <tr>
       <td class="formlabel">{$LANG.q_announce_round_seconds}:</td>
       <td class="subtable">   
          <input name="announce_round_seconds" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.announce_round_seconds|default:0}" />
          {$LANG.time_secs}
       </td>
    </tr>        
    <tr>
       <td class="formlabel">{$LANG.q_announce_holdtime}:</td>
       <td class="subtable">   
          <input name="announce_holdtime" type="text" size="30" maxlength="30"  class="campos" value="{$dt_queues.announce_holdtime}"  />
       </td>
    </tr>          
    <tr>
       <td class="formlabel">{$LANG.q_periodic_announce}:</td>
       <td class="subtable">   
          <input name="periodic_announce" type="text" size="30" maxlength="30"  class="campos" value="{$dt_queues.periodic_announce}"  /> 
       </td>
    </tr>            
   <tr>
       <td class="formlabel">{$LANG.q_eventmemberstatus}:</td>
       <td class="subtable" >   
          {html_radios name="eventmemberstatus" selected=$dt_queues.eventmemberstatus options=$OPCOES_TRUEFALSE}
       </td>
    </tr>     
    <tr>
       <td class="formlabel">{$LANG.q_periodic_announce_frequency}:</td>
       <td class="subtable">   
          <input name="periodic_announce_frequency" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.periodic_announce_frequency|default:0}"  /> 
       </td>
    </tr>      
    -->
    <tr>
       <td class="formlabel">{$LANG.q_queue_thankyou}:</td>
       <td class="subtable">
          <select name="queue_thankyou" class="campos">
             {html_options selected=$dt_queues.queue_thankyou options=$OPCOES_SONS}
          </select>
       </td>
    </tr>     
    <tr>
       <td class="formlabel">{$LANG.q_announce_frequency}:</td>
       <td class="subtable">   
          <input name="announce_frequency" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.announce_frequency|default:0}" />
          {$LANG.time_secs}
       </td>
    </tr>        
    <tr>
       <td class="formlabel">{$LANG.q_retry}:</td>
       <td class="subtable">   
          <input name="retry" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.retry|default:0}" /> 
          {$LANG.time_secs}
       </td>
    </tr>          
    <tr>
       <td class="formlabel">{$LANG.q_wrapuptime}:</td>
       <td class="subtable">   
          <input name="wrapuptime" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.wrapuptime|default:0}" /> 
          {$LANG.time_secs}
       </td>
    </tr>            
    <tr>
       <td class="formlabel">{$LANG.q_maxlen}:</td>
       <td class="subtable">   
          <input name="maxlen" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.maxlen|default:0}"  />
       </td>
    </tr>                
    <tr>
       <td class="formlabel">{$LANG.q_servicelevel}:</td>
       <td class="subtable">   
          <input name="servicelevel" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.servicelevel|default:0}"  /> 
          {$LANG.time_secs}
       </td>
    </tr>                
    <tr>
       <td class="formlabel">{$LANG.q_strategy}:</td>
       <td class="subtable" >   
          <select name="strategy" class="campos">
             {html_options selected=$dt_queues.strategy options=$OPCOES_STRATEGY}
          </select>
       </td>
    </tr>      
    <tr>
       <td class="formlabel">{$LANG.q_joinempty}:</td>
       <td class="subtable" >   
          {html_radios name="joinempty" selected=$dt_queues.joinempty options=$OPCOES_JOINEMPTY}
       </td>
    </tr>     
    <tr>
       <td class="formlabel">{$LANG.q_leavewhenempty}:</td>
       <td class="subtable">  
          {html_radios name="leavewhenempty" selected=$dt_queues.leavewhenempty options=$OPCOES_TRUEFALSE}
       </td>
    </tr>      
        
    <tr>
       <td class="formlabel">{$LANG.q_reportholdtime}:</td>
       <td class="subtable" >   
          {html_radios name="reportholdtime" selected=$dt_queues.reportholdtime options=$OPCOES_TRUEFALSE}
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.q_memberdelay}:</td>
       <td class="subtable">   
          <input name="memberdelay" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.memberdelay|default:0}"  /> 
          {$LANG.time_secs}
       </td>
    </tr>      
    <tr>
       <td class="formlabel">{$LANG.q_weight}:</td>
       <td class="subtable">   
          <input name="weight" type="text" size="3" maxlength="3"  class="campos" value="{$dt_queues.weight|default:0}"  />
       </td>
    </tr>
    <tr>
       <td colspan="2">
           <div id="titulo">
             {$LANG.queues_alert}
           </div>
       </td>
    </tr>

    <tr>
        <td colspan="3" class="tb_tit2">
            Envio de alerta por E-mail
        </td>
    </tr>

    <tr>

        <td colspan="3">
            <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="formlabel">
                        Emails: (separados por vírgula)
                        {$alert_email.ativo}
                        <input type="hidden" name="a_email_ativo" id="a_email_ativo" value="{$alert_email.ativo}" />
                    </td>
                    <td class="subtable">
                       <input type="text" name="a_email_emails" id="a_email_emails" class="campos" value="{$alert_email.destino}" />
                    </td>
                    <td class="subtable"> <span style="float:right;" id="a_email_" class="{if $alert_email.ativo == 1} regra1 {else} regra0 {/if}" onclick="change_alert('a_email_')"> </span> </td>
                </tr>
                <tr>
                    <td class="formlabel">
                        Número de ligações em espera na Fila para emitir alerta
                    </td>
                    <td class="subtable">
                       <input type="text" value="{$alert_email.sla}"  name="a_email_sla" id="a_email_sla" class="campos" size="3" maxlength="3" />
                    </td>
                    
                </tr>
                <tr>
                    <td class="formlabel">
                        Tempo máximo de espera na Fila para emitir alerta
                    </td>
                    <td class="subtable">
                        <input type="text" value="{$alert_email.tme}"  name="a_email_tme" id="a_email_tme" class="campos" size="3" maxlength="3" />
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="3" class="tb_tit2">
            Envio de alerta Sonoro
        </td>
    </tr>


    <tr>
        <td colspan="2">
            <table cellspacing="0" cellpadding="0" border="0">                
                <tr>
                    <td class="formlabel">
                        Número de ligações em espera na Fila para emtir alerta
                        <input type="hidden" name="a_sonoro_ativo" id="a_sonoro_ativo" value="{$alert_sonoro.ativo}" />
                    </td>
                    <td class="subtable">
                       <input type="text"  value="{$alert_sonoro.sla}" name="a_sonoro_sla" id="a_sonoro_sla" class="campos" size="3" maxlength="3"/>
                    </td>
                    <td class="subtable"> <span style="float:right;" id="a_sonoro_" class="{if $alert_sonoro.ativo == 1} regra1 {else} regra0 {/if}" onclick="change_alert('a_sonoro_')"> </span> </td>
                </tr>
                <tr>
                    <td class="formlabel">
                        Tempo máximo de espera na Fila para emitir alerta
                    </td>
                    <td class="subtable">
                       <input type="text" value="{$alert_sonoro.tme}" name="a_sonoro_tme" id="a_sonoro_tme" class="campos" size="3" maxlength="3"/>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="3" class="tb_tit2">
            Envio de alertas Visuais
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table cellspacing="0" cellpadding="0" border="0">

                <tr>
                    <td class="formlabel">
                        Número de ligações em espera na Fila para emitir alerta
                        <input type="hidden" name="a_visual_ativo" id="a_visual_ativo" value="{$alert_visual.ativo}" />
                    </td>
                    <td class="subtable">
                       <input type="text" value="{$alert_visual.sla}" name="a_visual_sla" id="a_visual_sla"  class="campos" size="3" maxlength="3"/>
                    </td>
                    <td class="subtable"> <span style="float:right;" id="a_visual_" onclick="change_alert('a_visual_')" class="{if $alert_visual.ativo == 1} regra1 {else} regra0 {/if}"> </span> </td>
                </tr>
                <tr>
                    <td class="formlabel">
                        Tempo máximo de espera na Fila para emitir alerta
                    </td>
                    <td class="subtable">
                       <input type="text"  value="{$alert_visual.tme}" name="a_visual_tme" id="a_visual_tme"  class="campos" size="3" maxlength="3"/>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
<!--
    <tr>
        <td colspan="3" class="tb_tit2">
            Envio de alerta por SMS
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table cellspacing="0" cellpadding="0" border="0">
                
                <tr>
                    <td class="formlabel">
                        Celulares: (separados por vírgula)
                        <input type="hidden" name="a_sms_ativo" id="a_sms_ativo" value="{$alert_sms.ativo}" />
                    </td>
                    <td class="subtable">
                       <input type="text"  value="{$alert_sms.destino}" name="a_sms_celular" id="a_sms_celular"  class="campos" />
                    </td>
                    <td class="subtable"> <span style="float:right;" id="a_sms_" onclick="change_alert('a_sms_')" class="{if $alert_sms.ativo == 1} regra1 {else} regra0 {/if}"> </span> </td>
                </tr>                
                
                <tr>
                    <td class="formlabel">
                        Número de ligações em espera na Fila para emitir alerta
                    </td>
                    <td class="subtable">
                       <input type="text" value="{$alert_sms.sla}"  name="a_sms_sla" id="a_sms_sla"  class="campos" size="3" maxlength="3"/>
                    </td>
                </tr>

                <tr>
                    <td class="formlabel">
                        Tempo máximo de espera na Fila para emitir alerta
                    </td>
                    <td class="subtable">
                       <input type="text" value="{$alert_sms.tme}"  name="a_sms_tme" id="a_sms_tme"  class="campos" size="3" maxlength="3"/>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

-->
    <!--
    <tr>  
       <td class="formlabel">{$LANG.maxcallqueue}</td>
       <td class="subtable">
          <input class="campos" type="text" size="2" name="max_call_queue"  value="{$dt_queues.max_call_queue|default:0}" />
         &nbsp;&nbsp;&nbsp;{$LANG.zerodisable}
       </td>
    </tr>
    <tr>
       <td class="formlabel">{$LANG.maxtimecall}</td>
       <td class="subtable">
          <input class="campos" type="text" size="2" name="max_time_call"  value="{$dt_queues.max_time_call|default:0}"  />
         &nbsp;&nbsp;&nbsp;{$LANG.zerodisable}
       </td>
    </tr>       
    <tr>
       <td class="formlabel">{$LANG.alert_mail}</td>
       <td class="subtable">
          <input class="campos" type="text" size="60" maxlength="80" name="alert_mail"  value="{$dt_queues.alert_mail}"  />
       </td>
    </tr>          
    <tr>
       <td class="subtable" colspan="2" ><hr /></td>
    </tr>

    -->
    <tr>
       <td colspan="2" class="subtable" align="center" height="32px" valign="top">
          <input class="button" type="submit" id="gravar" value="{$LANG.save}">
          <div class="buttonEnding"></div>
          &nbsp;&nbsp;&nbsp;
          <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_queues.php'" />
          <div class="buttonEnding"></div>      
       </td>
    </tr>
 </form>
 </table>
 <span id=dummyspan></span>
 { include file="rodape.tpl }

 <script language="javascript" type="text/javascript">

 {literal}
  document.forms[0].elements[0].focus() ;
 /*---------------------------------------------------------------------------
  * Funcoes JAVA de validacao do Formulario
  * --------------------------------------------------------------------------*/
  function valida_formulario() {
     var mensagem="{$LANG.msg_errors}";
     var erro=false ;

     var name =  $('name').value;

     if (name.length == 0 ) {
        mensagem += "\n - {$LANG.msg_thefield} '{$LANG.name}' {$LANG.msg_notblank}";
        erro=true ;
     }
     var iChars = "!@#$%^&*()+=-[]\\\';,./{}|\":<>?~_ ";

     for (var i = 0; i < name.length; i++) {
          if (iChars.indexOf(name.charAt(i)) != -1) {
             mensagem="A fila possue caracteres inválidos no nome, verifique."
             erro=true;
          }
     }
     if (erro) {
        alert(mensagem);
     }
     return erro ;
  }

  function DHTMLSound(surl) {
     var som='{$SOUNDS_PATH}'+document.getElementById(surl).value ;
     document.getElementById('dummyspan').innerHTML="<embed src='"+som+"' hidden=true autostart=true loop=false>";
  }

 function change_alert(par) {

    if($(par).hasClassName('regra0')) {
        $(par).removeClassName('regra0');
        $(par).addClassName('regra1');
        $(par + 'ativo').value = 1;

    }else{
        $(par).removeClassName('regra1');
        $(par).addClassName('regra0');
        $(par + 'ativo').value = 0;
    }

 }

 {/literal}
 </script>
