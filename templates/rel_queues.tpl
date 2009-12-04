{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_queues.tpl - Relacao das Queues Cadastradas       
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
         <td class="esq" width="10%">{$LANG.name}</td>
         <td class="cen">{$LANG.q_strategy}</td>
         <td class="cen">{$LANG.q_maxlen}</td>
         <td class="cen">{$LANG.q_musiconhold}</td>
         <td class="cen" colspan="3" width="100px;">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=queues loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="esq">{$DADOS[queues].name}</td>
         {assign var="strategy" value=$DADOS[queues].strategy}
         <td class="esq">{$OPCOES_STRATEGY.$strategy}</td>
         <td class="cen">{$DADOS[queues].maxlen}</td>
         <td class="cen">{$DADOS[queues].musiconhold}</td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/queues.php?acao=alterar&amp;name={$DADOS[queues].name}"><img src="../imagens/edit.png" alt="{$LANG.change}" border="0" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
                <img src="../imagens/delete.png" alt="{$LANG.exclude}" border="0" onclick="remove_queue('{$DADOS[queues].name}');"/>
            </acronym>          
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.queue_members}">
               <a href="../src/members_queues.php?name={$DADOS[queues].name}"><img src="../imagens/usuario.png" alt="{$LANG.queue_members}" border="0" /></a>
            </acronym>          
         </td>       
      </tr>
   {/section}
</table>
<script type="text/javascript">
{literal}

    /* Confirmacao e remocao de regras de negocio */
    function remove_queue(id) {
        var url = '../src/queues.php';
        var params = 'acao=excluir&name='+id;

        if(confirm("{/literal} {$LANG.confirm_remocao_queue} {literal}")) {
            var retorno = new Ajax.Request (
                url, {
                      method: 'post',
                      parameters: params,
                      onComplete: resposta_queue
                     }
            );
        }
    }

    function resposta_queue(resp) {
         window.location.href="../src/rel_queues.php";
    }

{/literal}
</script>
{ include file="rodape.tpl }