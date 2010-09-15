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
<table cellspacing="0" cellpadding="0" border="0" align="center" >
   <tr>
      <td style="width: 60%;" valign="top">
         <table cellspacing="0" align="center" class="subtable">
            <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">
            <tr>
               <td colspan="2" class="subtable"></td>
            </tr>
            <tr>
               <td class="formlabel" >{$LANG.id}:</td>
               <td class="subtable" >
               <input name="codigo" type="text" size="7" maxlength="7"  value="{$dt_ccustos.codigo}" {if $ACAO == "grava_alterar"} readonly="true" class="campos_disable" {else}  class="campos" {/if} onKeyUp="mascara_ccustos(this,this.value)" onBlur="masc_cod(this,this.value)" /> (ex: 9.99.99)
               </td>
            </tr>
            <tr>
               <td class="formlabel" >{$LANG.name}:</td>
               <td class="subtable" >
               <input name="nome" type="text" size="30" maxlength="40"  class="campos" value="{$dt_ccustos.nome}" >
               </td>
            </tr>
            <tr>
               <td class="formlabel">{$LANG.type}:</td>
               <td class="subtable">
               {html_radios name="tipo" checked=$dt_ccustos.tipo options=$TIPOS_CCUSTOS }
               </td>
            </tr>
            <tr>
               <td class="formlabel" >{$LANG.desc}:</td>
               <td class="subtable">
               <textarea name="descricao" cols="50" rows="3" class="campos" >{$dt_ccustos.descricao}</textarea>
               </td>
            </tr>
         </table>   <!-- fecha tabela de entrada de dados -->
      </td>
      {if $ACAO == "grava_alterar"}
         <td valign="top">            
            <strong>{$LANG.cod_struct}:</strong>
            <br /><br />
            {if $family.father != ""}
               {$family.father}
            {/if}
            {if $family.sun != ""}
               <br  /><br />
               {$family.sun}
            {/if}
            <br /><br />{$dt_ccustos.codigo} - {$dt_ccustos.nome}
         </td>
      {else}
         <td valign="top">
            <iframe src="../src/lista_ccustos.php" frameborder="0" width="100%"></iframe>
         </td>
     {/if}
   <tr>
     <td colspan="2" class="subtable" align="center" height="38px" valign="middle">
        <input class="button" type="submit" id="gravar" value="{$LANG.save}">
        <div class="buttonEnding"></div>
        &nbsp;&nbsp;&nbsp;
        <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../index.php/costcenter/'" />
        <div class="buttonEnding"></div>      
     </td>
  </tr>
</form>
</table>
{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
 document.forms[0].elements[0].focus() ;
 function masc_cod(objeto,codigo) {ldelim}
    if (codigo.length == 2 ) 
       codigo = codigo.substring(0,1);
    if (codigo.length == 3 ) {ldelim}
       ncodigo = codigo.substring(0,1)+".0"+codigo.substring(2) ;
       codigo = ncodigo ;
    {rdelim}
    if (codigo.length == 5 )
       codigo = codigo.substring(0,4);
    if (codigo.length == 5 ) {ldelim}
       ncodigo = codigo.substring(0,4)+".0"+codigo.substring(5) ;
       codigo = ncodigo ;
    {rdelim}
    objeto.value = codigo ;   
 {rdelim}
 function check_form() {ldelim}
       campos = new Array() ;
       campos[0] = "{$LANG.name};"+document.formulario.nome.value+";NOT_NULL;";
       return valida_formulario(campos) ;
 {rdelim}
 { include file="../includes/javascript/functions_smarty.js" }
</script>
<script  language="JavaScript" type="text/javascript" scr="../includes/javascript/functions.js"></script>