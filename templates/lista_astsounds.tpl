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
 {config_load file="../includes/setup.conf" section="cores"}
 <link rel="stylesheet" href="../css/{$CSS_TEMPL}.css" type="text/css" /> 
 <table style="width: 100%">
    {foreach name=files item=arquivo from=$dt_files}
       <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
          <td>             
             <a href="#" class="links_disable"  onClick="define_arquivo('{$arquivo}')">{$arquivo}</a>
          </td>
       </tr>
    {/foreach}
 </table>
 <script language="javascript" type="text/javascript">
 function define_arquivo(arquivo) {ldelim}
    var desc=parent.document.formulario.descricao.value;
    parent.document.formulario.reset() ;
    parent.document.formulario.arquivo.value=arquivo ;
    parent.document.formulario.descricao.value=desc;
    parent.document.formulario.descricao.focus();
 {rdelim}
 </script>