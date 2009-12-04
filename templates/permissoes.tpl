{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: permissoes.tpl - Define permissoes do usuario
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
 {include file="cabecalho.tpl"}
 {config_load file="../includes/setup.conf" section="cores"}
 <table align="center">
    <tr>
       <td class="usuario">{$dt_usuario}</td>
    </tr>
</table>
 <table>
    <thead>
       <tr>
          <td width="10%">{$LANG.id}</td>
          <td width="60%" class="esq">{$LANG.desc}</td>
          <td>{$LANG.autorized}</td>
       </tr>  
    </thead>
    <form name="formulario" action="{$smarty.server.SCRIPT_NAME}"  method="POST" enctype="multipart/form-data" >
    <input type="hidden" name="id" value="{$dt_id}">
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
          <input type="submit" value="{$LANG.save}" name="permissao" class="button">
          <div class="buttonEnding"></div>     
          &nbsp;&nbsp;&nbsp;
          <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_ramais.php'" />
         <div class="buttonEnding"></div>     
      </td>
    </tr>
    </form>
 </table>
 { include file="rodape.tpl }
