{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: operadoras.tpl - Formulario para Cadastro de operadoras
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
<table cellspacing="0" align="center" class="contorno">
   <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">   
   <tr>
      <td colspan="2" class="subtable"></td>
   </tr>
   <tr>
      <td class="formlabel" >{$LANG.name}:</td>
      <td class="subtable" >
        <input name="nome" type="text" size="30" maxlength="50"  class="campos" value="{$dt_operadoras.nome}" >
      </td>
   </tr>
  <tr>
    <td class="formlabel">{$LANG.firstmin}:</td>
    <td class="subtable" >
      <input name="tpm" type="text" size="2" maxlength="3" class="campos" value="{$dt_operadoras.tpm|default:0}" > {$LANG.time_secs}
    </td>
  </tr>
  <tr>
    <td class="formlabel">{$LANG.outmin}:</td>
    <td class="subtable" >
      <input name="tdm" type="text" size="2" maxlength="3" class="campos" value="{$dt_operadoras.tdm|default:0}" > {$LANG.time_secs}
    </td>
  </tr>
  <tr>
    <td class="formlabel">{$LANG.vlrbase_fix}:</td>
    <td class="subtable" >
      <input name="tbf" type="text" size="5" maxlength="6" class="campos" value="{$dt_operadoras.tbf}" /> {$LANG.dottodec}
    </td>
  </tr>

  <tr>
    <td class="formlabel">{$LANG.vlrpartida_fix}:</td>
    <td class="subtable" >
      <input name="vpf" type="text" size="5" maxlength="6" class="campos" value="{$dt_operadoras.vpf}" /> {$LANG.dottodec}
    </td>
  </tr>
  <tr>
    <td class="formlabel">{$LANG.vlrbase_cel}:</td>
    <td class="subtable" >
      <input name="tbc" type="text" size="5" maxlength="6" class="campos" value="{$dt_operadoras.tbc}" /> {$LANG.dottodec}
    </td>
  </tr>

  <tr>
    <td class="formlabel">{$LANG.vlrpartida_cel}:</td>
    <td class="subtable" >
      <input name="vpc" type="text" size="5" maxlength="6" class="campos" value="{$dt_operadoras.vpc}" /> {$LANG.dottodec}
    </td>
  </tr>


  <tr>
     <td class="formlabel">
       {$LANG.menu_ccustos}:
     </td>
     <td class="subtable">
        <table  class="subtable">
           <tr>
              <td rowspan="2" class="subtable" width="40%">
                 <select name="ccustos[]" id="ccustos" multiple="true" size="4" class="campos" style="width: 300px;" />
                     {html_options options=$CCUSTOS}
                 </select>
              </td>
              <td class="subtable"  align="center">
                 <a href="#"  onclick="movimento('oper_ccustos', 'passar', 'ccustos')">
                    <img src="../imagens/go-next.png" border="0" />
                 </a>
              </td>
              <td  class="subtable" rowspan="2" width="40%">
                 <select  class="campos" name="oper_ccustos[]" multiple="true" id="oper_ccustos" size="4" style="width: 300px;" >
                    {html_options options=$OPER_CCUSTOS}
                 </select>
              </td>
           </tr>
           <tr>
              <td class="subtable" align="center">
                 <a href="#" onclick="movimento('ccustos', 'passar','oper_ccustos')">
                    <img src="../imagens/go-previous.png" border="0" />
                 </a>
              </td>
           </tr>
        </table>
     </td>
  </tr>
  <tr>
    <td class="subtable" colspan="2" ><hr /></td>
  </tr>
  <tr>
     <td colspan="2" class="subtable" align="center" height="32px" valign="top">
        <input type="hidden" name="codigo" value="{$dt_operadoras.codigo}" />
        <input class="button" type="submit" id="gravar" value="{$LANG.save}">
        <div class="buttonEnding"></div>
        &nbsp;&nbsp;&nbsp;
        <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../tarifas/rel_operadoras.php'" />
        <div class="buttonEnding"></div>      
     </td>
  </tr>
</form>
</table>
{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
  document.forms[0].elements[0].focus() ;
  function check_form() {ldelim}
     var listBox = document.formulario.oper_ccustos;
     var len = listBox.length;
     for(var x=0;x<len;x++){ldelim}
        listBox.options[x].selected= true;
     {rdelim}
     campos = new Array() ;
     campos[0] = "{$LANG.name};"+document.formulario.nome.value+";NOT_NULL;";
     campos[1] = "{$LANG.firstmin};"+document.formulario.tpm.value+";NUM;";
     campos[2] = "{$LANG.outmin};"+document.formulario.tdm.value+";NUM;";
     campos[3] = "{$LANG.vlrbase_fix};"+document.formulario.tbf.value+";FLOAT;";
     campos[4] = "{$LANG.vlrbase_cel};"+document.formulario.tbc.value+";FLOAT;";
     return valida_formulario(campos) ;
  {rdelim}
  { include file="../includes/javascript/functions_smarty.js" }
</script>
<script type="text/javascript" src="../includes/javascript/fselects.js"></script>