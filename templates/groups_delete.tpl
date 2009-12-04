{* Smarty *}
{* Template: grupos.tpl - Formulario para Cadastro de grupos
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * --------------------------------------------------------------- *}
{include file="cabecalho.tpl"}
<table cellspacing="0" align="center" class="contorno">
   <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao=excluir_def"  onSubmit="return check_form();">
   <tr>
      <td colspan="2" class="subtable"></td>
   </tr>
   <tr>
      <td class="formlabel" style="width: 60%;" >{$LANG.migrar_grupo}:</td>
      <td class="subtable" style="width: 40%;" >
        <select name="group" class="campos">
            {html_options options=$dt_grupos selected=users}
        </select>
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
        <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_groups.php'" />
        <div class="buttonEnding"></div>
     </td>
  </tr>
  <input type="hidden" name="cod_grupo" value="{$name}" />
</form>
</table>
{ include file="rodape.tpl }