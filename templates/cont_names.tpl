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
<table cellspacing="0" align="center" class="contorno">
   <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">   
   <tr>
      <td colspan="2" class="subtable"></td>
   </tr>

   <tr>
      <td class="formlabel" >{$LANG.id}:</td>
      <td class="subtable" >
        <input name="lastid" type="text" size="5" maxlength="5" {if $ACAO == "cadastrar"}  class="campos"  value="{$LASTID}" {else} class="campos_disable" readonly="true"  value="{$dt_contatos.id} {/if}  " >
      </td>
   </tr>
   <tr>
      <td class="formlabel" >{$LANG.name}:</td>
      <td class="subtable" >
        <input name="name" type="text" size="30" maxlength="50"  class="campos" value="{$dt_contatos.name}" >
      </td>
   </tr>
   <tr>
      <td class="formlabel" >{$LANG.address}:</td>
      <td class="subtable" >
        <input name="address" type="text" size="30" maxlength="50"  class="campos" value="{$dt_contatos.address}" >
      </td>
   </tr>
   <tr>
     <td class="formlabel">{$LANG.city}:</td>
     <td class="subtable" >
        <input name="city" type="text" size="20" maxlength="30" class="campos" value="{$dt_contatos.city}" >
     </td>
   </tr>
   <tr>
     <td class="formlabel">{$LANG.state}:</td>
     <td class="subtable" >
        <input name="state" type="text" size="2" maxlength="2" class="campos" value="{$dt_contatos.state}" />
     </td>
   </tr>
   <tr>
     <td class="formlabel">{$LANG.cep}:</td>
     <td class="subtable" >
        <input name="cep" type="text" size="8" maxlength="9" class="campos" value="{$dt_contatos.cep}" />  (999999-999)
     </td>
   </tr>   
   <tr>
     <td class="formlabel">{$LANG.phones}:</td>
     <td class="subtable" >
        <input name="phone_1" type="text" size="15" maxlength="15" class="campos" value="{$dt_contatos.phone_1}" />
        &nbsp;&nbsp;&nbsp;&nbsp;{$LANG.phone_format_contact}
        
     </td>
   </tr>
   <tr>
     <td class="formlabel">{$LANG.cells}:</td>
     <td class="subtable" >
        <input name="cell_1" type="text" size="15" maxlength="15" class="campos" value="{$dt_contatos.cell_1}" />
        &nbsp;&nbsp;&nbsp;&nbsp;{$LANG.phone_format_contact}
        
     </td>
   </tr>
   <tr>
      <td colspan="2" class="subtable"><hr /></td>
   </tr>
   <tr>
     <td colspan="2" class="subtable" align="center" height="32px" valign="top">
        <input type="hidden" name="id" value="{$dt_contatos.id}" />
        <input class="button" type="submit" id="gravar" value="{$LANG.save}">
        <div class="buttonEnding"></div>
        &nbsp;&nbsp;&nbsp;
        <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_cont_names.php'" />
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
     campos[0] = "{$LANG.name};"+document.formulario.name.value+";NOT_NULL;";
     campos[1] = "{$LANG.phones};"+document.formulario.phone_1.value+";NUM;";
     campos[2] = "{$LANG.cells};"+document.formulario.cell_1.value+";NUM;";
     return valida_formulario(campos) ;
  {rdelim}
  { include file="../includes/javascript/functions_smarty.js" }
</script>
<script type="text/javascript" src="../includes/javascript/fselects.js"></script>
