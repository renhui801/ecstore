// vim: set et st=4 ts=4 sts=4:
/*------- Author: Tyler Chao===tylerchao.sh@gmail.com -------*/

/*
 * 弹框组件
 * @param target 弹出框内容元素
 * @param id 弹出框的id
 * @param type 弹框类型:popup,nohead,notitle,noclose,nofoot
 * @param template 页面中模板位置ID
 * @param width 弹出框宽度 0 或'auto'为不限制，支持传小数表示窗口的比例
 * @param height 弹出框高度 0 或'auto'为不限制，支持传小数表示窗口的比例
 * @param title 弹出框标题
 * @param autoHide 是否一定时间自动关闭 false或具体数值(秒)
 * @param onLoad 载入时触发事件
 * @param onShow 显示时触发事件
 * @param onClose 关闭时触发事件
 * @param modal 是否在弹出时候其他区域不可操作，可接受的参数有：
 *        target 遮罩层定位的目标
 *        'class' 遮罩层的class样式
 *        opacity 遮罩层的透明度
 * @param pins 是否把弹窗固定不动
 * @param single 单一窗口，还是多层窗口
 * @param show 是否显示弹出框，默认建立时即显示
 * @param effect 是否使用特效 false true or {style from to}
 * @param position 定位到哪里
 *        target 相对定位的目标
 *        to 相对定位基点x,y(九点定位:0/left/right/100%/center/50%,0/top/bottom/100%/center/50%)
 *        base 弹框的定位基点x,y(同上)
 *        offset 偏移目标位置
 *              x 横向偏移
 *              y 纵向偏移
 *        intoView 如果超出可视范围，是否滚动使之可见
 * @param useIframeShim 是否使用iframe遮盖
 * @param async 异步调用方式: false, frame, ajax
 * @param frameTpl iframe方式调用的模板
 * @param ajaxTpl ajax方式调用的模板
 * @param asyncOptions 异步请求的参数
 *        cache 是否缓存请求内容 2012.6.24
 *        target 请求的缓存目标
 *        method 请求的方式
 *        data ajax/iframe方式请求的数据
 *        onRequest 请求时执行
 *        onSuccess 请求成功后执行
 *        onFailure 请求失败后执行
 *        etc. 还有更多Request里其它参数
 * @param component 弹出框的构成组件，有以下组成:
 *        container: 'popup-container'
 *        body: 'popup-body'
 *        header: 'popup-header'
 *        close: 'popup-btn-close'
 *        content: 'popup-content'
 *        mask: 'mask'
 * @method getTemplate 获取模板
 * @method build 建立对话框结构
 * @method toElement 返回this.container窗体，通过$(new Popup())可自动调用此方法
 * @return this Popup instance
 */
