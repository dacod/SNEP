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
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="20%">Cadastrada em</td>
         <td class="cen" width="20%">{$LANG.valid_date}</td>
         <td class="dir">{$LANG.vlrbase_fix}</td>
         <td class="dir">{$LANG.vlrbase_cel}</td>
         <td class="cen" width="10%">{$LANG.actions}</td>
      </tr>
   </thead>
   {foreach name=valores from=$dt_valores key=key item=item}
      {assign var="indice" value=$smarty.foreach.valores.total}
      {assign var="data_atual" value=strtotime("now()")}
      <input type="hidden" name="data[{$key}]" id="data" value="{$item.data}" />
      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$item.data|date_format:'%d/%m/%Y %T'}</td>
         <td class="cen">{$item.data|date_format:'%d/%m/%Y'}</td>
         <td class="dir">
            <input type="text" name="vfix[{$key}]" id="vfix" value="{$item.vfix|string_format:'%.2f'}"  class="campos" style="text-align:right;"  size="8"  onChange="this.form.elements['action[{$key}]'].checked=true;;" />
         </td>
         <td class="dir">
            <input type="text" name="vcel[{$key}]" id="vcel" value="{$item.vcel|string_format:'%.2f'}" class="campos" style="text-align:right;" size="8" onChange="this.form.elements['action[{$key}]'].checked=true;;"/>
         </td>
         <td class="cen">
             <input class="campos" type="checkbox" name="action[{$key}]" value="{$key}" />
             {$LANG.change}
         </td>         
      </tr>      
   {/foreach}
   <tr>
      <td colspan="6" height="30">
         <span class="links_include"">{$LANG.newvalue_tarifa}</span>
      </td>
   </tr>
   <!-- Nova Linha para Incluir novo registro -->
   <input type="hidden" name="data[{$indice}]" id="data" value="{$data_atual|date_format:'%Y-%m-%d %T'}" />
   <tr bgcolor="#FFF9C4">
       <td class="cen">{$data_atual|date_format:'%d/%m/%Y %T'}</td>
      <td class="cen">{$data_atual|date_format:'%d/%m/%Y'}</td>
      <td class="dir">
         <input type="text" name="vfix[{$indice}]" id="vfix" class="campos" style="text-align:right;"  size="8" value="0"   onChange="this.form.elements['action[{$indice}]'].checked=true;;" />
      </td>
      <td class="dir">
         <input type="text" name="vcel[{$indice}]" id="vcel" class="campos" style="text-align:right;" size="8" value="0"  onChange="this.form.elements['action[{$indice}]'].checked=true;;"/>
      </td>
      <td class="cen">
          <input class="campos" type="checkbox" name="action[{$indice}]" value="{$indice}" />
          <strong>{$LANG.include}</strong>
      </td>
   </tr>
</table>
