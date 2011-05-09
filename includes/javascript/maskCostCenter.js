var maskCostCenter = Class.create({

    initialize: function(obj) {
        this.obj = obj;
        $(this.obj).observe('keyup', this.format.bind(this));
        $(this.obj).observe('blur', this.finish.bind(this));
    },
    format: function() {
        var length = $F(this.obj).length;

        if(length == 1) {
            $(this.obj).value = $F(this.obj) + '.'
        }
        if(length == 4) {
            $(this.obj).value = $F(this.obj) + '.'
        }
        if(length >= 7) {
            $(this.obj).value = $F(this.obj).substr(0,7)
        }
    },
    finish: function() {
        dot = $F(this.obj).substr( $F(this.obj).length-1, $F(this.obj).length );
        if(dot == '.') {
            $(this.obj).value = $F(this.obj).substr(0, $F(this.obj).length-1 );
        }
    }
});