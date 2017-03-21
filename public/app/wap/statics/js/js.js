var Dialog = function(url,obj){
    var winWidth = $(window).width();
    var winHeight = $(window).height();
    var bodyHeight = $(document.body).height();
    var self = this;
    this.options = {
        url : url,
        type:'window',
        method : 'get', //请求方式
        data:'',         //数据
        height:'auto',   //dialog 高度,默认为内容高度
        title:'&nbsp;'    //dialog title 默认为空
    };
    var init = function(){
        if(obj){
            self.options = $.extend({},self.options,obj);
        }
        if(!(/http/.test(self.options.url)) && ($(self.options.url)[0].nodeType == 1)){
            $(self.options.url).eq(0).css('display','block');
            $(self.options.url).appendTo(self.contentEle);
        }else{
            ajaxLoad(self.options.url);
        }
        applyElement();
    }
    this.dialogEle = $('<div class="dialog"></div>');
    this.contentEle = $('<div class="dialog-content"></div>');

    this.close = function(){
        if(!(/http/.test(self.options.url)) && ($(self.options.url)[0].nodeType == 1)){
            $(self.options.url).appendTo(self.hide);
        }
        self.dialogEle.remove();
        self.mask.hide();
    };
    var applyElement = function(){
        if($('#J_mask').length>0){
            self.mask = $('#J_mask');
            self.hide = $('#J_dialog_hide');
            self.mask.show();
        }else{
            self.mask = $('<div id="J_mask"></div>');
            self.hide = $('<div id="J_dialog_hide" style="display:none"></div>');
            var maskHeight = Math.max(winHeight,bodyHeight);
            self.mask.css({
                'width':winWidth,
                'height':maskHeight
            });
            self.mask.appendTo($(document.body));
            self.hide.appendTo($(document.body));
        }
        if(self.options.type == 'window'){
            self.headEle = $('<div class="dialog-head"><h3></h3></div>');
            self.closeEle = $('<span class="close">X</div>')
            .on('touchend', function(e){e.preventDefault();})
            .on('tap',function(){
                self.close();
            })
            .appendTo(self.headEle);
            self.headEle.find('h3').html(self.options.title);
            self.headEle.appendTo(self.dialogEle);
        }
        self.dialogEle.data('close',self.close);
        self.contentEle.appendTo(self.dialogEle);
        self.dialogEle.appendTo($('body'));
        self.style();
    };
    this.style = function(){
        var left = 30,
            sy = window.scrollY,
            _top = (winHeight - self.dialogEle.height())/2;
        _top = _top<20 ? 20 : _top+sy;
        if(self.options.height != 'auto'){
            self.contentEle.css({
                'height':self.options.height,
                'overflow-y':'auto'
            });
        }
        self.dialogEle.css({
            'width':winWidth - 60,
            'position':'absolute',
            'left':left,
            'top':_top,
            'z-index':65535
        });
    }
    var ajaxLoad = function(url){
        $.ajax({
            'url':url,
            'data':self.options.data,
            'type' : self.options.method,
            'success':function(re){
                self.contentEle.html(re);
                self.style();
            }
        });
    }
    init();
}


// 通用倒计时，包括倒计时所在容器，倒数秒数，显示方式，回调。
function countdown(el, opt){
    opt = $.extend({
        start: 60,
        secondOnly: false,
        callback: null
    }, opt || {});
    var t = opt.start;
    var sec = opt.secondOnly;
    var fn = opt.callback;
    var d = +new Date();
    var diff = Math.round((d + t*1000) /1000);
    this.timer = timeout(el, diff, fn);

    function timeout(elem, until, fn) {
        var str = '',
            started = false,
            left = {d: 0, h: 0, m: 0, s: 0, t: 0},
            current = Math.round(+new Date() / 1000),
            data = {d: '天', h: '时', m: '分', s: '秒'};

        left.s = until - current;

        if (left.s <= 0) {
            (typeof fn === 'function') && fn();
            return;
        }
        if(!sec) {
            if (Math.floor(left.s / 86400) > 0) {
              left.d = Math.floor(left.s / 86400);
              left.s = left.s % 86400;
              str += left.d + data.d;
              started = true;
            }
            if (Math.floor(left.s / 3600) > 0) {
              left.h = Math.floor(left.s / 3600);
              left.s = left.s % 3600;
              started = true;
            }
        }
        if (started) {
          str += ' ' + left.h + data.h;
          started = true;
        }
        if(!sec) {
            if (Math.floor(left.s / 60) > 0) {
              left.m = Math.floor(left.s / 60);
              left.s = left.s % 60;
              started = true;
            }
        }
        if (started) {
          str += ' ' + left.m + data.m;
          started = true;
        }
        if (Math.floor(left.s) > 0) {
          started = true;
        }
        if (started) {
          str += ' ' + left.s + data.s;
          started = true;
        }

        elem.innerHTML = str;
        return setTimeout(function() {timeout(elem, until,fn);}, 1000);
    }
}

