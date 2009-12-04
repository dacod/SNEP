{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: lista_astsounds.tpl - Lista Arquivos de Som do Asterisk
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- *}
 {config_load file="../includes/setup.conf" section="cores"}
 <link rel="stylesheet" href="../css/{$CSS_TEMPL}.css" type="text/css" /> 
 <table style="width: 100%">
    {foreach name=files item=arquivo from=$dt_files}
       <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
          <td>             
             <a href="#" class="links_disable"  onClick="define_arquivo('{$arquivo}')">{$arquivo}</a>
          </td>
       </tr>
    {/foreach}
 </table>
 <script language="javascript" type="text/javascript">
 function define_arquivo(arquivo) {ldelim}
    var desc=parent.document.formulario.descricao.value;
    parent.document.formulario.reset() ;
    parent.document.formulario.arquivo.value=arquivo ;
    parent.document.formulario.descricao.value=desc;
    parent.document.formulario.descricao.focus();
 {rdelim}
 </script>