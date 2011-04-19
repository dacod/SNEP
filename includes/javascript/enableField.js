
function enableField(fieldSelected, fieldDisable)
{
    if (document.getElementById(fieldSelected).value == '' || document.getElementById(fieldSelected).value == null || document.getElementById(fieldSelected).checked==1){
        document.getElementById(fieldDisable).disabled = false;
        document.getElementById(fieldDisable).readOnly = false;
    }
    else {
        document.getElementById(fieldDisable).value = '';
        document.getElementById(fieldDisable).disabled = true;
        document.getElementById(fieldDisable).readOnly = true;
    }
}

