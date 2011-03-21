var SelectBox = Class.create({

    initialize: function(name) {
        this.name = name;        
        $(this.name +'_add_bt').observe('click', this.add.bind(this));
        $(this.name +'_remove_bt').observe('click', this.remove.bind(this));
    },
    add: function() {
        var options = $$('select#'+this.name+'_box option');
        for (var i = 0; i < options.length; i++) {
            if(options[i].selected) {
                html = '<option value="'+ options[i].value +'">'+ options[i].text +'</option>';
                $(this.name +'_box_add').insert(html);
                options[i].remove();
            }
        }
    },
    remove: function() {
        var options = $$('select#'+this.name+'_box_add option');
        for (var i = 0; i < options.length; i++) {
            if(options[i].selected) {
                html = '<option value="'+ options[i].value +'">'+ options[i].text +'</option>';
                $(this.name +'_box').insert(html);
                options[i].remove();
            }
        }
    }
});