var Popup = new Class({
    Implements: [Events, Options],
    options: {
        title: LANG_shopwidgets.tip,
        /* id: null,
         * type: null,
         * template: null,
         * width: 0,
         * height: 0,
         * autoHide: false,
         * modal: false,
         * pins: false,
         * single: false,
         * onLoad: function(){},
         * onShow: function(){},
         * onClose: function(){},*/
        show: true,
        minHeight:220,
        minWidth:250,
        effect: {
            style: 'opacity',
            duration: 400,
            from: 0,
            to: 1,
            maskto: 0.7
        },
        position: {
            target: document.body,
            from: {x: 'center', y: 'center'},
            to: {x: 'center', y: 'center'},
            offset: {x: 0, y: 0}
            /* intoView: false,
             * resize: false*/
        },
        /* useIframeShim: false,
         * async: false,*/
        frameTpl: '<iframe allowtransparency="allowtransparency" align="middle" frameborder="0" height="100%" width="100%" scrolling="auto" src="about:blank">请使用支持iframe框架的浏览器。</iframe>',
        ajaxTpl: '<div class="loading">loading...</div>',
        asyncOptions: {
            method: 'get',
            cache: true
            /* target: null,
             * data: '',
             * onRequest: function() {},
             * onSuccess: function() {},
             * onFailure: function() {}*/
        },
        component: {
            container: 'popup-container',
            body: 'popup-body',
            header: 'popup-header',
            close: 'popup-btn-close',
            content: 'popup-content',
            mask: 'mask'
        }
    },
    initialize: function(target, options) {
        if (!target) return;
        this.target = target;
        this.doc = document.id(document.body);
        this.setOptions(options);

        options = this.options;
        var asyncOptions = options.asyncOptions || {};
        var container = this.container = this.build(options.template, target);
        var el = new Element('div');
        this.body = container.getElement('.' + options.component.body) || el;
        this.header = container.getElement('.' + options.component.header) || el;
        this.title = this.header.getElement('h2') || el;
        this.close = container.getElements('.' + options.component.close);
        this.content = container.getElement('.' + options.component.content) || el;
        this.zIndex = maxZindex('div');
        if(options.width && !isNaN(options.width)) {
            if(options.width <= 1 && options.width > 0) {
                options.width = options.width * window.getSize().x;
            }
            else {
                options.width = options.width.toInt();
            }
        }
        if(options.height && !isNaN(options.height)) {
            if(options.height <= 1 && options.height > 0) {
                options.height = options.height * window.getSize().y;
            }
            else {
                options.height = options.height.toInt();
            }
        }
        this.size = {
            x: options.width || '',
            y: options.height || ''
        };
        options.title || (this.header.getElement('h2') && this.header.getElement('h2').destroy());
        container.retrieve('instance') || this.body.setStyles({
            width: this.size.x,
            height: this.size.y
        });
        this.fireEvent('load', this);
        if (typeOf(target) === 'string') {
            if (options.async === 'ajax') {
                this.requestCache(Object.merge({
                    url: target + '',
                    update: this.content
                }, asyncOptions));
            }
            else {
                var url = asyncOptions.data ? target + (target.indexOf('?') > 1 ? '&' : '?') + asyncOptions.data : target + '';
                this.content.getElement('iframe').set('src', url).addEvent('load', (asyncOptions.onSuccess || function(){}).bind(this));
            }
        }
        if($$('div[rel=mask].'+options.component.mask)[0]) options.modal = false;
        if (!!options.modal) {
            var effect = !!options.effect ? {
                style: options.effect.style,
                duration: options.effect.duration,
                from: options.effect.from,
                to: options.modal.opacity || options.effect.maskto
            } : false;
            this.mask = new Mask({
                target: options.modal.target || options.modal,
                'class': options.modal['class'] || options.component.mask,
                zIndex: this.zIndex++,
                effect: effect
            });
        }
        this.hidden = true;
        this.attach(); //执行初始化加载
    },
    attach: function() {
        if(this.options.show) this.show();
        //如果有存储实例，就返回
        if (!this.container.retrieve('instance')) {
            this.container.store('instance', this).addEvent('click:relay(.' + this.options.component.close + ')', function(e){
                this.hide();
            }.bind(this));
            // var closeBtn = this.container.getElements('.' + this.options.component.close);
            // closeBtn.length && closeBtn.addEvent('click', function(e){
            //     this.hide();
            //     // this.bindHide = true;
            // }.bind(this));
            // this.container.store('instance', this);
        }
        return this;
    },
    build: function(template, target) {
        var options = this.options;
        var single = options.id ? document.id(options.id) : document.getElement('[data-single=true].' + options.component.container);
        var main;

        if (typeOf(target) === 'element') {
            main = target.tagName.test(/^(?:IMG|OBJECT|SCRIPT)$/) ? target : target.innerHTML;
        }
        else if (typeOf(target) === 'string') {
            main = options.async === 'ajax' ? options.ajaxTpl : options.frameTpl;
        }

        if(options.single && single) {
            if(typeOf(target) === 'element') single.retrieve('instance').content.innerHTML = main;
            return single;
        }

        template = '<div class="{container}" {id} data-single="'+ !!options.single +'" tabindex="1">' + this.getTemplate(template) + '</div>';

        Object.merge(options.component, {
            title: options.title,
            id: options.id ? 'id="' + options.id + '"' : '',
            main: main
        });
        return new Element('div', {html: template.substitute(options.component)}).getFirst().inject(this.doc);
    },
    getTemplate: function(template, type) {
        var options = this.options;
        template = template || options.template;
        if (template && typeOf(template) === 'string') {
            if(!document.id(template)) return template;
            template = document.id(template);
        }
        if (typeOf(template) === 'element' && (/^(?:script|textarea|template)$/i).test(template.tagName)) return document.id(template).get('value') || document.id(template).get('html');
        type = type || options.type;
        var containerTpl = [
            '<div class="{body}">',
            '<div class="{header}">',
            '<h2>{title}</h2>',
            '<span><button type="button" class="{close}" title="关闭"><i>×</i></button></span>',
            '</div>',
            '<div class="{content}">{main}</div>',
            '</div>'
        ];
        if (type === 'nohead') containerTpl[1] = containerTpl[2] = containerTpl[3] = containerTpl[4] = '';
        else if (type === 'notitle') containerTpl[2] = '';
        else if (type === 'noclose' || !!options.autoHide) containerTpl[3] = '';

        return containerTpl.join('\n');
    },
    show: function() {
        if(!this.hidden) return this;
        this.container.setStyle('display', 'block').setStyle('z-index', this.zIndex ++);
        this.position();

        if(Browser.ie6 && this.options.useIframeShim) {
            new Element('iframe', {
                frameborder: 0,
                src: 'about:blank',
                style: 'position:absolute;z-index:-1;border:0 none;filter:alpha(opacity=0);top:' + (- this.container.getPatch().y || 0) + 'px;left:' + (- this.container.getPatch().x || 0) + 'px;width:' + (this.container.getSize().x || 0) + 'px;height:' + (this.container.getSize().y || 0) + 'px;'
            }).inject(this.container);
        }

        var eff = this.options.effect;
        if(eff) {
            if(eff === true || eff.style === 'opacity') this.container.setStyle('opacity', eff.from || 0).setStyle('visibility','visible');
            new Fx.Tween(this.container, {duration: eff.duration || 400,onComplete:function(){this.container.focus();}.bind(this)}).start(eff.style || 'opacity', eff.from || 0, eff.to || 1);
        }
        else {
            this.container.focus();
        }
        this.hidden = false;
        this.fireEvent('show', this);

        this.mask && this.mask.show();
        if(this.options.autoHide) {
            this.container.timer = this.hide.delay(this.options.autoHide.toInt() * 1000, this);
        }

        return this;
    },
    hide: function() {
        if (this.hidden) return this;
        var fn = this.container.retrieve('resize_position');
        fn && window.removeEvent('resize', fn);
        this.fireEvent('close', this);
        this.options.pins && this.container.pin(false, false, false);
        var eff = this.options.effect;
        if (this.options.single) {
            if(eff) this.stopTimer().container.setStyles({display: 'none', opacity:0, visibility: 'hidden'});
            else this.container.setStyle('display', 'none');
        } else {
            if(eff) {
                this.container.timer = new Fx.Tween(this.container, {
                    duration: eff.duration || 400,
                    link: 'ignore',
                    onComplete: function(){
                        this.container.destroy();
                    }.bind(this)
                }).start(eff.style || 'opacity', eff.to || 1, eff.from || 0);
            }
            else {
                this.container.destroy();
            }
            document.id(this.container).eliminate('instance');
        }
        this.hidden = true;
        this.container.store('instance', this);
        return this.hideMask();
    },
    stopTimer: function(){
        if (this.container.timer) {
            clearTimeout(this.container.timer);
            this.container.timer = null;
        }
        return this;
    },
    hideMask: function () {
        if (this.options.modal && $$('[data-single]').retrieve('instance').every(function(e) {
            return !e || !e.close.length || e.hidden;
        })) this.mask.hide();
        return this;
    },
    hideAll: function(){
        this.hide();
        var dialog = $$('[data-single]').retrieve('instance');
        dialog.length && dialog.each(function(el) {
            el && el.hide();
        });
    },
    position: function(){
        var options = this.options, element;
        if(!this.size.x && Browser.ie && Browser.version < 8 && this.container.getSize().x >= this.doc.getSize().x){
            document.id(this.body).setStyle('width', options.minWidth.toInt() - this.container.getPatch().x);
        }
        if(!this.size.y && Browser.ie && Browser.version < 8 && this.container.getSize().y >= this.doc.getSize().y){
            document.id(this.body).setStyle('height', Math.min(options.minHeight.toInt(), this.doc.getSize().y) - this.container.getPatch().y);
        }
        if (this.size.y) element = this.container;
        else if(this.container.getSize().y >= this.doc.getSize().y) element = this.doc;
        if(typeOf(element) === 'element') this.setHeight(element);
        this.container.position(options.position);
        options.pins && this.container.pin();
        return this;
    },
    setHeight: function(el) {
        el = el || this.container;
        this.content.setStyle('height', (!this.size.y && Browser.ie && Browser.version < 8 ? this.body.getStyle('height').toInt() : el.getSize().y - this.container.getPatch().y) - document.id(this.body).getPatch().y - document.id(this.header).getOuterSize().y - this.content.getPatch().y);
    },
    setTitle: function(html) {
        this.title.set('html', html);
    },
    requestCache: function(options) {
        var cache;
        if(!options) return null;
        if(options.target && options.cache) {
            cache = options.target.retrieve('request:cache');
            if(cache) return cache.success(cache.response.text);
        }
        cache = new Request.HTML(options).send();
        options.target && options.target.store('request:cache', cache);
        return this;
    },
    toElement: function() {
        return this.container;
    }
});

