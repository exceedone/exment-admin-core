/*-------------------------------------------------*/
/* forms */
/*-------------------------------------------------*/

admin.tree = {

    elm : false,
    url : false,
    sortable : false,

    sortableDefaults : {
        group: 'nested',
        animation: 150,
        fallbackOnBody: false,
        swapThreshold: 0.65
    },

    init : function(elm, settings,url){

        this.url = url;
        this.elm = elm;
        this.sortable = false;

        let nestedSortables = document.querySelectorAll("#"+elm+" ol");
        let sortableSettings = merge_default(this.sortableDefaults,settings);

        for (var i = 0; i < nestedSortables.length; i++) {
            let setSortable = new Sortable(nestedSortables[i], sortableSettings);
            if (!this.sortable){
                this.sortable = setSortable;
            }
        }

        let container = document.getElementById(elm);
        if (container) {
            container.addEventListener('click', function(e) {
                let btn = e.target.closest('button[data-action="collapse"], button[data-action="expand"]');
                if (!btn) return;
                let action = btn.dataset.action;
                let li = btn.closest('li.dd-item');
                let ol = li ? li.querySelector(':scope > ol.dd-list') : null;
                if (!ol) return;

                if (action === 'collapse') {
                    li.classList.add('dd-collapsed');
                    ol.style.display = 'none';
                    let colBtn = li.querySelector(':scope > button[data-action="collapse"]');
                    let expBtn = li.querySelector(':scope > button[data-action="expand"]');
                    if (colBtn) colBtn.style.display = 'none';
                    if (expBtn) expBtn.style.display = '';
                } else {
                    li.classList.remove('dd-collapsed');
                    ol.style.display = '';
                    let colBtn = li.querySelector(':scope > button[data-action="collapse"]');
                    let expBtn = li.querySelector(':scope > button[data-action="expand"]');
                    if (colBtn) colBtn.style.display = '';
                    if (expBtn) expBtn.style.display = 'none';
                }
            });
        }
    },

    delete : function(id){

        let resource_url = this.url + "/"+id;
        admin.resource.delete_do(resource_url);

    },

    save : function(){
        let order = this.toArrayNested();
        admin.ajax.loadPost(this.url,{_order:JSON.stringify(order)});
    },

    toArrayNested:function(){
        let top = document.querySelector("#"+this.elm+" > ol");
        return this.getChildren(top);
    },

    getChildren : function(elm){
        let arr = [];
        elm.querySelectorAll(":scope > li").forEach(li=>{
            let obj = {id:li.dataset.id};
            let ol = li.querySelector(":scope > ol");
            if (ol){
                obj.children = this.getChildren(ol,arr);
            }
            arr.push(obj);
        })
        return arr;
    },

    collapse : function(){
        let container = document.getElementById(this.elm);
        if (!container) return;
        container.querySelectorAll('li.dd-item').forEach(li => {
            let ol = li.querySelector(':scope > ol.dd-list');
            if (!ol) return;
            li.classList.add('dd-collapsed');
            ol.style.display = 'none';
            let colBtn = li.querySelector(':scope > button[data-action="collapse"]');
            let expBtn = li.querySelector(':scope > button[data-action="expand"]');
            if (colBtn) colBtn.style.display = 'none';
            if (expBtn) expBtn.style.display = '';
        });
    },

    expand : function(){
        let container = document.getElementById(this.elm);
        if (!container) return;
        container.querySelectorAll('li.dd-item').forEach(li => {
            let ol = li.querySelector(':scope > ol.dd-list');
            if (!ol) return;
            li.classList.remove('dd-collapsed');
            ol.style.display = '';
            let colBtn = li.querySelector(':scope > button[data-action="collapse"]');
            let expBtn = li.querySelector(':scope > button[data-action="expand"]');
            if (colBtn) colBtn.style.display = '';
            if (expBtn) expBtn.style.display = 'none';
        });
    }
}