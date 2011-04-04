
function enableField(fieldSelected, fieldDisable)
{
    if (document.getElementById(fieldSelected).value == '' || document.getElementById(fieldSelected).value == null){
        document.getElementById(fieldDisable).disabled = false;
    }
    else {
        document.getElementById(fieldDisable).value = '';
        document.getElementById(fieldDisable).disabled = true;
    }
}

