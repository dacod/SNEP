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
          <input class="button" type="button" name="voltar" value="{$LANG.back}" onClick="location.href='../index.php/queues'" />
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