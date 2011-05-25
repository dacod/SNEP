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
<table cellspacing="0" align="center" class="contorno">
   <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">
   <input type="hidden" name="codigo" value="{$dt_contas.codigo}" />
   <tr>
      <td colspan="2" class="subtable"></td>
   </tr>
   <tr>
      <td class="formlabel" >{$LANG.desc}:</td>
      <td class="subtable" >
        <input name="nome" type="text" size="30" maxlength="50"  class="campos" value="{$dt_contas.nome}" >
      </td>
   </tr>
  <tr>
    <td class="formlabel">{$LANG.accounttype}:</td>
    <td class="subtable">   
    {html_radios name="tipo" checked=$dt_contas.tipo options=$TIPOS_CONTAS }
    </td>
  </tr>
  <tr>
    <td class="subtable" colspan="2" ><hr /></td>
  </tr>
  <tr>
     <td colspan="2" class="subtable" align="center" height="32px" valign="top">
        <input class="button" type="submit" id="gravar" value="{$LANG.save}">
        <div class="buttonEnding"></div>
        &nbsp;&nbsp;&nbsp;
        <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_contas.php'" />
        <div class="buttonEnding"></div>      
     </td>
  </tr>
</form>
</table>
{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
 document.forms[0].elements[0].focus() ;
 function check_form() {ldelim}
       campos = new Array() ;
       campos[0] = "{$LANG.name};"+document.formulario.nome.value+";NOT_NULL;";
       return valida_formulario(campos) ;
 {rdelim}
 { include file="../includes/javascript/functions_smarty.js" }
</script>