// pin
Element.implement({
    fixed: function(pos) {
        pos = [pos].flatten() || ['top', 'left'];
        var isLeft = pos.contains('left');
        var isTop = pos.contains('top');
        if(!Browser.ie6) return;
        // var doc = document.id(document.body);
        // var hasbg = doc.getStyle('background-image');
        // if(!hasbg || hasbg == 'none') doc.setStyle('background-image', 'url(about:blank)');
        // doc.setStyle('background-attachment', 'fixed');
        var scroll = window.getScroll();
        var position = {
            x: this.getPosition().x - scroll.x,
            y: this.getPosition().y - scroll.y
        };
        var scrollFx = function(){
            if (isLeft && this.getStyle('left').toInt() >= document.body.clientWidth || isTop && this.getStyle('top').toInt() >= document.body.clientHeight) return;
            scroll = window.getScroll();
            this.set('morph',{duration: 200, transition:Fx.Transitions.Quart.easeOut}).morph({
                left: isLeft ? position.x + scroll.x : null,
                top: isTop ? position.y + scroll.y : null
            });
        }.bind(this);

        window.addEvent('scroll', scrollFx);
    },
    pin: function(enable, forceScroll, restore, pos){
        //if(this.getStyle('display') == 'none') this.setStyle('display', '');
        if (typeOf(enable) === 'object') {
            pos = enable.pos;
            restore = enable.restore;
            forceScroll = enable.forceScroll;
            enable = enable.enable;
        }
        pos = pos ? [pos].flatten() : ['top', 'left', 'bottom', 'right'];
        var isLeft = pos.contains('left');
        if (enable !== false){
            if (!this.retrieve('pin:_pinned')){
                var scroll = window.getScroll();
                this.store('pin:_original', this.getStyles('position', pos));
                var pinnedPosition = this.getPosition(!Browser.ie6 ? document.body : this.getOffsetParent());
                var currentPosition = {
                    left: isLeft ? pinnedPosition.x - scroll.x : '',
                    top: pinnedPosition.y - scroll.y
                };
                if (!Browser.ie6){
                    this.setStyle('position', 'fixed').setStyles(currentPosition);
                } else {
                    if(!!forceScroll) this.setPosition({
                        x: this.getOffsets().x + scroll.x,
                        y: this.getOffsets().y + scroll.y
                    });
                    if (this.getStyle('position') == 'static') this.setStyle('position', 'absolute');

                    var position = {
                        x: this.getPosition().x - scroll.x,
                        y: this.getPosition().y - scroll.y
                    };
                    var scrollFixer = function(){
                        if (!this.retrieve('pin:_pinned') || isLeft && this.getStyle('left').toInt() >= document.body.clientWidth || this.getStyle('top').toInt() >= document.body.clientHeight) return;
                        var scroll = window.getScroll();
                        this.setStyles({
                            left: isLeft ? position.x + scroll.x : '',
                            top: position.y + scroll.y
                        });
                    }.bind(this);

                    this.store('pin:_scrollFixer', scrollFixer);
                    window.addEvent('scroll', scrollFixer);
                }
                this.store('pin:_pinned', true);
            }
        } else {
            if (!this.retrieve('pin:_pinned')) return this;
            if (!!restore) this.setStyles(this.retrieve('pin:_original', {}));
            this.eliminate('pin:_original');
            this.store('pin:_pinned', false);
            if (Browser.ie6) {
                window.removeEvent('scroll', this.retrieve('pin:_scrollFixer'));
                this.eliminate('pin:_scrollFixer');
            }
        }
        return this;
    },
    togglePin: function(){
        return this.pin(!this.retrieve('pin:_pinned'));
    }
});

