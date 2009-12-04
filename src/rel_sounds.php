<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_sounds.php - Lista de Sons do Asterisk cadastrados no SNEP
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php"); 
 ver_permissao(50) ;
 unset($_SESSION['secao']);
 $titulo = $LANG['menu_config']." -> ".$LANG['menu_sounds'] ;
 // SQL padrao
 $sql = "SELECT arquivo,descricao,tipo,date_format(data,'%d/%m/%Y %h:%i:%s') as data FROM sounds WHERE tipo != 'MOH'" ;
 // Opcoes de Filtrros
 $opcoes = array( "arquivo" => $LANG['name'],
                  "descricao" => $LANG['desc'],
                  "tipo" => $LANG['filetype']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) 
    $sql .= " AND ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 $sql .= " ORDER BY arquivo" ;
 // Executa acesso ao banco de Dados
 try {
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }
 // Varre diretorio de Sons e Backup para relacionar arquivo a ser ouvido
 $dir_sounds = SNEP_PATH_SOUNDS ;
 foreach ($row as $key=>$val) {
    $tmp = array("atual"=>False,"backup"=>False,
                 "arq_atual"=>"","arq_backup"=>"") ;
    if (file_exists($dir_sounds.$val['arquivo'])) {
       $tmp['atual'] = True ;
       $tmp['arq_atual'] = $dir_sounds.$val['arquivo'] ;
    }
    if (file_exists($dir_sounds."backup/".$val['arquivo'])) {
       $tmp['backup'] = True ;
       $tmp['arq_backup'] = $dir_sounds."backup/".$val['arquivo'] ;
    }
    $row[$key] += $tmp ;
 }
 $tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
 for ($i = 1 ; $i <= $tot_pages ; $i ++ )
     $paginas[$i] = $i;
 // Define variaveis do template          
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('TOT',$tot_pages);
 $smarty->assign ('PAGINAS',$paginas) ;
 $smarty->assign ('INI',1);
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/sounds.php", "display"  => $LANG['register']." ".$LANG['menu_sounds']));
 // Exibe template
 display_template("rel_sounds.tpl",$smarty,$titulo);
 ?>