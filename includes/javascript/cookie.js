   function setCookie(c_name,value,expiresecs) {
       var exdate = new Date();
       exdate.setTime(exdate.getTime()+ ((expiresecs) ? expiresecs*1000: 0));
       document.cookie = c_name+ "=" +escape(value)+
           ((expiresecs==null) ? "" : ";expires="+exdate.toGMTString());
   }

   function getCookie(c_name) {
       if (document.cookie.length > 0)  {
           c_start = document.cookie.indexOf(c_name + "=");
           if (c_start != -1) {
               c_start = c_start + c_name.length + 1;
               c_end = document.cookie.indexOf(";", c_start);
              if (c_end == -1) c_end = document.cookie.length;
               return unescape(document.cookie.substring(c_start,c_end));
           }
       }
       return "";
    }
    function delCookie() {

       var cookies = unescape(document.cookie.split(";"));

       alert(cookies);

    }
