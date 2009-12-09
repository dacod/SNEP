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
{ include file="cabecalho.tpl" }   
<table >
   <tr>
      <td style="width:50%" align="left" valign="top" class="subtable">
         <table class="subtable2">
            <thead>
               <tr>
                  <td colspan="2">{$LANG.statusserver}
                  </td>
               </tr>
            </thead>
            <tr>
               <td class="campos"><strong>{$LANG.distrib}</strong></td>
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
               <td class="campos"><strong>{$LANG.timelife}</strong></td>
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
         </table>
         <br />
         <table class="subtable2">
            <thead>
               <tr>
                  <td colspan="2" >
                     {$LANG.statusasterisk}
                  </td>
               </tr>
            </thead>
            <tr>
               <td class="campos"><strong>{$LANG.ramais}&nbsp;SIP</strong></td>
               <td class="campos">{$SIS.sip_peers}</td>
            </tr>
            <tr>
               <td class="campos"><strong>{$LANG.channels}&nbsp;SIP</strong></td>
               <td class="campos">{$SIS.sip_channels}</td>
            </tr>
            <tr>
               <td class="campos"><strong>{$LANG.ramais}&nbsp;IAX2</strong></td>
               <td class="campos">{$SIS.iax2_peers}</td>
            </tr>
            <tr>
               <td class="campos"><strong>{$LANG.agents}</strong></td>
               <td class="campos">{$SIS.agents}</td>
            </tr>
         </table>
         <br />
         <table class="subtable2">
            <thead>
               <tr>
                  <td colspan="2">
                     {$LANG.statussnep}
                  </td>
               </tr>
            </thead>
            <tr>
               <td class="campos"><strong>{$LANG.tarifas_mod}</strong></td>
               <td class="campos">
                  {if $SIS.tarifas_mod }
                     {$LANG.install} ( {$SIS.tarifas_vers} )
                  {else}
                     {$LANG.notinstall}
                  {/if}
                  &nbsp;&nbsp;//&nbsp;&nbsp;
                  {if $SIS.tarifas_inst} 
                     {$LANG.enabled}
                  {else}
                     {$LANG.notenabled}
                  {/if}
               </td>
            </tr>    
                  <tr>
               <td class="campos"><strong>{$LANG.gestao_mod}</strong></td>
               <td class="campos">
                  {if $SIS.gestao_mod }
                     {$LANG.install} ( {$SIS.gestao_vers} )
                  {else}
                     {$LANG.notinstall}
                  {/if}
                  &nbsp;&nbsp;//&nbsp;&nbsp;
                  {if $SIS.gestao_inst} 
                     {$LANG.enabled}
                  {else}
                     {$LANG.notenabled}
                  {/if}
               </td>
            </tr>                   
            <tr>
               <td class="campos"><strong>{$LANG.panel_mod}</strong></td>
               <td class="campos">
                  {if $SIS.panel_mod }
                     {$LANG.install} ( {$SIS.panel_vers} )
                  {else}
                     {$LANG.notinstall}
                  {/if}
                  &nbsp;&nbsp;//&nbsp;&nbsp;
                  {if $SIS.panel_inst} 
                     {$LANG.enabled}
                  {else}
                     {$LANG.notenabled}
                  {/if}
               </td>
            </tr>

            <tr>
               <td class="campos"><strong>{$LANG.arqvoz}</strong></td>
               <td class="campos">
                  {$LANG.numfiles}: <strong>{$SIS.num_arqvoz|default:"0"}</strong>
                  &nbsp;&nbsp;
                  {$LANG.spaceused}: <strong>{$SIS.spc_arqvoz|default:"0"}</strong>
               </td>
            </tr>
         </table>
      </td>
      <td  style="width:50%" valign="top" class="subtable">
         <table  class="subtable2">
            <thead>
               <tr>
                  <td colspan="4">{$LANG.statusmemory}</td>
               </tr>
            </thead>
            <tr>
               <td class="campos"><strong>{$LANG.type}</strong></td>
               <td class="campos"><strong>{$LANG.total}</strong></td>
               <td class="campos"><strong>{$LANG.free}</strong></td>
               <td class="campos"><strong>{$LANG.percent} {$LANG.used}</strong></td>
            </tr>
            <tr>
               <td class="campos"><strong>{$LANG.phismem}</strong></td>
               <td class="campos">{$SIS.memory.ram.total/1024|string_format:"%.2f"}</td>
               <td class="campos">{$SIS.memory.ram.free/1024|string_format:"%.2f"}</td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.ram.percent}</td>
            </tr>
            <tr>
               <td class="campos">&nbsp;&nbsp;-{$LANG.kern_app}</td>
               <td class="campos">{$SIS.memory.ram.app/1024|string_format:"%.2f"}</td>
               <td class="campos"></td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.ram.app_percent} </td>
            </tr>
            <tr>
               <td class="campos">&nbsp;&nbsp;-{$LANG.buffers}</td>
               <td class="campos"></td>
               <td class="campos"></td>
               <td  class="campos"> {bargraph->linha a=$SIS.memory.ram.buffers_percent}</td>
            </tr>
            <tr>
               <td class="campos">&nbsp;&nbsp;-{$LANG.cached}</td>
               <td class="campos"> {$SIS.memory.ram.cached/1024|string_format:"%.2f"}</td>
               <td class="campos"></td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.ram.cached_percent} </td>
            </tr>                            
            <tr>
               <td class="campos"><strong>{$LANG.swap}</strong></td>
               <td class="campos"> {$SIS.memory.swap.total/1024|string_format:"%.2f"} </td>
               <td class="campos"> {$SIS.memory.swap.free/1024|string_format:"%.2f"} </td>
               <td class="campos"> {bargraph->linha a=$SIS.memory.swap.percent} </td>
            </tr>                     
         </table>
         <br />
         <table  class="subtable2">
            <thead>
               <tr>
                  <td colspan="4">{$LANG.statusdisco}</td>
               </tr>
            </thead>
            <tr>
               <td class="campos"><strong>{$LANG.partition}</strong></td>
               <td class="campos"><strong>{$LANG.size}</strong></td>
               <td class="campos"><strong>{$LANG.free}</strong></td>
               <td class="campos" width="50%"><strong>% {$LANG.used}</strong></td>
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

   
