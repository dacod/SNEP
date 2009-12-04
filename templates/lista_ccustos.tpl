{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: lista_ccustos.tpl - Lista de Centros de Custos
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- *}
 <link rel="stylesheet" href="../css/{$CSS_TEMPL}.css" type="text/css" /> 
 <table style="width: 100%">
    <thead>
      <tr>
         <td class="esq" width="5%" style="font-size: 10px">{$LANG.id}</td>
         <td class="esq" style="font-size: 10px">{$LANG.name}</td>
         <td class="cen" style="font-size: 10px">{$LANG.type}</td>
      </tr>
   </thead>
   {section name=ccustos loop=$DADOS}
   <tr>
      <td class="esq" style="font-size: 10px">{$DADOS[ccustos].codigo}</td>
      <td class="esq" style="font-size: 10px">{$DADOS[ccustos].nome}</td>
         {assign var="tipo_cc" value=$DADOS[ccustos].tipo}
      <td class="cen" style="font-size: 10px">{$TIPOS_CCUSTOS.$tipo_cc}</td>
   </tr>
   {/section}
</table>