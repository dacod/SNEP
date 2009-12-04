{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_troncos.tpl - Relacao das Troncos Cadastrados       
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *} 
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
{config_load file="../includes/setup.conf" section="ambiente"}
{math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="10px">{$LANG.tronco}</td>
         <td class="cen" width="25%">{$LANG.desc}</td>
         <td class="cen" width="20%">{$LANG.technologies}</td>
         <td class="cen" width="15%">{$LANG.trunktype}</td>
         <td class="esq" width="20%">{$LANG.trunkredund}</td>
         <td class="cen" colspan="2" width="10px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=troncos loop=$DADOS max=#linelimit# start=$INI}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[troncos].name}</td>
         <td>{$DADOS[troncos].callerid}</td>
         <td class="cen">{$DADOS[troncos].tecnologias}</td>
         {assign var="tt" value=$DADOS[troncos].trunktype}
         <td class="cen">{$OPCAO_TTRONCO.$tt}</td>
         <td class="esq">{$DADOS[troncos].trunkredund}</td>
         <form name="acao" method="post" action="../src/troncos.php">
         <td align="center" valign="middle" width="30px;">
            <acronym title="{$LANG.change}">
               <a href="../src/troncos.php?acao=alterar&amp;id={$DADOS[troncos].id}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center" width="30px;">
            <acronym title="{$LANG.exclude}">
               <img src="../imagens/delete.png" alt="{$LANG.exclude}" onclick="remove_tronco('{$DADOS[troncos].id}','{$DADOS[troncos].name}','{$DADOS[troncos].trunktype}')"/>
            </acronym>          
         </td>
      </tr>
   {/section}
   <tr class="dir">
      <td colspan="9" class="links" >
         {include file="paginacao.tpl"}
      </td>
   </tr>

</table>
{ include file="rodape.tpl }

<script type="text/javascript">
{literal}

    /* Confirmacao e remocao de regras de negocio */
    function remove_tronco(id,nm,tt) {
        var url = '../src/troncos.php';
        var params = 'acao=excluir&id='+id+'&nm='+nm+'&tt='+tt;

        if(confirm("{/literal} {$LANG.confirm_remocao_tronco} {literal}")) {
            var retorno = new Ajax.Request (
                url, {
                      method: 'GET',
                      parameters: params,
                      onComplete: resposta_tronco
                     }
            );
        }
    }

    function resposta_tronco(resp) {
         window.location.href="../src/rel_troncos.php";
    }

{/literal}
</script>