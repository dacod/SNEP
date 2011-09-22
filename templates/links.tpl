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
 {config_load file="../includes/setup.conf" section="cores"}
 <form name="view" method="post" id="view">
 <table align="center">
   {foreach name=boards from=$DADOS key=board_key item=board_item}    
   {assign var="tit" value=$board_key|replace:'B':''}
    <tr>
      <td valign="top">
         <input type="hidden"  size="2" class="campos" id="status[{$board_key}]" name="{$board_key}" value="no" />
         <table>
            <tr>
               <td colspan="2" class="boards_khomp">
                  {$LANG.board}: {$board_key} {if $GSM.$tit == "yes"} {$LANG.gsm_string} {/if}
               </td>
            </tr>
            <tr>
               <td class="tb_tit2">{$LANG.status_links}</td>
               <td class="tb_tit2">{$LANG.status_channels}</td>
            </tr>
            <tr>
               <td class="links_khomp">
                  {assign var="channels" value="no"}
                  {assign var="count" value = 0}
                  {foreach name=links from=$DADOS[$board_key] key=link_key  item=link_item}
                     {$LANG.link} {$link_key}: {$link_item}
                     <br />
                  {/foreach}
               </td>
               <td class="links_sint" width="50%">
                  
                  {assign var="sintetic" value="no"}
                    
                  {assign var="cor_status" value=$canal_item.asterisk}
                  
                  {foreach name=sintetic from=$SINTETIC[$board_key] key=sint_key  item=sint_item}
                     {$STATUS_SINTETIC.$sint_key}:<strong> {$sint_item}</strong>
                     <br />
                  {/foreach}
                  
               </td>
               
            </tr>        
            <tr >
               <td colspan="2">
<!--              <div id="channels[{$board_key}]" style="display: none;"> -->
                  <div id="channels[{$board_key}]">
           
                  {if $TIPOREL == "yes"}  
                  <table align="center"  width="100%">
                  <tr>
                      <td><strong>{$LANG.chann}</strong></td>
                      <td><strong>{$LANG.status_ast}</strong></td>
                      <td><strong>{$LANG.status_call}</strong></td>
             

                      {if $GSM.$tit == "yes"}

                      <td><strong>{$LANG.gsm_signal}</strong></td>
                      <td><strong>{$LANG.gsm_operadora}  </strong></td>
                      
                      {/if}

                      {if $STATUS === "yes"}
                        <td><strong>{$LANG.status_chann}</strong></td>
                      {/if}

                  </tr>

                     {foreach from=$CANAIS[$board_key] key=canal_key item=canal_item} 
                      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`, `$smarty.config.COR_GRID_B`"}'>
                         <td style="width:20px;">{$canal_key}</td>
                            {assign var="cor_status" value=$canal_item.asterisk}
                         
                         <td style="width:120px;color:#fff;background: {$STATUS_CANAIS.$cor_status};">
                           <strong>{$canal_item.asterisk}</strong>
                         </td>
                            
                         <td style="width:120px;color:#fff;background: {$STATUS_CANAIS.$cor_status};">
                           {$canal_item.k_call}
                         </td>

                         {if $canal_item.k_gsm == "k_gsm"}

                         <td style="width:120px;color:#fff;background: {$STATUS_CANAIS.$cor_status};">
                           {$canal_item.k_signal}
                         </td>
                         
                         <td style="width:120px;color:#fff;background: {$STATUS_CANAIS.$cor_status};">
                           {$canal_item.k_opera}
                         </td>

                         {/if}

                         {if $STATUS === "yes"}
                         <td style="color:#fff;background: {$STATUS_CANAIS.$cor_status};">
                           {$canal_item.k_channel}
                         </td>
                         {/if}
                         

                     </tr>
                     {$count += 1}
                     {/foreach}
                  </table>
                    {/if}
                  </div>
               </td>
            </tr>
         </table>
       </td>
       </tr>
   {/foreach}
   
   
   <tr>
      <td colspan="3" align="center">
         {$LANG.updating} {#tempo_refresh#}
      </td>
   </tr>
   </form>
</table>
 { include file="rodape.tpl }
 <script language="javascript" type="text/javascript">
 function view_channels(board) {ldelim}
    var placa =  document.getElementById('status['+board+']') ;
    if (placa.value == "no") {ldelim}
       document.getElementById('channels['+board+']').style.display='block' ;
       document.getElementById('img['+board+']').src='../imagens/go-up.png'   ;
       placa.value = 'yes' ;
    {rdelim} else {ldelim}
       document.getElementById('channels['+board+']').style.display='none';
       document.getElementById('img['+board+']').src='../imagens/go-down.png'   ;
       placa.value = 'no' ;
    {rdelim}
    
 {rdelim}

 </script>
