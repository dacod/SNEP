{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_grupos.tpl - Relacao dos grupos do sistema      
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >   
   <thead>
      <tr>
         <td class="cen" width="10px">{$LANG.id}</td>
         <td class="esq">{$LANG.name}</td>
         <td class="cen" colspan="2" width="10px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=grupos loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[grupos].cod_grupo}</td>
         <td>{$DADOS[grupos].nome}</td>
         <td align="center" valign="middle" width="30px" >
            <acronym title="{$LANG.change}">
               <a href="../src/grupos.php?acao=alterar&amp;cod_grupo={$DADOS[grupos].cod_grupo}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center" width="30px">
            <acronym title="{$LANG.exclude}">
               <img src="../imagens/delete.png" alt="{$LANG.exclude}" onclick="remove_grupo('{$DADOS[grupos].cod_grupo}')"/>
            </acronym>          
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }

<script type="text/javascript">
{literal}

    /* Confirmacao e remocao de regras de negocio */
    function remove_grupo(id) {
        var url = '../src/grupos.php';
        var params = 'acao=excluir&cod_grupo='+id;

        if(confirm("{/literal} {$LANG.confirm_remocao_grupo} {literal}")) {
            var retorno = new Ajax.Request (
                url, {
                      method: 'post',
                      parameters: params,
                      onComplete: resposta_grupo
                     }
            );
        }
    }

    function resposta_grupo(resp) {
         window.location.href="../src/rel_grupos.php";
    }

{/literal}
</script>