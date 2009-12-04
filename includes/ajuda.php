<?php
// Parsing the received variable
$script = isset($_GET['script']) ? mysql_escape_string($_GET['script']) : "default";
$script = basename($script, ".php");
if(file_exists("../doc/manual/$script.html"))
    header("location: ../doc/manual/$script.html");
else
    header("location: ../doc/manual/index.html");
?>