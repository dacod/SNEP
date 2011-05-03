{*
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 *}
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
<h2>{$LANG.examples}</h2>
<p><a href="../doc/manual/exemplos.html">{$LANG.click_for_examples}</a></p>
<h2>{$LANG.app_list}</h2>
<p>{$LANG.click_for_details}</p>
<ol>
    {section name=apps loop=$APP_LIST}
        <li><a href="?acao=app&amp;app={$APP_LIST[apps]}">{$APP_LIST[apps]}</a><br />{$APP_DESC[apps]}</li>
    {/section}
</ol>
</body>
</html>