//Mask
var Mask = new Class({
    Implements: [Options, Events],
    options: {
        /*target: null,
        injectTo: null,
        html: '',
        width: 0,
        height: 0,
        zIndex: null,*/
        'class': 'mask',
        effect: {
            style: 'opacity',
            duration: 300,
            from: 0,
            to: 0.7
        }
        /*position: false,
        pins: false,
        resize: false*/
    },
    initialize: function(options) {
        this.target = (options && document.id(options.target)) || document.id(document.body);
        //this.target.store('mask', this);
        this.setOptions(options);

        this.element = $$('div[rel=mask].' + this.options['class'])[0] || new Element('div[rel=mask].' + this.options['class']).inject(this.options.injectTo || (this.target == window ? document.body : this.target));
        if(this.options.html) this.element.set('html', this.options.html.stripScripts());
        this.hidden = true;
    },
    setSize: function() {
        if(!this.target || !document.id(this.element).isVisible()) return;
        this.element.setStyles({
            width: this.options.width || Math.max(this.target.getScrollSize().x, this.target.getSize().x, this.target.clientWidth || 0),
            height: this.options.height || Math.max(this.target.getScrollSize().y, this.target.getSize().y, this.target.clientHeight || 0)
        });
    },
    position: function() {
        this.element.position({target:this.target, resize: this.options.resize});
    },
    show: function() {
        if (!this.hidden) return;
        if(this.target == window) {
            document.html.setStyles({'height':'100%','overflow':'hidden'});
        }
        window.addEvent('resize', this.setSize.bind(this));
        this.setSize();

        this.element.setStyle('display','block');
        if(this.options.zIndex) this.element.setStyle('z-index', this.options.zIndex);
        if(this.options.html) this.element.setStyle('line-height', this.element.getSize().y);
        if(this.options.position) this.position();
        if(this.options.pins) this.element.pin();
        var effect = this.options.effect;
        if(effect) {
            // this.opacity = this.element.get('opacity');
            if(effect === true || effect.style == 'opacity') this.element.setStyle('opacity', effect.from || 0).setStyle('visibility','visible');
            new Fx.Tween(this.element,{duration: effect.duration || 400}).start(effect.style || 'opacity', effect.from || 0, effect.to);
        }
        else if(this.element.get('opacity') === 0){
            this.element.set('opacity', '').setStyle('visibility', '');
        }
        this.hidden = false;
        return this;
    },
    hide: function() {
        if (this.hidden) return;
        window.removeEvent('resize', this.setSize.bind(this));
        var fn = this.element.retrieve('resize_position');
        fn && window.removeEvent('resize', fn);

        var effect = this.options.effect;
        if(effect) {
            new Fx.Tween(this.element, {
                duration:effect.duration || 400,
                onComplete: function(){
                    this.element.destroy();
                }.bind(this)
            }).start(effect.style || 'opacity', effect.to, effect.from || 0);
        }
        else {
            this.element.destroy();
        }
        if(this.target == window) {
            document.html.setStyles({height:'',overflow:''});
        }
        this.hidden = true;
        return this;
    },
    toggle: function() {
        return this[this.hidden ? 'show' : 'hide']();
    },
    toElement: function(){
        return this.element;
    }
});
Element.Properties.mask = {
    set: function(options){
        var mask = this.retrieve('mask');
        if(mask) {
            var element = document.id(mask.element);
            element && element.destroy();
            this.eliminate('mask');
        }
        return this.store('mask:options', options);
    },
    get: function(){
        var mask = this.retrieve('mask');
        if (!mask){
            mask = new Mask(Object.merge(this.retrieve('mask:options') || {}, {target:this}));
            this.store('mask', mask);
        }
        return mask;
    }
};
Element.implement({
    mask: function(options){
        if (options) this.set('mask', options);
        this.get('mask').show();
        return this;
    },
    unmask: function(){
        this.get('mask').hide();
        return this;
    }
});

