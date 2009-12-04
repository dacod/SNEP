{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: tarifas_alterar.tpl - Alterar Valores de Tarifas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- *} 
{config_load file="../includes/setup.conf" section="cores"}
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="20%">{$LANG.date}</td>
         <td class="dir">{$LANG.vlrbase_fix}</td>
         <td class="dir">{$LANG.vlrpartida_fix}</td>
         <td class="dir">{$LANG.vlrbase_cel}</td>
         <td class="dir">{$LANG.vlrpartida_cel}</td>
         <td class="cen" width="10%">{$LANG.actions}</td>
      </tr>
   </thead>
   {foreach name=valores from=$dt_valores key=key item=item}
      {assign var="indice" value=$smarty.foreach.valores.total}
      {assign var="data_atual" value=strtotime("now()")}
      <input type="hidden" name="data[{$key}]" id="data" value="{$item.data}" />
      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$item.data_f}</td>
         <td class="dir">
            <input type="text" name="vfix[{$key}]" id="vfix" value="{$item.vfix|string_format:'%.2f'}"  class="campos" style="text-align:right;"  size="8"  onChange="this.form.elements['action[{$key}]'].checked=true;;" />
         </td>
         <td class="dir">
            <input type="text" name="vpf[{$key}]" id="vpf" value="{$item.vpf|string_format:'%.2f'}"  class="campos" style="text-align:right;"  size="8"  onChange="this.form.elements['action[{$key}]'].checked=true;;" />
         </td>
         <td class="dir">
            <input type="text" name="vcel[{$key}]" id="vcel" value="{$item.vcel|string_format:'%.2f'}" class="campos" style="text-align:right;" size="8" onChange="this.form.elements['action[{$key}]'].checked=true;;"/>
         </td>
         <td class="dir">
            <input type="text" name="vpc[{$key}]" id="vpc" value="{$item.vpc|string_format:'%.2f'}" class="campos" style="text-align:right;" size="8" onChange="this.form.elements['action[{$key}]'].checked=true;;"/>
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
      <td class="dir">
         <input type="text" name="vfix[{$indice}]" id="vfix" class="campos" style="text-align:right;"  size="8" value="0"   onChange="this.form.elements['action[{$indice}]'].checked=true;;" />
      </td>
      <td class="dir">
         <input type="text" name="vpf[{$indice}]" id="vpf" class="campos" style="text-align:right;"  size="8" value="0"   onChange="this.form.elements['action[{$indice}]'].checked=true;;" />
      </td>
      <td class="dir">
         <input type="text" name="vcel[{$indice}]" id="vcel" class="campos" style="text-align:right;" size="8" value="0"  onChange="this.form.elements['action[{$indice}]'].checked=true;;"/>
      </td>
      <td class="dir">
         <input type="text" name="vpc[{$indice}]" id="vpc" class="campos" style="text-align:right;" size="8" value="0"  onChange="this.form.elements['action[{$indice}]'].checked=true;;"/>
      </td>
      <td class="cen">
          <input class="campos" type="checkbox" name="action[{$indice}]" value="{$indice}" />
          <strong>{$LANG.include}</strong>
      </td>
   </tr>
</table>
