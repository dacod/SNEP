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
{include file="cabecalho.tpl"}

<form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?action=process">
    <table>
        <tr>
            <td colspan="2" class="subtable">&nbsp;</td>
        </tr>

        <tr>
            <td class="formlabel" ><label for="contacts_csv">Arquivo CSV:</label></td>
            <td class="subtable" >
                <input type="file" name="contacts_csv" id="contacts_csv" />
            </td>
        </tr>

        <tr>
            <td colspan="2" class="subtable">&nbsp;</td>
        </tr>

        <tr>
            <td colspan="2" class="subtable" align="center" height="32px" valign="top">
                <input class="new_button" type="submit" id="enviar" value="Enviar">
                <input class="new_button" type="button" id="voltar" value="Cancelar" onClick="location.href='../src/rel_cont_names.php'" />
            </td>
        </tr>
    </table>
</form>

{ include file="rodape.tpl }