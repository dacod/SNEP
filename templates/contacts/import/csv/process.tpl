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
{include file="cabecalho.tpl"}



<form name="formulario" method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?action=finish">
    <div style="overflow: auto;">
        <table>
            <thead>
              <tr>
                  {section name=field loop=$fields}
                  <td class="cen">
                      <select name="assoc[{$smarty.section.field.index}]">
                          {html_options options=$contacts_fields}
                      </select>
                  </td>
                  {/section}
              </tr>
           </thead>
           <tbody id="tbody">
               {section name=row loop=$sample_data}
               <tr>
                   {section name=col loop=$sample_data[row]}
                   <td>{$sample_data[row][col]}</td>
                   {/section}
               </tr>
              {/section}
           </tbody>
        </table>
    </div>

    <div id="main_container" style="border-top: none;">
    <p style="text-align: center; margin: 0px; padding: 5px;">
        <label for="group">Grupo:</label> <select id="group" name="group" class="campos">
            {html_options options=$GROUPS selected=$dt_contatos.group}
        </select><br />
        <input type="checkbox" name="discard_first_row" id="discard_first_row" /><label for="discard_first_row">Descartar primeira linha</label><br />
        <input class="new_button" type="submit" id="salvar" value="Salvar" />
        <input class="new_button" type="button" id="voltar" value="Cancelar" onClick="location.href='../index.php/contacts/'" />
    </p>
    </div>
</form>


{ include file="rodape.tpl }
