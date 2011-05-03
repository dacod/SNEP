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
           {if $CODECS.1}
              <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
                 <td class="esq" style="vertical-align: top;">{$CODECS.0}</td>
                 <td class="esq">{$CODECS.1}</td>
                 <td class="esq">{$CODECS.2}</td>
              </tr>
           {/if}
        </table>
        <br />
        
        <!-- Tabela de Troncos -->
        <table>
           <thead>
              <th colspan='5'>
                 Troncos SIP
              </th>
              <tr>
	        <td>Nome</td>
              	<td>{$LANG.ip}</td>
              	<td>{$LANG.status}</td>
              	<td>{$LANG.latencia}</td>
              </tr>
           </thead>
           {foreach name=TRONCOS from=$TRONCOS key=trunk_key item=trunk_val}
              <tr>
              	{foreach name=PEERS from=$trunk_val key=peer_key item=peer_val }
                 	<td class="esq" style="vertical-align: top;">{$peer_val}<br/></td>
                {/foreach}
              </tr>
           {/foreach}
        </table>
     </td>
   </tr>   
</table>
{ include file="rodape.tpl" }