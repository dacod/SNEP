{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: database_show.tpl - Template da rotina database_show.tpl
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}

{config_load file="../includes/setup.conf" section="cores"}
<table cellpadding="0" cellspacing="0" border="0" align="center">
   <thead>
      <tr>
         <td>{$LANG.ramais_loggeds|upper}</td>
         <td>{$LANG.queues|upper}</td>
      </tr>
   </thead>
   <tr>
     <!-- Ramais -->
     <td valign="top">
        <table>
           <thead>
              <tr>
                 <td class="esq">{$LANG.ramal}</td>
                 <td class="cen">{$LANG.type}</td>
                 <td class="esq">{$LANG.ip}</td>
                 <td class="esq">{$LANG.delay}</td>
                 <td class="esq">{$LANG.codecs}</td>
              </tr>
           </thead>
           {foreach name=ramais from=$RAMAIS key=curr_key item=curr_item}
              <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
                 <td class="esq">{$curr_item.ramal}</td>
                 <td class="cen">{$curr_item.tipo}</td>
                 <td class="esq">{$curr_item.ip}</td>
                 <td class="esq">{$curr_item.delay}</td>
                 <td class="esq">{$curr_item.codec}</td>
              </tr>
           {/foreach}
        </table>
     </td>

     <td valign="top">
        <table>
           <thead>
              <tr>
                 <td class="esq">{$LANG.row}</td>
                 <td class="esq">{$LANG.queue_members}</td>
                 <td class="esq">{$LANG.state}</td>
              </tr>
           </thead>
           {foreach name=agentes from=$FILAS key=curr_key item=curr_item}
              <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
                 <td class="esq" style="vertical-align: top;">{$curr_item.fila}</td>
                 <td class="esq">{$curr_item.agent|replace:",":"<br/>"}</td>
                 <td class="esq">{$curr_item.status|replace:",":"<br/>"}</td>
              </tr>
           {/foreach}
        </table>
        <br />
        <table>
           <thead>
              <tr>
                 <td class="esq">{$LANG.licenses}</td>
                 <td class="esq">{$LANG.encode}</td>
                 <td class="esq">{$LANG.decode}</td>
              </tr>
           </thead>
           {if $CODECS.1 != 'No'}
              <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
                 <td class="esq" style="vertical-align: top;">{$CODECS.0}</td>
                 <td class="esq">{$CODECS.1}</td>
                 <td class="esq">{$CODECS.2}</td>
              </tr>
           {/if}
        </table>
     </td>
   </tr>   
</table>
{ include file="rodape.tpl" }