var Dialog = new Class({
    Extends: Popup,
    initialize: function(target,options){
        options = Object.merge({
            width:330,
            useIframeShim: true,
            template: document.id('popup_template'),
            position: {
                intoView: true
            }
        }, options || {});
        this.parent(target,options);
    }
}).extend({
    //= 创建Dialog实例，只建立不显示
    instance: function(target, options) {
        return new Dialog(target, Object.merge({
            show: false
        }, options || {}));
    },
    //= 类似系统alert功能
    alert: function() {
        var args = Array.from(arguments).link({
            msg: Type.isString,
            callback: Type.isFunction,
            options: Type.isObject
        });
        var options = args.options || {};
        var html = '<div class="pop-attention-main"><div class="figure"><dfn class="alert">!</dfn><span class="mark">' + args.msg + '</span></div><div class="bottom"><button type="button" class="btn btn-caution action-confirm"><span><span>' + (options.confirmText || '确定') + '</span></span></button> </div></div>';
        Dialog.instance(new Element('div', {html: html}), Object.merge({
            width: 350,
            title: '友情提示',
            modal: window,
            pins: true,
            single: false,
            effect: false,
            position: {
                intoView: true
            },
            component: {
                container: 'alert-container',
                header: 'alert-header',
                close: 'alert-btn-close',
                body: 'alert-body',
                content: 'alert-content'
            },
            onLoad: function() {
                this.content.getElements('.action-confirm').addEvent('click', function(e){
                    this.hide();
                    args.callback && args.callback.call(this);
                }.bind(this));
            }
        }, options)).show();
    },
    //= 类似系统confirm功能
    confirm: function() {
        var args = Array.from(arguments).link({
            msg: Type.isString,
            callback: Type.isFunction,
            options: Type.isObject
        });
        var options = args.options || {};
        var html = '<div class="pop-attention-main"><div class="figure"><dfn class="confirm">!</dfn><span class="mark">' + args.msg + '</span></div><div class="bottom"><button type="button" class="btn btn-caution action-confirm" data-return="1"><span><span>' + (options.confirmText || '确定') + '</span></span></button><button type="button" class="btn btn-simple action-cancel" data-return="0"><span><span>' + (options.cancelText || '取消') + '</span></span></button></div></div>';
        Dialog.instance(new Element('div', {html: html}), Object.merge({
            width: 350,
            title: '友情提示',
            modal: window,
            pins: true,
            single: false,
            effect: false,
            position: {
                intoView: true
            },
            component: {
                container: 'alert-container',
                header: 'alert-header',
                close: 'alert-btn-close',
                body: 'alert-body',
                content: 'alert-content'
            },
            onLoad: function() {
                var _this = this, _return;
                this.content.getElements('[data-return]').addEvent('click', function(e){
                    _return = !!this.get('data-return').toInt();
                    _this.hide();
                    args.callback && args.callback.call(this, _return);
                });
            }
        }, options)).show();
    },
    tips: function() {
        var args = Array.from(arguments).link({
            element: Type.isElement,
            msg: Type.isString,
            callback: Type.isFunction,
            options: Type.isObject
        });
        var options = args.options || {};
        var element = document.id(args.element);
        var template = '<div class="{body}"><div class="{content}">{main}</div></div>';
        var html = '<span class="icon">&#x24;</span><p>' + args.msg + '</p><div class="bottom"><button type="button" class="btn btn-caution btn-small action-confirm" data-return="1"><span><span>' + (options.confirmText || '确定') + '</span></span></button><button type="button" class="btn btn-simple btn-small action-cancel" data-return="0"><span><span>' + (options.cancelText || '取消') + '</span></span></button></div>';
        Dialog.instance(new Element('div', {html: html}), Object.merge({
            width: '',
            modal: false,
            template: template,
            single: true,
            effect: false,
            position: {
                target: element,
                from: {x:'c',y:'b'},
                to: {x:'c',y:'t'},
                offset:{x:0,y:-2},
                intoView: true,
                resize: true
            },
            component: {
                container: 'dialog-tips-container',
                body: 'dialog-tips-body',
                content: 'dialog-tips-content'
            },
            onLoad: function() {
                var _this = this, _return;
                this.content.getElements('[data-return]').removeEvents('click').addEvent('click', function(e){
                    _return = !!this.get('data-return').toInt();
                    args.callback && args.callback.call(this, _return);
                    _this.hide();
                });
                this.arrow = Arrow(this.container, {to:this.options.position.from});
                this.options.position.offset = this.arrow.setTargetOffset(this.options.position);
                this.options.eventTarget && this.container.addEvent('outerclick', function(e){
                    if(e.target != this.options.eventTarget) {
                        this.hide();
                    }
                }.bind(this));
            },
            onShow: function(){
                this.arrow.show();
            },
            onClose: function(){
                this.container.removeEvents('outerclick');
            }
        }, options)).show();
    },
    iframe: function(target, options) {
        return Dialog.instance(target, Object.merge({
            async: 'iframe'
        }, options || {})).show();
    },
    ajax: function(target, options) {
        return Dialog.instance(target, Object.merge({
            async: 'ajax'
        }, options || {})).show();
    },
    image: function(src, options) {
        return Dialog.instance(new Element('div', {html: '<img src="' + src + '" />'}), Object.merge({
            template: '<div class="{body}"><span><button type="button" class="{close}" title="关闭"><i>×</i></button></span><div class="{content}">{main}</div></div>',
            width:'',
            height:'',
            modal: true,
            single: true,
            component: {
                container: 'image-container',
                close: 'image-btn-close',
                content: 'image-content'
            }
        }, options || {})).show();
    }
});

