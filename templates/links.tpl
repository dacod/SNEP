{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: links.tpl - Links de Placas Khomp
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}
 {config_load file="../includes/setup.conf" section="cores"}
 <form name="view" method="post" id="view">
 <table align="center">
   <tr>
   {foreach name=boards from=$DADOS key=board_key item=board_item}            
      <td width="{$COLS}%" valign="top">
         <input type="hidden"  size="2" class="campos" id="status[{$board_key}]" name="{$board_key}" value="no" />
         <table>
            <tr>
               <td colspan="2" class="boards_khomp">
                  {$LANG.board}: {$board_key}
               </td>
            </tr>
            <tr>
               <td class="tb_tit2">{$LANG.status_links}</td>
               <td class="tb_tit2">{$LANG.status_channels}</td>
            </tr>
            <tr>
               <td class="links_khomp">
                  {assign var="channels" value="no"}
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
                         {if $STATUS === "yes"}
                         <td style="color:#fff;background: {$STATUS_CANAIS.$cor_status};">
                           {$canal_item.k_channel}
                         </td>
                         {/if}
                     </tr>
                     {/foreach}
                  </table>
                    {/if}
                  </div>
               </td>
            </tr>
         </table>
       </td>
   {/foreach}
   </tr>
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
