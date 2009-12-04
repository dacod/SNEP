{* Smarty *}
{* ----------------------------------------------------------------------------
 * Template: links_errors.tpl - Monitoramento de Erros de Links Khomp.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio H. Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- *}
 <table>
    <thead>
       <tr>
          <td class="esq" >{$LANG.error_type}</td>
          {assign var="colunas" value=1}
          {foreach name=boards from=$CANAIS key=board_key item=board_item}
             {foreach name=links from=$CANAIS[$board_key] key=link_key item=link_item}
                <td>
                   {$LANG.board}: {$board_key} , {$LANG.link}: {$link_key}
                   <br />
                   {$link_item}
                </td>
                {assign var="colunas" value="`$colunas+1`"}
             {/foreach}
          {/foreach}

       </tr>
    </thead>
    {foreach name=status from=$STATUS key=sts_key item=sts_item}
       <tr>
          <td>{$sts_key}</td>
          {foreach name=sts_board from=$STATUS[$sts_key] key=sb_key item=sb_item}
             {foreach name=sts_link from=$STATUS[$sts_key][$sb_key] key=sl_key item=sl_item}
                <td class="cen">{$sl_item}</td>
             {/foreach}
          {/foreach}
       </tr>
    {/foreach}
    <tr>
     <td colspan="{$colunas}" class="subtable" align="center" height="38px" valign="middle">
        <form name="formulario"  method="POST" enctype="multipart/form-data" action="{$smarty.server.SCRIPT_NAME}?acao=zerar" >
           <input class="button" type="submit" id="resetar"  value="{$LANG.zerocounter}">
           <div class="buttonEnding"></div>
        </form>
     </td>
  </tr>

 </table>
 { include file="rodape.tpl }

