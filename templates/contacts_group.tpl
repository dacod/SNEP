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
{config_load file="../includes/setup.conf" section="ambiente"}
{include file="cabecalho.tpl"}
<script type="text/javascript" src="../includes/javascript/fselects.js"></script>
<form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}&cod_grupo={$dt_grupos.id}"  onSubmit="return check_form();">
<table cellspacing="0" align="center" class="contorno">
    <tr>
        <td colspan="2" class="subtable"></td>
    </tr>
    <tr>
        <td class="formlabel">{$LANG.name}:</td>
        <td class="subtable">
            <input name="nome" type="text" size="30" maxlength="50"  class="campos" value="{$dt_grupos.name}" >
        </td>
    </tr>
    <tr>
    <input type="hidden" name="grupo" id="grupo" value="{$GRUPO}" />
    <td rowspan="2" class="norightcenter" width="40%">
        <div id="titulo">
            {$LANG.ramais_free}
        </div>
        <select name="lista1[]" id="lista1" multiple="true" size="10" class="campos" style="width: 300px;" >
            {html_options options=$CONTACTS}
        </select>
    </td>
    <td class="subtable" style="text-align:center; vertical-align: middle;">
        <a href="#"  onclick="movimento('lista2', 'passar', 'lista1')">
            <img src="../imagens/go-next.png" border="0"  width="32" height="32"/>
        </a>
    </td>
    <td class="noleftrightcenter" rowspan="2" width="40%">
        <div id="titulo">
            {$LANG.include_exten} :
        </div>
        <select  class="campos" name="lista2[]" multiple="true" id="lista2" size="10" style="width: 300px;" >
            {if $EDITAR}
                {html_options options=$PERTENCE}
            {else}
                {html_options}
            {/if}
        </select>
    </td>
    </tr>
    <tr>
        <td class="subtable" style="text-align:center; vertical-align: middle; ">
            <a href="#" onclick="movimento('lista1', 'passar','lista2')">
                <img src="../imagens/go-previous.png" border="0"  width="32" height="32"/>
            </a>
        </td>
    </tr>

    <tr>
        <td colspan="3" class="subtable" align="center" height="32px" valign="top">
            <br />
            <input class="button" type="submit" id="gravar" value="{$LANG.save}">
            <div class="buttonEnding"></div>
            &nbsp;&nbsp;&nbsp;
            <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_contacts_groups.php'" />
            <div class="buttonEnding"></div>
        </td>
    </tr>
</table>
</form>


{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
    document.forms[0].elements[0].focus() ;
    function check_form() {ldelim}
        campos = new Array(1) ;
        campos[0] = "{$LANG.name};"+document.formulario.nome.value+";ALPHANUM;";
        return valida_formulario(campos);
    {rdelim}
    { include file="../includes/javascript/functions_smarty.js" }
</script>

{literal}
<script language="javascript" type="text/javascript">
    function valida_formulario() {
        var listBox = document.formulario.lista2;
        var len = listBox.length;
        for(var x=0;x<len;x++){
            listBox.options[x].selected= true;
        }
    }
</script>
{/literal}