//Tips
var popTip = new Class({
    Extends: Popup,
    initialize: function(msg,options){
        if(!msg) return;
        options = options || {};
        var target = new Element('div[html=' + msg + ']');
        var relative = options.relative || document.body,
            rel = (/^(?:body|html)$/i).test(relative.tagName.toLowerCase()),
            x = rel ? 'center' : 0,
            y = rel ? 0 : 'top',
            pins = !!rel,
            offsetY = rel ? 0 : 'bottom';

        this.options = Object.merge(this.options, {
            type: options.type || 'nofoot',
            template: options.template || document.id('xtip_template'),
            modal: false,
            pins: pins,
            single: false,
            effect: true,
            position: {
                target: relative,
                from: {x: 0, y: offsetY},
                to: {x: x, y: y},
                offset: {
                    x: options.offset && options.offset.x ? options.offset.x : 0,
                    y: options.offset && options.offset.y ? options.offset.y : 0
                },
                intoView: options.intoView !== undefined ? options.intoView : true
            },
            component: {
                container: 'xtip-container',
                body: 'xtip-body',
                header: 'xtip-header',
                close: 'xtip-btn-close',
                content: 'xtip-content'
            }
        });
        this.parent(target, options);
    }
});

/*
 * tooltips,需要在元素上添加自定义属性"data-tips"
 */
var Tips = new Class({
    Implements: Options,
    options: {
        type: 'autohide',
        offset:{x:0,y:-9},
        hideDelay: 2000,
        position: 'topCenter',
        inject: document.body,
        where: 'bottom',
        'class': 'xtips-container',
        arrow: true,
        text: null
    },
    initialize: function() {
        var params = Array.from(arguments).link({
            element: Type.isElement,
            msg: Type.isString,
            options: Type.isObject
        });
        var elements = this.elements = document.id(params.element) || $$('[data-tips]');
        if(!elements || typeOf(elements) === 'elements' && !elements.length) return null;
        this.setOptions(params.options);
        this.options.inject = this.options.inject || document.body;

        this.build(params.msg);
    },
    build: function(msg) {
        //build elements
        var tag = this.options.type == 'inline' ? 'span' : 'div';
        var html = '<' + tag + ' class="xtips-content"></' + tag + '>';

        this.container = document.id('xtips_container') || new Element(tag + '#xtips_container.' + this.options['class'] , {
            html: html
        }).inject(this.options.type == 'inline' && typeOf(this.elements == 'element') ? this.elements : document.id(this.options.inject), this.options.where).store('tips', this);
        this.content = this.container.getElement('.xtips-content');
        this.msg = msg || '';
        return this;
    },
    attach: function(eventType) {
        if(eventType == 'mouse') {
            Array.from(this.elements).each(function(item){
                item.addEvents({
                    mouseenter: this.show.bind(this, [item, this.msg, false]),
                    mouseleave: this.hide.bind(this)
                });
            });
        }
        else if(eventType == 'click') {
            Array.from(this.elements).each(function(item){
                item.addEvent('click', function(){
                    this.hidden ? this.show(item,this.msg) : this.hide();
                });
            });
        }
        return this;
    },
    show: function(msg, el, type) {
        type = type || this.options.type;
        if(!el && typeOf(this.elements) === 'element') el = this.elements;
        var text = msg || this.msg || el.get('data-tips') || el.retrieve('tips:text');
        if(!el || !text || !this.container) return this;
        if(this.options.type !== 'tooltip') text = '<q class="icon">&#x24;</q>' + text;
        this.content.set('html', text); // set message
        //position it and set width?
        var pos = this.options.position.toLowerCase();
        var position = el.getPosition();
        var size = this.container.getSize();
        var elSize = el.getSize();
        var config = el.get('data-tips-config');
        config = JSON.decode(config) || {};
        var style = {};
        if(this.options.type !== 'inline') {
            if (['rightcenter', 'rc', 'cr'].indexOf(pos) != -1) {
                style = {
                    left: position.x + elSize.x,
                    top: Math.max(position.y + (size.y - elSize.y) / 2, 0)
                }
            }
            else if(['topcenter', 'tc', 'ct'].indexOf(pos) != -1) {
                style = {
                    left: Math.max(position.x - (size.x - elSize.x) / 2 + this.options.offset.x || 0, 0),
                    top: Math.max(position.y - size.y + this.options.offset.y || 0, 0)
                }
            }
            else if(['bottomleft', 'bl', 'lb'].indexOf(pos) != -1) {
                style = {
                    left: Math.max(position.x || 0, 0),
                    top: Math.max(position.y + elSize.y + this.options.offset.y || 0, 0)
                }
            }
        }
        style = Object.merge({
            display: '',
            opacity: 1,
            visibility: 'visible',
            width: config.width ? config.width : size.x > window.getSize().x ? window.getSize().x : ''
        }, style);
        this.container.setStyles(style);
        if(this.options.arrow) {
            pos = {
                'topCenter' : {x:'b',y:'c'},
                'rightCenter': {x:'l',y:'c'},
                'bottomCenter': {x:'t',y:'c'},
                'leftCenter': {x:'r',y:'c'}
            };
            Arrow(this.container, {to: pos[this.options.position]}).show();
        }
        //this.anim = new Fx.Tween(this.container, {duration:250}).start('opacity', 1);
        this.hidden = false;
        if (type == 'autohide') {
            this.stopTimer(el);
            el.timer = this.hide.delay(this.options.hideDelay, this);
        }
        return this;
    },
    stopTimer: function(el){
        if (el.timer) {
            clearTimeout(el.timer);
            el.timer = null;
        }
        return this;
    },
    hide: function() {
        /*if (this.anim) {
            this.anim.cancel();
            this.anim.start('opacity', 0);
        }
        this.container.tween('opacity', 0);*/
        this.container.setStyle('opacity', 0).setStyle('visibility', 'hidden');
        this.hidden = true;
        return this;
    }
});
Element.implement({
    tips: function(msg, autohide) {
        new Tips(this).show(msg, this, autohide);
        return this;
    }
});

