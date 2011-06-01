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

<form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?action=process">
    <table>
        <tr>
            <td colspan="2" class="subtable">
                <p style="text-align:center;">
                    Selecione todos ou um grupo de contato a ser exportado.
                </p>

            </td>
        </tr>

        <tr>
            <td class="formlabel" ><label for="contacts_csv">Grupo de Contato:</label></td>
            <td class="subtable" >
                  <select name="grupo">
                      {html_options options=$GRUPOS}
                  </select>
            </td>
        </tr>

        <tr>
            <td colspan="2" class="subtable">&nbsp;</td>
        </tr>

        <tr>
            <td colspan="2" class="subtable" align="center" height="32px" valign="top">
                <input class="new_button" type="submit" id="enviar" value="Exportar">
                <input class="new_button" type="button" id="voltar" value="Cancelar" onClick="location.href='../index.php/contacts/'" />
            </td>
        </tr>
    </table>
</form>

{ include file="rodape.tpl }