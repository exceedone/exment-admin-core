/*-------------------------------------------------*/
/* tree */
/*-------------------------------------------------*/

// Drag & drop uses jQuery Nestable, same as v6: a slight drag shows the placeholder,
// dragging right nests the item under the one above, dragging left moves it out.
admin.tree = {

    elm : false,
    url : false,

    init : function(elm, settings, url){
        this.elm = elm;
        this.url = url;
        this.$tree().nestable(settings);
    },

    $tree : function(){
        return $('#' + this.elm);
    },

    delete : function(id){
        let resource_url = this.url + "/" + id;
        admin.resource.delete_do(resource_url);
    },

    save : function(){
        let order = this.$tree().nestable('serialize');
        admin.ajax.loadPost(this.url, {_order: JSON.stringify(order)});
    },

    collapse : function(){
        this.$tree().nestable('collapseAll');
    },

    expand : function(){
        this.$tree().nestable('expandAll');
    }
}
