{* Smarty *}
{* Template: grupos.tpl - Formulario para Cadastro de grupos       
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Henrique Grolli Bassotto <henrique@opens.com.br>            
 * --------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
<table cellspacing="0" align="center" class="contorno">
   <form method="post" action="{$SUBMIT_FILE}?acao=salvar">
   <input type="hidden" name="conf_file" value="{$CONF_FILE}"  />
   <tr>
      <td width="60%">
         <textarea style="width: 100%; height: 360px; border: 1px solid #999; color: white; background-color:#333;" name="text">{$CONF_CONTENT}</textarea>
      </td>
      <td>
         <iframe src="app_help.php" style="width:100%; height:360px; border: 1px solid #999;" ></iframe>
      </td>
   </tr>
   <tr>
      <td colspan="2" style="text-align: center; padding:5px;">
         <input type="submit" value="{$LANG.save}" class="button">
         <div class="buttonEnding"></div>
         &nbsp;&nbsp;
         <input class="button" type="button" name="voltar" value="{$LANG.discard}" onClick="location.href='../src/sistema.php'" />
          <div class="buttonEnding"></div>
      </td>
   </tr>
   </form>
</table>
{ include file="rodape.tpl }