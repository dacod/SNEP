{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_sounds.tpl - Template usado pelo arquivo rel_sounds.php
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
{config_load file="../includes/setup.conf" section="ambiente"}
{math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
<table acellspacing="0" cellpadding="0" border="0" align="center"  >   
   <thead>
      <tr>
         <td rowspan="2" class="esq" width="15%">{$LANG.filename}</td>
         <td rowspan="2" class="esq">{$LANG.desc}</td>
         <td rowspan="2" class="cen">{$LANG.type}</td>
         <td rowspan="2" class="cen">{$LANG.updated}</td>
         <td colspan="2" class="cen">{$LANG.listen}</td>
         <td rowspan="2" class="cen" colspan="3" width="20%">{$LANG.actions}</td>
      </tr>
      <tr>
         <td class="cen">{$LANG.backup}</td>
         <td class="cen">{$LANG.actual}</td>
      <tr>
   </thead>   
   {section name=sounds loop=$DADOS max=#linelimit# start=$INI}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
      <td class="esq">{$DADOS[sounds].arquivo}</td>
      <td class="esq">{$DADOS[sounds].descricao}</td>
      <td class="cen">{$DADOS[sounds].tipo}</td>
      <td class="cen">{$DADOS[sounds].data}</td>      
      <td class="cen">
         {if $DADOS[sounds].backup}
            <a href="#">        
            <img src="../imagens/ouvir.png" alt="Ouvir" width="24" height="24" hspace="0" vspace="0" style="border: none; cursor : hand;" onclick="DHTMLSound('{$DADOS[sounds].arq_backup}')"/>
            </a>
         {else}
            N.D.
         {/if}
      </td>
      <td class="cen">
         {if $DADOS[sounds].atual}
            <a href="#">
            <img src="../imagens/ouvir.png" alt="Ouvir" width="20" height="20" hspace="0" vspace="0" style="border: none; cursor : hand;"  onclick="DHTMLSound('{$DADOS[sounds].arq_atual}')"/>
            </a>
         {else}
            N.D.
         {/if}
      </td>
      <form name="formulario" method="post" action="../src/sounds.php" enctype="multipart/form-data">
         <input type="hidden" name="arquivo" value="{$DADOS[sounds].arquivo}" />
         <input type="hidden" name="backup" value="{$DADOS[sounds].arq_backup}" />
         <input type="hidden" name="atual" value="{$DADOS[sounds].arq_atual}" />
         <input type="hidden" name="tipo" value="{$DADOS[sounds].tipo}" />
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/sounds.php?acao=alterar&amp;arquivo={$DADOS[sounds].arquivo}&amp;backup={$DADOS[sounds].arq_backup}&amp;atual={$DADOS[sounds].arq_atual}&amp;secao={$SECAO}&amp;tipo={$DADOS[sounds].tipo}">
                 <img src="../imagens/edit.png" alt="{$LANG.change}" width="16" height="16" />
               </a>
            </acronym>
         </td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.backbackup}">
               <a href="../src/sounds.php?acao=voltar&amp;arquivo={$DADOS[sounds].arquivo}&amp;backup={$DADOS[sounds].arq_backup}&amp;atual={$DADOS[sounds].arq_atual}&amp;secao={$SECAO}&amp;tipo={$DADOS[sounds].tipo}">
                    <img src="../imagens/refresh.png" alt="{$LANG.backbackup}" width="16" height="16"/>
               </a>
            </acronym>
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <a href="../src/sounds.php?acao=excluir&amp;arquivo={$DADOS[sounds].arquivo}&amp;backup={$DADOS[sounds].arq_backup}&amp;atual={$DADOS[sounds].arq_atual}&amp;secao={$SECAO}&amp;tipo={$DADOS[sounds].tipo}">
                    <img src="../imagens/delete.png" alt="{$LANG.exclude}" width="16" height="16" />
               </a>
            </acronym>          
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
<span id=dummyspan></span>
{ include file="rodape.tpl }
<script language="javascript" type="text/javascript">
function EvalSound(soundobj) {ldelim}
  var thissound=document.getElementById(soundobj);
  thissound.Play();
{rdelim}

function DHTMLSound(surl) {ldelim}
  document.getElementById('dummyspan').innerHTML="<embed src='"+surl+"' hidden=true autostart=true loop=false>";
{rdelim}
</script>