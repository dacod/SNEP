{* Smarty *}
{* Template: grupos.tpl - Formulario para Cadastro de grupos       
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * --------------------------------------------------------------- *}
{config_load file="../includes/setup.conf" section="ambiente"}
{include file="cabecalho.tpl"}
 <script type="text/javascript" src="../includes/javascript/fselects.js"></script>
<table cellspacing="0" align="center" class="contorno">
   <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">
   <tr>
      <td colspan="2" class="subtable"></td>
   </tr>
   <tr>
      <td class="formlabel" >{$LANG.name}:</td>
      <td class="subtable" >
        <input name="nome" type="text" size="30" maxlength="50"  class="campos" value="{$dt_grupos.name}" >
      </td>
   </tr>
   <tr>
      <td class="formlabel" >{$LANG.type}:</td>
      <td style="border:0px;border-bottom: 1px solid #A4A7AB;">
        <input type="radio" name="type" value="admin" id="admin_type" {if $dt_grupos.inherit == 'admin'}checked="checked"{/if} /><label for="admin_type">{$LANG.admin}</label>
        <input type="radio" name="type" value="users" id="user_type" {if $dt_grupos.inherit == 'users' || !$dt_grupos.inherit}checked="checked"{/if} /><label for="user_type">{$LANG.user}</label>
       </td>
   </tr>

    <tr>
       <input type="hidden" name="grupo" id="grupo" value="{$GRUPO}" />
       <td rowspan="2" class="norightcenter" width="40%">
          <div id="titulo">
          {$LANG.ramais_free}
          </div>
          <select name="lista1[]" id="lista1" multiple="true" size="10" class="campos" style="width: 300px;" >
                {html_options options=$RAMAIS}
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
        <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_groups.php'" />
        <div class="buttonEnding"></div>
     </td>
  </tr>
  <input type="hidden" name="cod_grupo" value="{$dt_grupos.name}" />
</form>

    
</table>


{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
 document.forms[0].elements[0].focus() ;
 function check_form() {ldelim}
       campos = new Array(1) ;
       campos[0] = "{$LANG.name};"+document.formulario.nome.value+";ALPHANUM;";
       return valida_formulario(campos) ;
   {rdelim}
   { include file="../includes/javascript/functions_smarty.js" }
 </script>

 {literal}
  <script language="javascript" type="text/javascript">
  /*---------------------------------------------------------------------------
   * Funcoes JAVA de validacao do Formulario
   * --------------------------------------------------------------------------*/
  function valida_formulario() {
     var listBox = document.formulario.lista2;
     var len = listBox.length;
     for(var x=0;x<len;x++){
        listBox.options[x].selected= true;
     }
  }
  </script>
 {/literal}