var Warn = function(element, msg, cls){
    element = document.id(element);
    if(!element) return;
    this.msg = msg || '';
    cls = cls || 'warn-message';
    this.container = document.id('warn_message') ? document.id('warn_message').set('html', this.msg) : new Element('span#warn_message.' + cls, {html: this.msg}).inject(element, 'after');
};
Warn.prototype.show = function(msg){
    this.container.set('html', msg || this.msg || '').show('block');
};
Warn.prototype.hide = function() {
    this.container.hide();
};

var ToolTip = new Class({
    Implements: [Events, Options],
    Binds: ['hide'],
    timer: null,
    options: {
        autohide: true,
        offset: 12,
        hideDelay: 1000,
        'class': 'tooltip',
        arrow: 'arrow',
        position: {
            from: 'cb',
            to: 'ct',
            offset: {x: 0, y: -2}
        },
        text: null
    },
    initialize: function(element, options) {
        this.element = element;
        this.setOptions(options);
        // Create ToolTip
        this.toolTip = new Element('div.' + this.options['class'] + '-container', {'html': '<div class="' + this.options['class'] + '-content">' + this.options.text + '</div>'}).hide().inject(document.body);
        this.content = this.toolTip.getElement('.' + this.options['class'] + '-content');
        // Create arrow
        if(this.options.arrow) {
            this.arrow = Arrow(this.toolTip, {'class':this.options.arrow, to:this.options.position.from});
            this.options.position.offset = this.arrow.setTargetOffset(this.options.position);
        }
        // Attach event listeners
        [this.element, this.toolTip].each(function(el) {
            el.addEvents({
                'mouseenter': function() {
                    if (this.options.autohide) window.clearTimeout(this.timer);
                }.bind(this),
                'mouseleave': function() {
                    if (this.options.autohide) this.timer = this.hide.delay(this.options.hideDelay, this);
                }.bind(this)
            });
        }, this);
    },
    hide: function() {
        var btn;
        // this.arrow.hide();
        this.toolTip.hide();
        if (typeOf(btn = this.toolTip.getElement('div.close')) == 'element') {
            btn.destroy();
        }
        this.fireEvent('hide');
        this.element.erase('data-tooltip-displayed');
        return this;
    },
    position: function() {
        this.toolTip.position(Object.merge(this.options.position,{target:this.element}));
        return this;
    },
    set: function(content) {
        if (typeOf(content) == 'element') {
            this.content.empty().grab(content);
        }
        else {
            this.content.set('html', content);
        }
        if (!this.options.autohide) {
            new Element('div.close[title="关闭"]').inject(this.toolTip, 'top').addEvent('click', this.hide.bind(this));
        }
        //this.position();
    },
    show: function() {
        if (!this.element.get('data-tooltip-displayed')) {
            this.element.set('data-tooltip-displayed', true);
            this.toolTip.show();
            this.position();
            this.arrow && this.arrow.show();
            this.fireEvent('show');
        }
        return this;
    }
}).extend({
    /**
     * Tooltip instance getter
     * @param  Element           tooltip owner
     * @param  string | Element  tooltip content
     * @param  object            options; 2nd and 3rd parameters order may be reversed
     */
    instance: function() {
        var current;
        var toolTip;
        var element = arguments[0],
            param = ['string', 'element'].contains(typeOf(arguments[1])),
            content = param ? arguments[1] : (arguments[2] || null),
            options = param ? (arguments[2] || {}) : arguments[1];
        if (typeOf(current = document.retrieve('ToolTip.current')) == 'object') {
            current.hide();
        }
        if ((toolTip = element.retrieve('ToolTip.instance')) == null) {
            toolTip = new ToolTip(element, options);
        }
        else {
            toolTip.setOptions(options);
        }
        element.store('ToolTip.instance', toolTip);
        document.store('ToolTip.current', toolTip);
        if (content) {
            toolTip.set(content);
        }
        return toolTip;
    }
});

