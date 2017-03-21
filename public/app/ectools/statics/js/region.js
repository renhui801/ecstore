var region_sel = {
    addOpt:function(select,data){
        var html = ['<option value="_NULL_">请选择</option>'];
        if(data) {
            data.forEach(function(v){
                var attrs = v.split(':');
                var lv = '';
                if(attrs[2]) {
                    lv = ' data-level-index="' + attrs[2] + '"';
                }
                html.push('<option value="' + attrs[1] + '"' + lv + '>' + attrs[0] + '</option>');
            });
            if(select) {
                select.set('html', html.join('')).show();
            }
        }
        else if(select) {
            select.innerHTML = html[0];
        }
        return this;
    },
    attachEvent:function(){
        var _this = this;
        this.sels.addEvent('change', function(e){
            _this.changeResponse(this);
        });
    },
    changeResponse:function(cur_sel, opt){
        var _this = this;
        var level = this.set(cur_sel, opt),
            elems = cur_sel.getAllNext();

        if(!cur_sel.getSelected()[0].getAttribute('data-level-index') && this.callback) {
            _this.callback(_this.sels);
        }
        elems.each(function(el,i){
            if(i || elems.length == 1) el.hide().empty();
        })
        this.addOpt(cur_sel.getNext(),level).setValue();
    },
    setValue:function(){
        var k = [],str,id;
        this.sels.each(function(el){
            var opt = el.getSelected(), t = opt.get('text'), v = opt.get('value');
            if(opt.length && v!='_NULL_'){
                k.push(t);
                id=v;
            }
        });

        if(k.length) {
            str = this.sels.getPrevious('*[package]').get('package') + ":" + k.join('/');
            this.elem.getElement('input').value=str+':'+id;
        }
        else {
            this.elem.getElement('input').value = '';
        }
    },
    isAddSel:function(select){
        select.getAllNext().each(function(el){el && el.empty().hide();});
        // select.getNext() && select.getNext().empty();
    },
    set:function(target,opt){
        if(opt) {
            opt.selected = true;
        }
        else {
            opt = target.options[target.selectedIndex];
        }
        var index = opt.getAttribute('data-level-index');
        this.index = parseInt(target.getAttribute('data-level-index')) + 1;
        var data = this.data[this.index];
        return data ?data[index]:false;
    },
    init:function(container,func_callback, region_Data){
        this.callback = window[func_callback];
        this.elem = container;
        this.sels = this.elem.getElements('select');
        this.data = region_Data;
        this.addOpt(this.sels[0],this.data[0]).isAddSel(this.sels[0].show());
        this.attachEvent();
    }
};
