{* Smarty *}
{* Template: grupos.tpl - Formulario para Cadastro de grupos       
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * --------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
<table cellspacing="0" align="center" class="contorno">
   <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">
   <tr>
      <td colspan="2" class="subtable"></td>
   </tr>
   <tr>
      <td class="formlabel" >{$LANG.name}:</td>
      <td class="subtable" >
        <input name="nome" type="text" size="30" maxlength="50"  class="campos" value="{$dt_grupos.nome}" >
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
        <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_grupos.php'" />
        <div class="buttonEnding"></div>      
     </td>
  </tr>
  <input type="hidden" name="cod_grupo" value="{$dt_grupos.cod_grupo}" />
</form>
</table>
{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
 document.forms[0].elements[0].focus() ;
 function check_form() {ldelim}
       campos = new Array(1) ;
       campos[0] = "{$LANG.name};"+document.formulario.nome.value+";NOT_NULL;";
       return valida_formulario(campos) ;
   {rdelim}
   { include file="../includes/javascript/functions_smarty.js" }
 </script>
