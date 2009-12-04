{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_ccustos.tpl - Relacao dos Centros de Cutos
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
         <td class="esq" width="5%">{$LANG.id}</td>
         <td class="esq">{$LANG.name}</td>
         <td class="cen">{$LANG.type}</td>
         <td class="esq">{$LANG.desc}</td>
         <td class="cen" colspan="2" width="30px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=ccustos loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="esq">{$DADOS[ccustos].codigo}</td>
         <td>{$DADOS[ccustos].nome}</td>
         {assign var="tipo_cc" value=$DADOS[ccustos].tipo}
         <td class="cen">{$TIPOS_CCUSTOS.$tipo_cc}</td>
         <td class="esq">{$DADOS[ccustos].descricao}</td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/ccustos.php?acao=alterar&amp;codigo={$DADOS[ccustos].codigo}"><img src="../imagens/edit.png" alt="{$LANG.change}" border="0" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <img src="../imagens/delete.png" alt="{$LANG.exclude}" border="0" onclick="remove_ccustos('{$DADOS[ccustos].codigo}');"/>
            </acronym>          
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }

<script type="text/javascript">
{literal}

    /* Confirmacao e remocao de regras de negocio */
    function remove_ccustos(id) {
        var url = '../src/ccustos.php';
        var params = 'acao=excluir'+'&codigo='+id;
        

        if(confirm("{/literal} {$LANG.confirm_remocao_ccustos} {literal}")) {
            var retorno = new Ajax.Request (
                url, {
                      method: 'post',
                      parameters: params,
                      onComplete: resposta_ccustos
                     }
            );
        }
    }

    function resposta_ccustos(resp) {
          window.location.href="../src/rel_ccustos.php";
    }

{/literal}
</script>