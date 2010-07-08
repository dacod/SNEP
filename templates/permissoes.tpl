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
 {config_load file="../includes/setup.conf" section="cores"}
 <script type="text/javascript" src="../includes/javascript/fselects.js"></script>
     
 <table align="center" class="noborder">
    <tr>
       <td class="usuario">{$dt_usuario}</td>
    </tr>
 </table>

 <table align="center" style="border:0px;">
 <form name="formulario" action="{$smarty.server.SCRIPT_NAME}"  method="POST" onsubmit="valida_formulario();">
    <thead>
       <tr>
          <td colspan="4" class="esq" width="100%">{$LANG.vinc}</td>
       </tr>
    </thead>
        <tr>
           <td rowspan="1" class="noborder norightcenter" width="40%">
              <div id="titulo">
              {$LANG.vinc_for_exten}
              </div>
              <select class="campos" name="vinculo1[]" id="vinculo1" multiple="true" size="10" style="width: 300px;" >
                 {html_options options=$LISTA_DESVINCULADOS}
              </select>
           </td>

           <td class="subtable noborder"  align="center">
              <span onclick="movimento('vinculo2', 'passar', 'vinculo1')">
                 <img src="../imagens/go-next.png" border="0" />
              </span>
               <br />

              <span onclick="movimento('vinculo1', 'passar', 'vinculo2')">
                 <img src="../imagens/go-previous.png" border="0" />
              </span>

           </td>

           <td class="noleftcenter"  width="40%">
              <div id="titulo">
                 {$LANG.vinc_exten}
              </div>
              <select  class="campos" name="vinculo2[]" id="vinculo2" multiple="true" size="10" style="width: 300px;" >
                 {html_options options=$LISTA_VINCULADOS}
              </select>
           </td>
           
        </tr>

        <tr>
            <td colspan="5" class="cen" height="40" >
                  <input type="submit" value="{$LANG.save}" name="permissao" class="button" />
                  <div class="buttonEnding"></div>
                  &nbsp;&nbsp;&nbsp;
                  <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/extensions.php'" />
                  <div class="buttonEnding"></div>
            </td>
        </tr>

 </table>   
<!--
 <table>
        <tr>            
            <td rowspan="1" class="norightcenter" width="40%">
              <div id="titulo">
              {$LANG.vinc_group_for_exten}
              </div>
              <select class="campos" name="grupo_vinculo1[]" id="grupo_vinculo1" multiple="true" size="10" style="width: 300px;" >
                 {html_options options=$LISTA_GRUPOS_DESVINCULADOS}
              </select>
            </td>
            <td class="subtable"  align="center">
              <span onclick="movimento('grupo_vinculo2', 'passar', 'grupo_vinculo1')">
                 <img src="../imagens/go-next.png" border="0" />
              </span>
               <br />

              <span onclick="movimento('grupo_vinculo1', 'passar', 'grupo_vinculo2')">
                 <img src="../imagens/go-previous.png" border="0" />
              </span>

            </td>
            <td class="noleftrightcenter" rowspan="1rowspan" width="40%">
              <div id="titulo">
                 {$LANG.vinc_group_exten}
              </div>
              <select  class="campos" name="grupo_vinculo2[]" id="grupo_vinculo2" multiple="true" size="10" style="width: 300px;" >
                 {html_options options=$LISTA_GRUPOS_VINCULADOS}
              </select>
            </td>
        </tr>



</table>
-->

<table>
    <thead>
       <tr>
          <td width="10%">{$LANG.id}</td>
          <td width="60%" class="esq">{$LANG.desc}</td>
          <td>{$LANG.autorized}</td>
       </tr>  
    </thead>
   
    <input type="hidden" name="id" value="{$dt_id}">
    <input type="hidden" name="name" value="{$dt_name}">
    
    {section name=perms loop=$dt_permissoes}
    <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
       <td class="cen">{$dt_permissoes[perms].cod_rotina}</td>
       <td class="esq">{$dt_permissoes[perms].desc_rotina}</td>       
       <td class="cen">      
          {html_radios name=$dt_permissoes[perms].cod_rotina checked=$dt_permissoes[perms].permissao options=$TIPOS_PERMS  separator="&nbsp;&nbsp;&nbsp;"}
       </td>      
    </tr>   
    {/section}

    <tr>
       <td colspan="3" class="cen" height="40" >
          <input type="submit" value="{$LANG.save}" name="permissao" class="button" />
          <div class="buttonEnding"></div>     
          &nbsp;&nbsp;&nbsp;
          <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_ramais.php'" />
         <div class="buttonEnding"></div>     
      </td>
    </tr>

 </form>

 </table>

 <script language="javascript" type="text/javascript">
 {literal}
     function valida_formulario() {

         var vinculo = document.formulario.vinculo2;
         var vinculo_tam = vinculo.length;

         for( var x=0 ; x < vinculo_tam ; x++ ) {
            vinculo.options[x].selected=true;
         }

         var grupo_vinculo = document.formulario.grupo_vinculo2;
         var grupo_tam = grupo_vinculo.length;
         
         for( var x=0 ; x < grupo_tam ; x++ ) {
            grupo_vinculo.options[x].selected=true;
         }

      }
 {/literal}
 </script>
 { include file="rodape.tpl }
