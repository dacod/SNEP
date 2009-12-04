{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_ramais.tpl - Relacao das Remais/Troncos  Cadastrados       
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="ambiente"}
{config_load file="../includes/setup.conf" section="cores"}
{math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >
   <thead>
      <tr>
         <td class="cen">{$LANG.ramal}</td>
         <td class="cen" width="24%">{$LANG.extendname}</td>
         <td class="cen">{$LANG.channel}</td>
         <td class="cen">{$LANG.group}</td>
         <td class="cen">{$LANG.vinculo}</td>
         <td class="cent">{$LANG.usevoicemail}</td>
         <td class="cen" colspan="3" width="20px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=ramais loop=$DADOS max=#linelimit# start=$INI}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[ramais].name}</td>
         <td>{$DADOS[ramais].callerid}</td>
         <td class="cen">{$DADOS[ramais].canal}</td>
         <td class="esq">{$DADOS[ramais].group}</td>
         <td class="esq">{$DADOS[ramais].vinculo}</td>
         {assign var="usa_vc" value=$DADOS[ramais].usa_vc}
         <td class="cen">{$OPCAO_YN.$usa_vc}</td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/ramais.php?acao=alterar&amp;id={$DADOS[ramais].id}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center">
         {if $DADOS[ramais].name != 'admin'}
            <acronym title="{$LANG.exclude}">
               <img src="../imagens/delete.png" alt="{$LANG.exclude}" onclick="remove_ramal('{$DADOS[ramais].id}','{$DADOS[ramais].canal}');"/>
            </acronym>
         {/if}
         </td>
         <form name="formulario" method="post"  action="../configs/permissoes.php" enctype="multipart/form-data">
         <input type="hidden" name="id" value="{$DADOS[ramais].id}" />
         <input type="hidden" name="nome" value="{$DADOS[ramais].callerid}" />
         <td valign="middle" align="center">
         {if $DADOS[ramais].name != 'admin'}
            <acronym title="{$LANG.permitions}">
               <input type="image" src="../imagens/permitions.png" border="0" alt="{$LANG.permitions}"  name="acao" value="permissoes"/>
            </acronym>
         {/if}
         </td>       
         </form>
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
    function remove_ramal(id,canal) {
        var url = '../src/ramais.php';
        var params = 'acao=excluir&id='+id+'&canal=';

        if(confirm("{/literal} {$LANG.confirm_remocao_ramal} {literal}")) {
            var retorno = new Ajax.Request (
                url, {
                      method: 'post',
                      parameters: params,
                      onComplete: resposta_ramal
                     }
            );
        }
    }

    function resposta_ramal(resp) {
         window.location.href="../src/rel_ramais.php";
    }

{/literal}
</script>