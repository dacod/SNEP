function update_channel_list() {
    selected_board = document.getElementById('khomp_boards').value;

    new Ajax.Request('../src/khomp_channels.php', {
        method:'get',
        parameters: {value: 'channels', board: selected_board},
        requestHeaders: {Accept: 'application/json'},
        onSuccess: function(transport){
            document.getElementById('khomp_channels').innerHTML = transport.responseText;
        }
    });
}

function load_khomp(placa, canal) {
    new Ajax.Request('../src/khomp_channels.php', {
        method:'get',
        parameters: {value: 'channels', board: placa, selected: canal},
        requestHeaders: {Accept: 'application/json'},
        onSuccess: function(transport){
            document.getElementById('khomp_channels').innerHTML = transport.responseText;
        }
    });
}
