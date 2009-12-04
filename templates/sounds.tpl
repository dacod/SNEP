{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: sounds.tpl - Template usado pelo arquivo sounds.php
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{config_load file="../includes/setup.conf" section="ambiente"}
<table cellspacing="0" align="center" class="contorno">
   <tr>
      <td style="width: 50%;" valign="top">
         <table cellspacing="0" align="center" class="subtable">
            <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao={$ACAO}"  onSubmit="return check_form();">
            <tr style="line-height: 20px;">
               <td class="formlabel" >{$LANG.filename}:</td>
               <td class="subtable" >
                  <input name="arquivo" type="text" size="20"  value="{$dt_sounds.arquivo}" readonly="true" class="campos_disable"/>
               </td>
            </tr>
            <tr style="line-height: 20px;">
               <td class="formlabel" >{$LANG.desc}:</td>
               <td class="subtable">
                  <input type="text" name="descricao" size="50" maxlength="80" class="campos" value="{$dt_sounds.descricao}" />
               </td>
            </tr>
            {if $ACAO=="grava_alterar"}
               <tr><td class="subtable" colspan="2"><hr /></td></tr>
            {/if} 
            <tr style="line-height: 20px;">
               <td class="formlabel">
                  {if $ACAO=="cadastrar"}
                     {$LANG.selectfile}:
                  {else}
                     {$LANG.changefor}:
                  {/if}
               </td>
               <td class="subtable">
                  <input type="file" name="file" size="35" class="campos" onChange="nome_arquivo(this.value,'{$ACAO}')" />
               </td>
            </tr>
            {if $ACAO=="cadastrar" &&  !$SECAO}
               <tr>
                  <td class="formlabel">{$LANG.filetype}:</td>
                  <td class="subtable">
                     {html_radios name="tipo" checked=$dt_sounds.tipo options=$TIPOS_SONS}                     
                  </td>
               </tr>               
            {/if}
            <tr>
               <td class="formlabel">{$LANG.fileformat}:</td>
               <td class="subtable">
                  <input type="checkbox" name="convertgsm" {if #convert_gsm#} checked {/if} />{$LANG.convertgsm}
               </td>
            </tr>
         </table>   <!-- fecha tabela de entrada de dados -->
      </td>
      <td valign="top">
        {if $ACAO=="grava_alterar"}
            <table cellspacing="0" align="center" class="subtable">
               <thead>
               <tr>
                  <td colspan="2">               
                     <strong>{$LANG.additionalinfo}:</strong>
                  </td>
               </tr>
               </thead>
               <tr>
                  <td style="width: 40%">{$LANG.filename}&nbsp;{$LANG.actual}</td>
                  <td>{$dt_sounds.arq_atual}</td>
               </tr>
               <tr>
                  <td>{$LANG.filename}&nbsp;{$LANG.of}&nbsp;{$LANG.backup}</td>
                  <td>{$dt_sounds.arq_backup}</td>
               </tr>
               <tr>
                  <td>{$LANG.filetype}</td>
                  <td>
                    {assign var="tiposom" value=$dt_sounds.tipo}
                    {$TIPOS_SONS.$tiposom}
                    <input type="hidden" name="tipo" value="{$dt_sounds.tipo}" />
                  </td>
            </tr>     
               <tr>
                  <td>{$LANG.lastupdate}</td>
                  <td>{$dt_sounds.data}</td>
               </tr>
            </table>
         {else}
            {if $SECAO != ""}
               <table cellspacing="0" align="center" class="subtable">
                  <thead>
                     <tr>
                        <td colspan="2">               
                           <strong>{$LANG.additionalinfosection}:</strong>
                        </td>
                    </tr>
                  </thead>
                  <tr>
                     <td>{$LANG.name}</td>
                     <td>{$SECAO}</td>
                  </tr>
                  <tr>
                     <td>{$LANG.directory}</td>
                     <td>{$DIRECTORY}</td>
                  </tr>
                  <tr>
                     <td>{$LANG.mode}</td>
                     <td>{$MODO}</td>
                  </tr>
                  <tr>
                     <td>{$LANG.application}</td>
                     <td>{$APP}</td>
                  </tr>
               </table>
            {else}
               <table>
                  <thead>
                     <tr>
                        <td>{$LANG.astfilesoundsdisp}</td>
                     </tr>
                  </thead>
                  <tr>
                     <td valign="top">
                        <iframe src="../src/lista_astsounds.php" frameborder="0" width="100%"></iframe>
                     </td>
                  </tr>
               </table>
            {/if}
         {/if}
      </td>
   </tr>
   <tr>
     <td colspan="2" class="subtable" align="center" height="38px" valign="middle">
        <input type="hidden" name="backup" value="{$dt_sounds.arq_backup}" />
        <input type="hidden" name="atual" value="{$dt_sounds.arq_atual}" />
        <input type="hidden" name="nome_original" value="{$dt_sounds.arquivo}" />
        <!-- Variaiveis para Musicas em Espera 0 INCIO -->
        <input type="hidden" name="secao" value="{$SECAO}" />        
        <input type="hidden" name="app" value="{$APP}" />
        <input type="hidden" name="diretorio" value="{$DIRECTORY}" />
        <input type="hidden" name="modo" value="{$MODO}" />
        <!-- Variaiveis para Musicas em Espera - FIM -->
        <input class="button" type="submit" id="gravar" value="{$LANG.save}">
        <div class="buttonEnding"></div>
        &nbsp;&nbsp;&nbsp;
        {if $SECAO != ""}           
           <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../gestao/musiconhold.php?acao=listar'" />
        {else}           
           <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_sounds.php'" />
        {/if}
        <div class="buttonEnding"></div>      
     </td>
  </tr>
</form>
</table>
{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
 document.forms[0].elements[1].focus() ;
 function check_form() {ldelim}
    campos = new Array() ;
    campos[1]="{$LANG.filename};"+document.formulario.arquivo.value+";NOT_NULL;";
    campos[0]="{$LANG.alert_desc};"+document.formulario.descricao.value+";NOT_NULL;";
    return valida_formulario(campos) ;
 {rdelim}
 
 function nome_arquivo(arquivo,ACAO) {ldelim}
   if (ACAO == "cadastrar") {ldelim}
      var pos=arquivo.lastIndexOf("/");
      if (pos == -1)
         var pos=arquivo.lastIndexOf("\\");
      if (pos >= 0 )
         var arq=arquivo.substr(pos+1);
      else
         var arq=arquivo ;
      document.formulario.arquivo.value=arq ;
   {rdelim}
 {rdelim}
 { include file="../includes/javascript/functions_smarty.js" }
</script>

