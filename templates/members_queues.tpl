{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: members_queues.tpl - Membros de uma Fila
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
 {include file="cabecalho.tpl"}
 {config_load file="../includes/setup.conf" section="ambiente"}
 <script type="text/javascript" src="../includes/javascript/fselects.js"></script>
 <fieldset>
 <table align="center">
    <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}" onsubmit="valida_formulario()">
    <tr>
       <td rowspan="2" class="norightcenter" width="40%"> 
          <div id="titulo">
          {$LANG.ramais_free}
          </div>
          <select name="lista1[]" id="lista1" multiple="true" size="10" class="campos" style="width: 300px;" >
             {html_options options=$OPCOES_LIVRES}
          </select>
       </td>
       <td class="subtable" style="text-align:center; vertical-align: middle;">
          <a href="#"  onclick="movimento('lista2', 'passar', 'lista1')">
             <img src="../imagens/go-next.png" border="0"  width="32" height="32"/>
          </a>
       </td>
       <td class="noleftrightcenter" rowspan="2" width="40%">
          <div id="titulo">
             {$LANG.queue_members}
          </div>
          <select  class="campos" name="lista2[]" multiple="true" id="lista2" size="10" style="width: 300px;" >
             {html_options options=$OPCOES_USADOS}
          </select>
       </td>
       <td class="subtable" style="text-align:center; vertical-align: middle;">
          <a href="#" onclick="movimento('lista2', 'cima')">
             <img src="../imagens/go-up.png" border="0" width="32" height="32" /> 
          </a>
       </td>
    </tr>
    <tr>
       <td class="subtable" style="text-align:center; vertical-align: middle; ">
           <a href="#" onclick="movimento('lista1', 'passar','lista2')">
              <img src="../imagens/go-previous.png" border="0"  width="32" height="32"/>
           </a>
       </td>
       <td class="subtable" style="text-align:center; vertical-align: middle; ">
          <a href="#" onclick="movimento('lista2', 'baixo')">
             <img src="../imagens/go-down.png"  border="0"  width="32" height="32"/>
          </a>
       </td>
    </tr>
    
    <tr>       
       <td colspan="4" class="cen" height="40px" valign="middle">
          <input type="hidden" name="name" value="{$name}"  />
          <input class="button" type="submit" name="gravar" value="{$LANG.save}">
          <div class="buttonEnding"></div>
          &nbsp;&nbsp;&nbsp;
          <input class="button" type="button" name="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_queues.php'" />
          <div class="buttonEnding"></div>      
       </td>
    </tr>
    </form>
 </table>
 </fieldset>
 { include file="rodape.tpl }
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