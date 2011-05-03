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
{ include file="cabecalho.tpl" }   
<table >
   {if $IE_ERROR}
   <tr>
       <td colspan="2" class="error_box">
           Você está usando uma versão incompativel do Internet Explorer. Para
           obter a melhor compactibilidade com o Snep por favor atualize seu
           browser para a versão 8.0 ou mais recente do Internet Explorer.
           Opcionalmente você pode baixar e instalar gratuitamente o Mozilla
           Firefox ou Google Chrome.
       </td>
   </tr>
   {/if}
   <tr>
      <td style="width:50%" align="left" valign="top" class="subtable">
         <table class="subtable2">
            <thead>
               <tr>
                  <td colspan="2">
                      {$i18n->translate("Status do Servidor")}
                  </td>
               </tr>
            </thead>
            <tr>
               <td class="campos"><strong>{$i18n->translate("Distribuição")}</strong></td>
               <td class="campos">{$SIS.linux_vers}</td>
            </tr>
            <tr>
               <td class="campos"><strong>Kernel</strong></td>
               <td class="campos">{$SIS.linux_kernel}</td>
            </tr>
            <tr>
               <td class="campos"><strong>CPU</strong></td>
               <td class="campos">{$SIS.hardware}</td>
            </tr>
            <tr>
                <td class="campos"><strong>{$i18n->translate("Tempo ligado")}</strong></td>
               <td class="campos">{$SIS.uptime}</td>
            </tr>
            <tr>
               <td class="campos"><strong>Asterisk</strong></td>
               <td class="campos">{$SIS.ast_vers}</td>
            </tr>
            <tr>
               <td class="campos"><strong>MySQL</strong></td>
               <td class="campos">{$SIS.mysql_vers}</td>
            </tr>
            <tr>
                <td class="campos"><strong>{$i18n->translate("Arquivos de Voz")}</strong></td>
               <td class="campos">
                  {$LANG.numfiles}: <strong>{$SIS.num_arqvoz|default:"0"}</strong>
                  &nbsp;&nbsp;
                  {$LANG.spaceused}: <strong>{$SIS.spc_arqvoz|default:"0"}</strong>
               </td>
            </tr>
         </table>
         <br />
         <table class="subtable2">
            <thead>
               <tr>
                  <td colspan="2" >
                      {$i18n->translate("Status do Asterisk")}
                  </td>
               </tr>
            </thead>
            <tr>
                <td class="campos"><strong>{$i18n->translate("Ramais SIP")}</strong></td>
               <td class="campos">{$SIS.sip_peers}</td>
            </tr>
            <tr>
                <td class="campos"><strong>{$i18n->translate("Canais SIP")}</strong></td>
               <td class="campos">{$SIS.sip_channels}</td>
            </tr>
            <tr>
                <td class="campos"><strong>{$i18n->translate("Ramais IAX2")}</strong></td>
               <td class="campos">{$SIS.iax2_peers}</td>
            </tr>
         </table>
         <br />
         <table class="subtable2">
            <thead>
               <tr>
                  <td colspan="3">
                      {$i18n->translate("Módulos")}
                  </td>
               </tr>
            </thead>
            {if count($SIS.modules) > 0}
                {foreach from=$SIS.modules item=module name=module}
                <tr>
                   <td class="campos"><strong>{$module.name}</strong></td>
                   <td class="campos">{$module.version}</td>
                   <td class="campos">{$module.description}</td>
                </tr>
                {/foreach}
            {else}
            <tr>
                <td>
                    <p style="text-align: center;"><img style="vertical-align: bottom" src="../imagens/ico_info.png" />{$i18n->translate("Nenhum modulo instalado")}</p>
                </td>
            </tr>
            {/if}
         </table>
      </td>
      <td  style="width:50%" valign="top" class="subtable">
         <table  class="subtable2">
            <thead>
               <tr>
                   <td colspan="4">{$i18n->translate("Memória")} (MB)</td>
               </tr>
            </thead>
            <tr>
                <td class="campos"><strong>{$i18n->translate("Tipo")}</strong></td>
                <td class="campos"><strong>{$i18n->translate("Total")}</strong></td>
                <td class="campos"><strong>{$i18n->translate("Livre")}</strong></td>
                <td class="campos"><strong>{$i18n->translate("Usado")}</strong></td>
            </tr>
            <tr>
               <td class="campos"><strong>{$i18n->translate("Memória Física")}</strong></td>
               <td class="campos">{$SIS.memory.ram.total/1024|string_format:"%.2f"}</td>
               <td class="campos">{$SIS.memory.ram.free/1024|string_format:"%.2f"}</td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.ram.percent}</td>
            </tr>
            <tr>
                <td class="campos">&nbsp;&nbsp;-{$i18n->translate("Kernel + Aplic.")}</td>
               <td class="campos">{$SIS.memory.ram.app/1024|string_format:"%.2f"}</td>
               <td class="campos"></td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.ram.app_percent} </td>
            </tr>
            <tr>
                <td class="campos">&nbsp;&nbsp;-{$i18n->translate("Buffer")}</td>
               <td class="campos"></td>
               <td class="campos"></td>
               <td  class="campos"> {bargraph->linha a=$SIS.memory.ram.buffers_percent}</td>
            </tr>
            <tr>
                <td class="campos">&nbsp;&nbsp;-{$i18n->translate("Em cache")}</td>
               <td class="campos"> {$SIS.memory.ram.cached/1024|string_format:"%.2f"}</td>
               <td class="campos"></td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.ram.cached_percent} </td>
            </tr>                            
            <tr>
                <td class="campos"><strong>{$i18n->translate("Memória de Troca")}</strong></td>
               <td class="campos"> {$SIS.memory.swap.total/1024|string_format:"%.2f"} </td>
               <td class="campos"> {$SIS.memory.swap.free/1024|string_format:"%.2f"} </td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.swap.percent} </td>
            </tr>                     
         </table>
         <br />
         <table  class="subtable2">
            <thead>
               <tr>
                   <td colspan="4">{$i18n->translate("Espaço em Disco")} (GB)</td>
               </tr>
            </thead>
            <tr>
                <td class="campos"><strong>{$i18n->translate("Partição")}</strong></td>
                <td class="campos"><strong>{$i18n->translate("Tamanho")}</strong></td>
                <td class="campos"><strong>{$i18n->translate("Livre")}</strong></td>
                <td class="campos" width="50%"><strong>{$i18n->translate("Usado")}</strong></td>
            </tr> 
            {foreach name=discos key=chave item=valor from=$SIS.space}
               <tr>
                  <td class="campos"><strong>{$valor.mount_point}</strong></td>
                  <td class="campos"> {$valor.size/1048576|string_format:"%.2f"} </td>
                  <td class="campos"> {$valor.free/1048576|string_format:"%.2f"} </td>
                  <td class="campos"> {bargraph->linha a=$valor.percent} </td>
               </tr>
            {/foreach}
         </table>
      </td>    
   </tr>
</table>
{ include file="rodape.tpl" }

   
