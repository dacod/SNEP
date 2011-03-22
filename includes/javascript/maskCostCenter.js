var maskCostCenter = Class.create({

    initialize: function(obj) {
        this.obj = obj;
        $(this.obj).observe('keyup', this.format.bind(this));
    },
    format: function() {
        var length = $F(this.obj).length;

        if(length == 1) {
            $(this.obj).value = $F(this.obj) + '.'
        }
        if(length == 4) {
            $(this.obj).value = $F(this.obj) + '.'
        }
        if(length == 7) {
            $(this.obj).value = $F(this.obj) + '.'
        }
    }
});