// vim: set et st=4 ts=4 sts=4:
/*------- Author: Tyler Chao===tylerchao.sh@gmail.com -------*/

//商品详情图片放大镜
var AlbumZoom=new Class({
    Implements: [Events, Options],
    options: {
        wrap: '.product-album-preview',
        preview: '.album-preview-container',
        zooms: '.album-zooms-container',
        handle: '.album-zooms-handle',
        bigImages: '.album-big-image',
        midImages: '.album-mid-image',
        thumbs: '.product-album-thumb',
        thumbEvent: 'mouseover',
        zoomable: true,
        zoomsSize:{
            x:400,
            y:300
        }
        // position: 'right'
    },
    initialize: function(container, options){
        container = this.container = $(container);
        this.setOptions(options);
        // this.zooms = [];
        this.album_big_img = $$(this.options.bigImages);
        this.album_mid_img = $$(this.options.midImages);
        this.recalculating = false;
        this.wrap = this.container.getElement(this.options.wrap);
        this.preview = this.container.getElement(this.options.preview);
        this.previewImg = this.preview.getElement('img');
        this.zooms = $(this.options.zooms) || $$(this.options.zooms)[0];
        this.zoomsImg = this.zooms ? this.zooms.getElement('img') : null;
        this.thumbs = this.container.getElement(this.options.thumbs);
        this.zoomsImgX = 0;
        this.zoomsImgY = 0;
        this.previewImgX = 0;
        this.previewImgY = 0;
        this.handleX = 20;
        this.handleY = 20;
        this.positionX = 0;
        this.positionY = 0;
        //loading
        var loadingText = '';
        var loadingImg = '';
        var loading = this.container.getElement('img.loading');
        if (loading.alt != '') loadingText = loading.alt;
        loadingImg = loading.src;

        this.loadingCont = new Element('div.loading', {
            html: '<img alt="loading..." src="' + loadingImg + '" /> ' + loadingText,
            styles: {
                visibility: 'hidden'
            }
        }).inject(this.wrap);

        this.baseuri = '';
        this.safariOnLoadStarted = false;
        // this.zooms.push(this);
        this.checkcoords_ref = this.checkcoords.bind(this);
        this.findZooms();
    },
    findSelectors: function() {
        var li = this.thumbs.getElements('li');
        if(!li.length) return;
        var ul = this.thumbs.getElement('ul');
        var prev = this.thumbs.getElement('.prev');
        var next = this.thumbs.getElement('.next');
        var liX = li[0].getSize().x + li[0].getPatch('margin').x;
        var ulX = liX * li.length;
        ul.setStyle('width', ulX);
        var thumbsX = this.thumbs.getSize().x;
        if(ulX > thumbsX) next.removeClass('over');
        $$(prev,next).addEvent('mousedown', function(e){
            var ml = parseInt(ul.getStyle('margin-left')) || 0;
            if(this.hasClass('over')) return;
            new Fx.Tween(ul, {
                duration: 100,
                onComplete: function(){
                    ml = parseInt(ul.getStyle('margin-left'));
                    if(ml < 0) prev.removeClass('over');
                    else if(ml >= 0) prev.addClass('over');
                    if(ml <= thumbsX - ulX) next.addClass('over');
                    else if(ml > thumbsX - ulX) next.removeClass('over');
                }
            }).start('margin-left', ml, ml + (this.hasClass('backward') ? -1 : 1) * liX);
        });
        var aels = this.thumbs.getElements('a[rev]');
        var self = this;
        aels.each(function(el, i){
            el.addEvent('click',function(e) {
                e.stop();
            });
            el.addEvent(self.options.thumbEvent, function(e){
                this.getParent('li').addClass('active').getSiblings('.active').removeClass('active');
                self.replaceZoom(el);
            });
            if(self.options.zoomable) {
                self.album_big_img[i] = self.album_big_img[i] || new Element('img' + self.options.bigImages,{
                    src: el.href
                }).inject(self.container);
            }
            self.album_mid_img[i] = self.album_mid_img[i] || new Element('img' + self.options.midImages,{
                src: el.rev
            }).inject(self.container);
        });
    },
    findZooms: function(){
        var preview = this.preview;
        var previewImg = this.previewImg;
        if (!previewImg) return;

        preview.addEvent('click', function(e) {
            e.stop();
        });
        if (Browser.ie) {
            preview.setStyle('z-index', 0);
        }
        if(this.options.zoomable) {
            this.zooms = this.zooms || new Element('div'+ this.options.zooms, {styles:{
                width: this.options.zoomsSize.x,
                height: this.options.zoomsSize.y
            }}).inject(this.container);
            this.zoomsImg = this.zoomsImg || new Element('img', {
                src: preview.href
            }).inject(this.zooms);
            this.initZoom();
        }
        this.findSelectors();
    },
    initHandle: function() {
        this.handle = new Element('div' + this.options.handle);
        this.recalculateHandleDimensions();
        this.handle.inject(this.preview);
        this.preview.unselectable = 'on';
        this.preview.onselectstart = Function.from(false);
    },
    initZoomsContainer: function() {
        var src = this.zoomsImg.src;
        this.zooms.innerHTML = '';

        this.zoomsImg = new Element('img',{
            src: src
        }).inject(this.zooms);
    },
    initZoom: function() {
        if (this.loadingCont != null && !this.zoomsImg.complete && this.previewImg.width != 0 && this.previewImg.height != 0) {
            this.loadingCont.setStyles({
                left: (this.wrap.getSize().x - this.loadingCont.getSize().x) / 2,
                top: (this.wrap.getSize().x - this.loadingCont.getSize().y) /2,
                visibility: 'visible'
            });
        }
        if (Browser.safari) {
            if (!this.safariOnLoadStarted) {
                this.zoomsImg.addEvent('load', this.initZoom.bind(this));
                this.safariOnLoadStarted = true;
                return;
            }
        } else {
            if (!this.zoomsImg.complete || !this.previewImg.complete) {
                this.initZoom.delay(100, this);
                return;
            }
        }
        this.zoomsImgX = this.zoomsImg.width;
        this.zoomsImgY = this.zoomsImg.height;
        this.previewImgX = this.previewImg.width;
        this.previewImgY = this.previewImg.height;
        if (this.zoomsImgX == 0 || this.zoomsImgY == 0 || this.previewImgX == 0 || this.previewImgY == 0) {
            this.initZoom.delay(100, this);
            return;
        }
        if (this.loadingCont != null) this.loadingCont.setStyle('visibility', 'hidden');
        // this.preview.setStyle('width', this.previewImg.width);
        //计算大图位置
        this.zoomsX = this.wrap.getPosition().x + this.wrap.getSize().x + 10;
        this.zooms.setStyles({
            'left': this.zoomsX,
            'top': this.wrap.getPosition().y
        });
        if (this.handle) {
            this.recalculateHandleDimensions();
            return;
        }
        this.initZoomsContainer();
        this.initHandle();
        document.addEvent('mousemove', this.checkcoords_ref);
        this.preview.addEvent('mousemove', this.mousemove.bind(this));
    },
    replaceZoom: function(ael) {
        if (ael.rev == this.previewImg.src) return;
        this.previewImg.src = ael.rev;
        if(this.options.zoomable) {
            var newZoomsImage = new Element('img', {
                src:ael.href
            });
            newZoomsImage.replaces(this.zoomsImg);
            this.zoomsImg = newZoomsImage;

            this.safariOnLoadStarted = false;
            this.initZoom();
        }
    },
    stopZoom:function() {
        document.removeEvent('mousemove', this.checkcoords_ref);
    },
    checkcoords: function(e) {
        var r = e.page;
        var x = r.x;
        var y = r.y;
        var s = this.previewImg.getPosition();
        var previewX = s.x;
        var previewY = s.y;
        if (x > previewX + this.previewImgX || x < previewX || y > previewY + this.previewImgY || y < previewY) {
            this.hiderect();
            return false;
        }
        if (Browser.ie) {
            this.preview.setStyle('z-index', 1);
        }
        return true;
    },
    mousemove: function(e) {
        e.stop();
        if (this.recalculating || !this.checkcoords(e)) {
            return;
        }
        this.recalculating = true;
        var previewImg = this.previewImg;
        var r = e.page;
        var x = r.x;
        var y = r.y;
        var s = previewImg.getPosition();
        var previewX = s.x;
        var previewY = s.y;
        this.positionX = x - previewX;
        this.positionY = y - previewY;
        if ((this.positionX + this.handleX / 2) >= this.previewImgX) {
            this.positionX = this.previewImgX - this.handleX / 2
        }
        if ((this.positionY + this.handleY / 2) >= this.previewImgY) {
            this.positionY = this.previewImgY - this.handleY / 2
        }
        if ((this.positionX - this.handleX / 2) <= 0) {
            this.positionX = this.handleX / 2
        }
        if ((this.positionY - this.handleY / 2) <= 0) {
            this.positionY = this.handleY / 2
        }
        setTimeout(this.showrect.bind(this), 16);
    },
    showrect: function() {
        var posX = parseInt(this.positionX - this.handleX / 2);
        var posY = parseInt(this.positionY - this.handleY / 2);
        this.handle.setStyles({
            left:posX,
            top:posY,
            visibility:'visible'
        });
        perX = posX * (this.zoomsImgX / this.previewImgX);
        perY = posY * (this.zoomsImgY / this.previewImgY);
        this.zoomsImg.setStyles({
            left: -perX,
            top: -perY,
            visibility:'visible'
        });
        this.zooms.setStyles({
            left: this.zoomsX,
            visibility: 'visible'
        });
        this.recalculating = false;
    },
    hiderect: function() {
        if (this.handle) {
            this.handle.setStyle('visibility', 'hidden');
        }
        this.zooms.setStyles({
            left: -10000,
            visibility: 'hidden'
        });
        if (Browser.ie) {
            this.preview.setStyle('z-index', 0);
        }
    },
    recalculateHandleDimensions: function() {
        this.handleX = (this.zooms.getSize().x - 3) / (this.zoomsImgX / this.previewImgX);
        this.handleY = (this.zooms.getSize().y - 3) / (this.zoomsImgY / this.previewImgY)
        if (this.handleX > this.previewImgX) {
            this.handleX = this.previewImgX;
        }
        if (this.handleY > this.previewImgY) {
            this.handleY = this.previewImgY;
        }

        this.handle.setStyles({
            width: this.handleX,
            height: this.handleY
        });
    }
});

