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