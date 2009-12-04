<?php
/*-----------------------------------------------------------------------------
 * Programa: ver_auth.php - Verifica o numero do cadeado, script chamado via Ajax pelo cadeado.js
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Rafael Bozzetti <rafael@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("conecta.php");
 require_once("../configs/config.php");

 $regras = PBX_Rules::get($_POST['id']);

 if($regras->isActive()) {
    $regras->disable();
 }else{     
    $regras->enable();
 }

 PBX_Rules::update($regras);

?>