//商品详情内容滚动加载
var LayoutRequest = new Class({
    Implements: [Events,Options],
    options:{
        threshold:50,
        loadCls:'loading',
        errorCls:'error',
        completeCls:'',
        onRequest:function(item){
            var el, loadCls=this.options.loadCls;
            if(el = item.update) el.addClass(loadCls);
            if(el = item.append) new Element('div',{'data-load':item.name,'class':loadCls}).inject(el);
        },
        onFailure:function(queue){
            var el,loadCls=this.options.loadCls, errorCls=this.options.errorCls;
            if((el = queue.append)) el = el.getElement('div[data-load='+queue.name+']');
            if(queue.update) el = queue.update;
            el.removeClass(loadCls);
            //new Element('div',{'data-load':queue.name,'class':errorCls}).inject(el).set('html','请求出错');
        },
        onComplete:function(queue){
            var el,loadCls=this.options.loadCls,errorCls = this.options.errorCls;
            if((el = queue.append)) el= el.getElement('div[data-load='+queue.name+']');
            el && el.destroy();
            if((el = queue.update)) el.removeClass(loadCls).removeClass(errorCls);
        },
        onSuccess:function(){}
    },
    initialize:function(ajax_queue,options){
        if(!ajax_queue.length)return;
        this.sync_queue = ajax_queue;

        this.setOptions(options).fireEvent('load');
        this.initEvent();
    },
    initEvent:function(){
        var timer , self = this; this.cur_sync = {},win = window;
        //win.addEvent('domready',this.progress.bind(this,this.sync_queue));
        win.addEvent('domready',function(){
            self.progress.call(self,self.sync_queue);
        });
        if(!this.sync_queue.length) return;
        win.addEvents({'scroll':loader,'resize':loader});

        function loader(){
        if(timer) return;
        timer = function(){
            self.progress.call(self,self.sync_queue);
            if(!self.sync_queue.length)
                win.removeEvent('scroll',loader).removeEvent('resize',loader);
                timer = null;
            }.delay(200);
        }
    },
    progress:function(queue){
        if(!queue.length) return this;
        var no_require_queue=[],require_queue = [];

        queue.each(function(q){
            if(!q.require) return no_require_queue.push(q);
            require_queue.push(q);
        });

        !!no_require_queue.length && no_require_queue.each(this.filterSync,this);
        !!require_queue.length && this.require(require_queue,queue);
    },
    filterItems:function(queue){

        var offsetY = (queue.update || queue.append).getOffsets().y, appendEl,
        win = window ,top = win.getScroll(), threshold, vh=win.getSize().y ;

        if(appendEl = queue.append) offsetY += appendEl.getSize().y;
        if(threshold = this.options.threshold)  offsetY -= threshold;
        return offsetY <= top.y + vh ? true : false;
    },
    filterSync:function(q){
        if(!q.update && !q.append) return this.sync_queue.erase(q);
        this.filterItems(q) && this.request(q);
    },
    require:function(cur_queue,queue){
        cur_queue.each(function(q){
            var cur_sync=this.cur_sync[q.require];
            if(cur_sync && cur_sync.running) return cur_sync.ajaxCb=function(){return this.filterSync(q);};
            if(cur_sync=='complete') this.filterSync(q);
        },this);
    },
    request:function(item){
        if(!item)return;
        var _onSuccess = item.onSuccess || function(){}, _onFailure= item.onFailure|| function(){},
        _onRequest = item.onRequest || function(){}, self = this,
        count = 2 , sync =self.cur_sync[item.name];

        var ele = $(item.name);
        if(ele){
            if(item.update) item.update = ele;
            else item.append = ele;
        }
        if(!ele && this.detail)return this.sync_queue.erase(item);

        var view = item.view? '&view='+item.view:'';
        if(sync && sync.running)return this;
        return this.cur_sync[item.name]= new Request.HTML(
        Object.append(item,{
            timeout:30000,
            data:'invalid_post_data=1'+view,
            onTimeout:function(async){
                this.cancel();
                if(!count) return self.fireEvent('failure',item).complete(item);
                count -= 1;
                this.send();
            },
            onRequest:function(){
                self.fireEvent('request',item);
                _onRequest.apply(self, arguments);
            },
            onFailure:function(){
                self.fireEvent('failure',item);
                _onFailure.apply(self, arguments);
                self.failure.call(self,item);
            },
            onSuccess:function(rs){
                self.fireEvent('complete',item);
                _onSuccess.apply(self, arguments);
                self.complete.call(self,item);
                if(this.ajaxCb) this.ajaxCb.call(self);
            }
        })).send();
    },
    complete:function(queue){
        this.cur_sync[queue.name] ='complete';
        this.sync_queue.erase(queue);
        if(!this.sync_queue.length)this.success();
    },
    failure:function(queue){
        this.cur_sync[queue.name]='failure';
        this.sync_queue.erase(queue).each(function(q){
            if(q.require==queue.name){
                delete q.require;
                this.filterSync(q);
            }
        },this);
    },
    success:function(){this.fireEvent('success');}
});
