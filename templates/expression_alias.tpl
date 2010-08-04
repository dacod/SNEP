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
<form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?action={$ACAO}"  onSubmit="atualizaValues();">
    <table>
       <tr>
          <td colspan="2" class="subtable"></td>
       </tr>
       <tr>
          <td class="formlabel" >{$LANG.name}:</td>
          <td class="subtable" >
            <input name="name" type="text" size="30" maxlength="50"  class="campos" value="{$alias.name}" />
          </td>
       </tr>
       <tr>
          <td class="formlabel" style="vertical-align: top;" >Expressões:</td>
          <td class="subtable" >
            <input type="hidden" name="exprValue" id="exprValue" value="--" />
            <ul id="expr" style="list-style:none; padding: 0px; margin: 0px;"></ul>
          </td>
       </tr>
       <tr>
         <td colspan="2" class="subtable" style="text-align: center;border-top: 1px solid rgb(204, 204, 204);padding: 4px 0px;"  >
            <input class="new_button" type="submit" id="gravar" value="{$LANG.save}">
            <input class="new_button" type="button" id="voltar" value="Cancelar" onClick="location.href='../gestao/expression_alias.php'" />
         </td>
      </tr>
    </table>
</form>
{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
    {literal}
    init = function() {
        exprObj = new MultiWx('expr', StringField);
        {/literal}
        {if !$alias.expressions}
        exprObj.addItem(1);
        {else}
        {foreach from=$alias.expressions key=k item=expression}
        exprObj.addItem(1);
        exprObj.widgets['{$k}'].value='{$expression}';
        {/foreach}
        {/if}
        {literal}
        exprObj.render();
    }


    Event.observe(window, 'load', init, false);

    function atualizaValues() {
        $('exprValue').value  = exprObj.getValue();
    }
    //{/literal}

    document.forms[0].elements[0].focus();
    function check_form() {ldelim}
        campos = new Array(1);
        campos[0] = "{$LANG.name};"+document.formulario.nome.value+";NOT_NULL;";
        return valida_formulario(campos);
    {rdelim}
    { include file="../includes/javascript/functions_smarty.js" }
 </script>