//= popup component: arrow
var Arrow = function(element, options) {
    options = Object.merge({
        target: document.body,
        'class': 'arrow',
        to: 'bc',
        offset: {x: 0, y: 0}
    }, options || {});
    if (typeOf(element) == 'element') {
        options.target = element;
    }
    var cls = typeof options['class'] == 'string' ? options['class'] : 'arrow';
    var direction = {
        'b': cls + '-bottom',
        't': cls + '-top',
        'l': cls + '-left',
        'r': cls + '-right'
    };
    options.position = Arrow.getCoordinate(options.to);
    var container = new Element('div.' + cls + '.'+direction[options.position.x], {
        html:'<i class="below">◆</i><i class="above">◆</i>'
    }).inject(options.target).store('Arrow:position', options);
    container.show = function() {
        this.setStyle('display', '').position(options);
        return this;
    };
    container.position = function() {
        var target, options;
        var param = ['string', 'element'].contains(typeOf(arguments[0]));
        if(param) {
            target = document.id(arguments[0]);
            options = arguments[1];
        }
        else {
            options = arguments[0];
            target = document.id(options.target);
        }
        if(!target) return;
        options = Object.merge({
            to: 'bc',
            offset: {x:0, y:0}
        }, options || this.retrieve('Arrow:position', {}));
        var edge = {
            'c': {x:0.5},
            'm': {y:0.5},
            'r': {x:1},
            'b': {y:1}
        };
        var size = target.getSize();
        var border = target.getPatch('border');
        var size2 = this.getSize();
        var position = options.position || Arrow.getCoordinate(options.to);
        var pos = {
            left: edge[position.y].x ? (size.x - size2.x - border.x) * edge[position.y].x + options.offset.x : '',
            top: edge[position.y].y ? (size.y - size2.y - border.y) * edge[position.y].y + options.offset.y : ''
        }
        return this.setStyles(pos);
    };
    //Calc offset
    container.setTargetOffset = function(to, offset) {
        if(typeof to == 'object' && to.to && to.offset) {
            offset = to.offset;
            to = to.to;
        }
        else {
            offset = offset || {
                x: 0,
                y: 0
            };
        }
        to = Arrow.getCoordinate(to);
        var edge = {
            t: ['y',-1, 'bottom'],
            b: ['y', 1, 'top'],
            l: ['x',-1, 'right'],
            r: ['x', 1, 'left']
        }
        offset[edge[to.x][0]] += edge[to.x][1] * this.getElement('.below').getStyle('border-' + edge[to.x][2] + '-width').toInt();
        return offset;
    };
    return container;
}.extend({
    getCoordinate: function(option) {
        if (typeof option == 'object' && (option.x || option.x === 0) && (option.y || option.y === 0)) {
            if('ltbr'.indexOf(option.x) > -1 && 'ltbrcm'.indexOf(option.y) > -1) return option;
            if('cm'.indexOf(option.x) > -1) return {
                x: option.y,
                y: option.x
            };
            option = option.x.toString() + option.y.toString();
        }
        option = option.toLowerCase();
        var a = option.match(/^c(enter)?|^m(iddle)?/g);
        var b = option.match(/c(enter)?$|m(iddle)?$/g);
        var c;
        if(a || b) {
            c = option.split(a ? a[0] : b[0])[a ? 1 : 0];
            option = c + (c.test(/^l(eft)?|^r(ight)?/g) ? 'm' : 'c');
        }
        return {
            x: option.test(/^l(eft)?|^0/) ? 'l' : option.test(/^r(ight)?|^1(00%)?/) ? 'r' : option.test(/^t(op)?/) ? 't' : 'b',
            y: option.test(/l(eft)?$/) ? 'l' : option.test(/r(ight)?$/) ? 'r' : option.test(/t(op)?$|0$/) ? 't' : option.test(/b(ottom)?$|1(00%)?$/) ? 'b' : option.test(/m(iddle)?$/) ? 'm' : 'c'
        };
    }
});

//Message box
function Message(msg, type, delay, callback, template){
    if(!msg) return null;
    if(!isNaN(type)) {
        delay = type;
        type = 'show';
    }
    else if(typeOf(delay) === 'function') {
        callback = delay;
        delay = 3;
    }
    else {
        type = type || 'show';
        delay = !isNaN(delay) ? delay : 3;
    }
    var icon = {
        'show': '',
        'success': '&#x25;',
        'error': '&#x21;'
    };
    var component = {
        container: 'message-' + type,
        body: 'message-body',
        content: 'message-content',
        icon: icon[type]
    };
    var pop = document.id('pop_message_' + type);
    var instance;
    if(pop) {
        instance = pop.retrieve('instance');
        if(instance) {
            instance.content.innerHTML = msg;
            instance.show();
        }
    }
    new Popup(new Element('div[html=' + msg + ']'), {
        id: 'pop_message_' + type,
        type: 'nohead',
        template: template || document.id('message_template'),
        modal: false,
        pins: true,
        single: true,
        effect: true,
        autoHide: delay,
        component: component,
        onShow: function() {
            this.stopTimer();
        },
        onClose: typeOf(callback) === 'function' ? callback.bind(this) : null
    });
    return (type == 'error' ? false : true);
}
Message.show = function(msg, delay, callback) {
    Message(msg || LANG_jstools['messageShow'], 'show', delay, callback);
};
Message.hide = function(type) {
    type = type || 'show';
    document.id('pop_message_' + type) && document.id('pop_message_' + type).retrieve('instance').hide();
}
Message.error = function(msg, delay, callback) {
    return Message(msg || LANG_jstools['messageError'], 'error', delay, callback);
};
Message.success = function(msg, delay, callback) {
    return Message(msg || LANG_jstools['messageSuccess'], 'success', delay, callback);
};

function maxZindex(scope, increase) {
    scope = scope || 'div';
    scope = $$(scope);
    var max = 0;
    if(scope.length) {
        var pos = scope.filter(function(el){
            if(el.nodeType != 1 || ['script', 'link', 'base', 'style'].contains(el.tagName.toLowerCase())) return;
            return ['absolute','relative','fixed'].contains(el.getStyle('position'));
        });
        if(pos.length) {
            for(var i=0, j=pos.length;i<j;i++) {
                var z = pos[i].getStyle('z-index');
                max = Math.max(max, isNaN(z) ? 0 : z);
            }
        }
    }
    if(increase) max += parseInt(increase);
    return Math.min(max, 2147483647);
}
