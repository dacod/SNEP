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
 {config_load file="../includes/setup.conf" section="ambiente"}
 {config_load file="../includes/setup.conf" section="canais"}
 {config_load file="../includes/setup.conf" section="troncos"}
 {config_load file="../includes/setup.conf" section="usuarios"}
 {config_load file="../includes/setup.conf" section="system"}
 <table align="center">
    <form name="ura" action="{$smarty.server.SCRIPT_NAME}" method="post" enctype="multipart/form-data" >
    <thead>
       <tr>
          <td class="esq" width="55%">{$LANG.descparam}</td>
          <td class="esq" width="40%">{$LANG.vlrparam}</td>
          <td class="esq" width="5%">{$LANG.change}</td>
       </tr>
    </thead>
    <tr>
       <td class="esq">{$LANG.empname}</td>
       <td class="esq">
          <input class="campos" type="text" size="40" name="new_emp_nome" value="{#emp_nome#}" onchange="this.form.elements['alterar[1]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[1]" value="emp_nome" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.linelimit}</td>
       <td class="esq">
          <input class="campos" type="text" size="2" name="new_linelimit" value="{#linelimit#}"  onchange="this.form.elements['alterar[2]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[2]" value="linelimit" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.ip_sock}</td>
       <td class="esq">
          <input class="campos" type="text" size="15" name="new_ip_sock" value="{#ip_sock#}" onchange="this.form.elements['alterar[4]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[4]" value="ip_sock" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.user_sock}</td>
       <td class="esq">
          <input class="campos" type="text" size="30" name="new_user_sock" value="{#user_sock#}" onchange="this.form.elements['alterar[5]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[5]" value="user_sock" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.pass_sock}</td>
       <td class="esq">
          <input class="campos" type="text" size="20" name="new_pass_sock" value="{#pass_sock#}" onchange="this.form.elements['alterar[6]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[6]" value="pass_sock" />
       </td>
    </tr>
     <tr>
        <td class="esq">{$LANG.typetime}</td>
        <td class="esq">
          <input type="radio" name="new_typetime" value="M" {if #typetime# == "M"} checked {/if} onchange="this.form.elements['alterar[12]'].checked=true;" />{$LANG.minutes}
          <input type="radio" name="new_typetime" value="S" {if #typetime# == "S"} checked {/if} onchange="this.form.elements['alterar[12]'].checked=true;" />{$LANG.seconds}
        </td>
        <td class="cen">
           <input class="campos" type="checkbox" name="alterar[12]" value="typetime" />
        </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.dstexceptions}</td>
       <td class="esq">
          <input class="campos" type="text" size="60"            name="new_dst_exceptions"  value="{#dst_exceptions#}"            onchange="this.form.elements['alterar[14]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[14]"  value="dst_exceptions" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.conference_app}</td>
       <td class="esq">
          <input type="radio" name="new_conference_app" value="M" {if #conference_app# == "M"} checked {/if} onchange="this.form.elements['alterar[15]'].checked=true;" />Meetme
          <input type="radio" name="new_conference_app" value="C" {if #conference_app# == "C"} checked {/if} onchange="this.form.elements['alterar[15]'].checked=true;" />Conference
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[15]"  value="conference_app" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.params_debug}</td>
       <td class="esq">
          <input type="radio" id="debug_yes" name="new_debug" value="yes" {if #debug# == true} checked {/if} onchange="this.form.elements['alterar[110]'].checked=true;" /><label for="debug_yes">{$LANG.yes}</label>
          <input type="radio" id="debug_no" name="new_debug" value="no" {if #debug# == false} checked {/if} onchange="this.form.elements['alterar[110]'].checked=true;" /><label for="debug_no">{$LANG.no}</label>
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[110]"  value="debug" />
       </td>
    </tr>

    <tr>
        <td colspan="3" class="tb_tit2"><strong>{$LANG.subsis_record}</strong></td>
    </tr>
    <tr>
       <td class="esq">{$LANG.record_app}</td>
       <td class="esq">
          <input type="radio" name="new_record_app" value="monitor" {if $record_app == "monitor"} checked {/if} onchange="this.form.elements['alterar[99]'].checked=true;" />Monitor
          <input type="radio" name="new_record_app" value="mixmonitor" {if $record_app == "mixmonitor"} checked {/if} onchange="this.form.elements['alterar[99]'].checked=true;" />MixMonitor
          <input type="radio" name="new_record_app" value="krecord" {if $record_app == "krecord"} checked {/if} onchange="this.form.elements['alterar[99]'].checked=true;" />KRecord
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[99]"  value="record_app" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.voz_flags}</td>
       <td class="esq">
          <input class="campos" type="text" size="30" name="new_record_flags" value="{$record_flags}" onchange="this.form.elements['alterar[10]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[10]" value="record_flags" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.path_voz}</td>
       <td class="esq">
          <input class="campos" type="text" size="30" name="new_path_voz" value="{#path_voz#}"                        onchange="this.form.elements['alterar[8]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[8]" value="path_voz" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.path_voz_bkp}</td>
       <td class="esq">
          <input class="campos" type="text" size="30" name="new_path_voz_bkp" value="{#path_voz_bkp#}"         onchange="this.form.elements['alterar[9]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[9]" value="path_voz_bkp" />
       </td>
    </tr>
    
    <tr>
        <td colspan="3" class="tb_tit2"><strong>{$LANG.config_ramais}</strong></td>
    </tr>
    
    <tr>
       <td class="esq">
          {$LANG.configpeers}
       </td>
       <td class="esq">
          <input class="campos" type="text" size="30" name="new_peers_range" value="{#peers_range#}" onchange="this.form.elements['alterar[23]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[23]"  value="peers_range" />
       </td>
    </tr>

    <tr>
        <td colspan="3" class="tb_tit2"><strong>{$LANG.subsis_gestao}</strong></td>
    </tr>
    <tr>
       <td class="esq">{$LANG.converttogsm}</td>
       <td class="esq">
           <input type="radio" name="new_convert_gsm" value="True" {if #convert_gsm#} checked {/if} onchange="this.form.elements['alterar[13]'].checked=true;" />{$LANG.yes}
          <input type="radio" name="new_convert_gsm" value="False" {if !#convert_gsm#} checked {/if} onchange="this.form.elements['alterar[13]'].checked=true;" />{$LANG.no}
      </td>
      <td class="cen">
         <input class="campos" type="checkbox" name="alterar[13]" value="convert_gsm" />
      </td>
    </tr>  
    
    <tr>
       <td class="esq">{$LANG.agents_range}</td>
       <td class="esq">
          <input class="campos" type="text" size="30" name="new_agents" value="{#agents#}" onchange="this.form.elements['alterar[28]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[28]" value="agents" />
       </td>
    </tr>
    <tr>
        <td colspan="3" class="tb_tit2"><strong>{$LANG.queues}</strong></td>
    </tr>
    <tr>
       <td class="esq">
          &nbsp;&nbsp;<img src="../imagens/corner.gif" alt="" border="0" />{$LANG.timerefresh}
       </td>
       <td class="esq">
          <input class="campos" type="text" size="2" name="new_tempo_refresh" value="{#tempo_refresh#}" onchange="this.form.elements['alterar[33]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[33]" value="tempo_refresh" />
       </td>
    </tr>
    <tr>  
       <td class="esq">
          &nbsp;&nbsp;<img src="../imagens/corner.gif" alt="" border="0" />{$LANG.maxcallqueue}
       </td>
       <td class="esq">
          <input class="campos" type="text" size="2" name="new_max_call_queue" value="{#max_call_queue#}" onchange="this.form.elements['alterar[34]'].checked=true;" />
         &nbsp;&nbsp;&nbsp;{$LANG.zerodisable}
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[34]" value="max_call_queue"></input>
       </td>
    </tr>
    <tr>
       <td class="esq">
          &nbsp;&nbsp;<img src="../imagens/corner.gif" alt="" border="0" />{$LANG.maxtimecall}
       </td>
       <td class="esq">
          <input class="campos" type="text" size="2" name="new_max_time_call" value="{#max_time_call#}" onchange="this.form.elements['alterar[35]'].checked=true;" />
         &nbsp;&nbsp;&nbsp;{$LANG.zerodisable}
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[35]" value="max_time_call" />
       </td>
    </tr>

    <tr>
        <td colspan="3" class="tb_tit2"><strong>{$LANG.menu_troncos}</strong></td>
    </tr>
    <tr>
       <td class="esq">
          &nbsp;&nbsp;<img src="../imagens/corner.gif" alt="" border="0" />{$LANG.trunks_cq_value}
       </td>
       <td class="esq">
          <input class="campos" type="text" size="4" name="new_valor_controle_qualidade" value="{#valor_controle_qualidade#}" onchange="this.form.elements['alterar[55]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[55]" value="valor_controle_qualidade" />
       </td>
    </tr>

    <tr>
       <td colspan="3" class="tb_tit2"><strong>{$LANG.subsis_oppanel}</strong></td>
    </tr>
    <tr>
       <td class="esq">{$LANG.abapainel}&nbsp;1&nbsp;{$LANG.depende}index1.php)</td>
       <td class="esq">
          <input class="campos" type="text" size="20" name="new_menu_status_1" value="{#menu_status_1#}"               onchange="this.form.elements['alterar[30]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[30]" value="menu_status_1" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.abapainel}&nbsp;2&nbsp;{$LANG.depende}index2.php)</td>
       <td class="esq">
          <input class="campos" type="text" size="20" name="new_menu_status_2" value="{#menu_status_2#}"              onchange="this.form.elements['alterar[31]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[31]" value="menu_status_2" />
       </td>
    </tr>
    <tr>
       <td class="esq">{$LANG.abapainel}&nbsp;3&nbsp;{$LANG.depende}index3.php)</td>
       <td class="esq">
          <input class="campos" type="text" size="20" name="new_menu_status_3" value="{#menu_status_3#}" onchange="this.form.elements['alterar[32]'].checked=true;" />
       </td>
       <td class="cen">
          <input class="campos" type="checkbox" name="alterar[32]" value="menu_status_3" />
       </td>
   </tr>

   <tr class="cen">
       <td height="40" colspan="3" valign="middle">
          <input type="submit" class="button" name="parametros" value="{$LANG.save}" />
           <div class="buttonEnding"></div>
        </td>
    </tr>
    </form>
 </table>
 { include file="rodape.tpl }
 <script>document.forms[0].elements[0].focus() ;</script>