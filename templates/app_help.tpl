{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: app_help.tpl - Application Help
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Henrique Grolli Bassotto <henrique@opens.com.br>
 * ---------------------------------------------------------------------------- *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
   <head>
      <title>{$TITULO}</title>
      <link rel="stylesheet" href="../css/app_help.css" type="text/css" />
      <link rel="stylesheet" href="../css/{$CSS_TEMPL}.css" type="text/css" />
      <meta http-equiv="Content-Type" content="text/html; {$LANG.ISO}" />
      <meta http-equiv="Content-Script-Type" content="text/javascript" />
      <meta http-equiv="imagetoolbar" content="false" />
      <META http-equiv="Expires" content="Fri, 25 Dec 1980 00:00:00 GMT" />
      <META http-equiv="Last-Modified" content="{php}gmdate('D, d M Y H:i:s'){/php} GMT" />   
      <META http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
      <META http-equiv="Cache-Control" content="post-check=0, pre-check=0" />
      <META http-equiv="Pragma" content="no-cache" />
      <meta name="copyright" content="Opens Tecnologia&reg;" />
   </head>
<body>
<h2>{$TITULO}</h2>
<a href="../src/app_help.php">{$LANG.back}</a>
<p>{$DESCRIPTION}</p>
</body>
</html>