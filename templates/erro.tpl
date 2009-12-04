{* Smarty *}
{*-----------------------------------------------------------------------------
 * Programa: erro.tpl - Exibe erro do Sistema
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *----------------------------------------------------------------------------- *}
 {if $HEADER}
    {include file="cabecalho.tpl"}
 {/if}
 <table align="center" class="mensagem" cellpadding="0" cellspacing="0">
    <thead>
       <tr>
          <td class="mensagem">{$LANG.warning}</td>
       </tr>
    </thead>
    <tr>
       <td height="50" class="mensagem">
          {$ERROR}
       </td>
    </tr>
    <tr>
       <td class="subtable" align="right" style="padding: 5px;">
          <form name="ok" id="ok">
          {if $RET < 0}
             <input type="button" class="button" value="Ok" onClick="history.go({$RET});"  />
          {else}
             <input type="button" class="button" value="Ok" />
          {/if}
          <div class="buttonEnding"></div>
          </form>
       </td>
    </tr>
 </table>