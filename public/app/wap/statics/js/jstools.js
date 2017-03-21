// vim: set et st=4 ts=4 sts=4:
/*------- Author: Tyler Chao===tylerchao.sh@gmail.com -------*/

Element.implement({
    //= 判断元素display
    isDisplayed: function(){
        return this.getStyle('display') != 'none';
    },
    //= 判断元素是否显示 --改正ie下对隐藏的img计算有误 bug
    isVisible: function(){
        var w = this.offsetWidth,
            h = this.offsetHeight;
        return this.isDisplayed() && w > 0 && h > 0;
        // return (w == 0 && h == 0) ? false : (w > 0 && h > 0) ? true : this.style.display != 'none';
    },
    //= 切换元素显示隐藏
    toggle: function(){
        return this[this.isDisplayed() ? 'hide' : 'show']();
    },
    //= 隐藏元素
    hide: function(){
        var d;
        try {
            //IE fails here if the element is not in the dom
            d = this.getStyle('display');
        } catch(e){}
        if (d == 'none') return this;
        return this.store('element:_originalDisplay', d || '').setStyle('display', 'none');
    },
    //= 显示元素 --改正用在内联元素时，默认转为block元素
    show: function(display){
        if (!display && this.isDisplayed()) return this;
        display = display || this.retrieve('element:_originalDisplay') || '';
        return this.setStyle('display', (display == 'none') ? 'block' : display);
    },
    //= remove & add classes
    swapClass: function(remove, add){
        return this.removeClass(remove).addClass(add);
    },
    //= 图片缩放
    zoomImg: function(maxwidth, maxheight, v) {
        if (!Browser.ie6 && this.getStyle('max-width') !== 'none' && this.getStyle('max-height') !== 'none') return;
        if (this.tagName !== 'IMG' || ! this.width) return;
        var thisSize = {
            'width': this.width,
            'height': this.height
        };
        maxwidth = parseInt(maxwidth) || Number.MAX_VALUE;
        maxheight = parseInt(maxheight) || Number.MAX_VALUE;
        var overSize, zoom, zoomSizeW, zoomSizeH;
        if (thisSize.width <= maxwidth && thisSize.height <= maxheight) return v ? thisSize : null;
        if (thisSize.width > maxwidth) {
            overSize = thisSize.width - maxwidth;
            zoom = (maxwidth / thisSize.width).toFloat();
            zoomSizeH = (thisSize.height * zoom).toInt();
            thisSize = {
                'width': maxwidth,
                'height': zoomSizeH
            };
        }
        if (thisSize.height > maxheight) {
            overSize = thisSize.height - maxheight;
            zoom = (maxheight / thisSize.height).toFloat();
            zoomSizeW = (thisSize.width * zoom).toInt();
            thisSize = {
                'width': zoomSizeW,
                'height': maxheight
            };
        }
        return v ? thisSize : this.set(thisSize);
    },
    //= 克隆并包裹元素，多用于页面模块重用
    wrapped: function(keepid) {
        return new Element('div').adopt(this.clone(keepid).setStyle('display',''));
    },
    //= 获取padding,margin,border值
    getPatch: function() {
        var args = arguments.length ? Array.from(arguments) : ['margin', 'padding', 'border'];
        var _return = {
            x: 0,
            y: 0
        };

        Object.each({x: ['left', 'right'], y: ['top', 'bottom']}, function(p2, p1) {
            p2.each(function(p) {
                try {
                    args.each(function(arg) {
                        arg += '-' + p;
                        if (arg == 'border') arg += '-width';
                        _return[p1] += this.getStyle(arg).toInt() || 0;
                    }, this);
                } catch(e) {}
            }, this);
        }, this);
        return _return;
    },
    //= the elements outer size
    getOuterSize: function() {
        var w = this.offsetWidth;
        var h = this.offsetHeight;
        if (!this.isDisplayed() || (w === 0 && h === 0)) return {x: 0, y: 0};
        return {
            x: w + this.getPatch('margin').x,
            y: h + this.getPatch('margin').y
        };
    },
    //= 检测element是否有某属性，或属性值
    has: function(attr, value) {
        if(!attr) return null;
        attr = attr.toLowerCase();
        if(attr.indexOf('.') === 0) {
            attr = attr.slice(1);
            return this.hasClass(attr);
        }
        return this.get && this.get(attr) ? value ? this.get(attr) === value : !! this.get(attr) : false;
    },
    //= 从元素向上找n层，根据条件返回找到的元素
    nearest: function(attr, value, times) {
        if(!attr) return null;
        var el = this;
        for (var i = (times || 3); i; i--) {
            if (!el || el.nodeType === 8 || el.nodeType === 9) return null;
            if (el.has && el.has(attr, value)) return el;
            el = el.parentNode;
        }
        return null;
    },
    //= form返回JSON格式数组或字串
    toJSON: function(string) {
        var json = this.getElements('input,select,textarea').map(function(el){
            var o = {};
            if((el.type == 'checkbox' || el.type=='radio') && !el.checked || !el.name || !el.value || el.disabled) return;
            o.name = el.name;
            o.value = el.value;
            return o;
        }).clean();
        return string ? JSON.encode(json) : json;
    },
    //= 检测元素是否支持某特性
    supportProperty: function(property, isType) {
        return supportProperty(isType ? ':' : '' + property, this.get('tag'));
    },
    //= 获取选择范围(for autocompleter)
    getSelectedRange: function() {
        if (this.selectionStart && this.selectionEnd) return {start: this.selectionStart, end: this.selectionEnd};
        var pos = {start: 0, end: 0};
        var range = this.getDocument().selection.createRange();
        if (!range || range.parentElement() != this) return pos;
        var dup = range.duplicate();
        if (this.type == 'text') {
            pos.start = 0 - dup.moveStart('character', -100000);
            pos.end = pos.start + range.text.length;
        } else {
            var value = this.value;
            var offset = value.length - value.match(/[\n\r]*$/)[0].length;
            dup.moveToElementText(this);
            dup.setEndPoint('StartToEnd', range);
            pos.end = offset - dup.text.length;
            dup.setEndPoint('StartToStart', range);
            pos.start = offset - dup.text.length;
        }
        return pos;
    },
    //= 选择范围(for autocompleter)
    selectRange: function(start, end) {
        if (this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else {
            var diff = this.value.substr(start, end - start).replace(/\r/g, '').length;
            start = this.value.substr(0, start).replace(/\r/g, '').length;
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', start + diff);
            range.moveStart('character', start);
            range.select();
        }
        return this;
    }
});
//= 获取元素uid，统一口径
Element.Properties.uid = {
    get: function() {
        return Slick.uidOf(this);
    }
};

//= 验证浏览器是否支持元素的某个属性或type
function supportProperty(property, tag){
    tag = tag || 'input';
    var element = document.createElement(tag);
    if(property.indexOf(':') != -1) {
        property = property.split(':')[1];
        element.setAttribute('type', property);
        return property === 'text' ? true : element.type !== 'text';
    }
    return property ? property in document.createElement(tag) : false;
}

//= 当值改变时，实时触发事件，不支持冒泡（事件代理）！
Element.Events.inputchange = {
    base: supportProperty('onpropertychange') ? 'propertychange' : 'input',
    condition: function(e){
        return supportProperty('onpropertychange') ? (e.event.propertyName == ['checkbox', 'radio'].contains(e.target.type) ? 'value' : 'checked') : true;
    }
};
// Element.NativeEvents.inputchange = 2;
//= 点击目标元素以外的地方触发事件
Element.Events.outerclick = {
    base : 'click',
    condition: function(event){
        event.stopPropagation();
        return false;
    },
    onAdd: function(fn){
        this.getDocument().addEvent('click', fn);
    },
    onRemove: function(fn){
        this.getDocument().removeEvent('click', fn);
    }
}
//= 按键盘回车触发事件
Element.Events.enter = {
    base: 'keyup',
    condition: function(event){
        return (event.key == 'enter');
    }
};

/**
 * 图片头数据加载就绪事件 - 更快获取图片尺寸
 * @param{String}图片路径
 * @param{Function}尺寸就绪
 * @param{Function}加载完毕 (可选)
 * @param{Function}加载错误 (可选)
 * @example:
 * imageReady('http://www.google.com.hk/intl/zh-CN/images/logo_cn.png', function () {
 *     alert('width=' + this.width + '; height=' + this.height);
 * });
 */
var imageReady = (function () {
    var list = [], intervalId = null,
    // 用来执行队列
    tick = function () {
        list.each(function(li, i){
            li.end ? list.splice(i--, 1) : li();
        });
        !list.length && stop();
    },
    // 停止所有定时器队列
    stop = function () {
        clearInterval(intervalId);
        intervalId = null;
    };

    return function (url, ready, load, error) {
        var onready, width, height, newWidth, newHeight,
        img = new Image();
        img.src = url;

        // 如果图片被缓存，则直接返回缓存数据
        if (img.complete) {
            ready && ready.call(img);
            load && load.call(img);
            return;
        };

        width = img.width;
        height = img.height;

        // 加载错误后的事件
        img.onerror = function () {
            error && error.call(img);
            onready.end = true;
            img = img.onload = img.onerror = null;
        };

        // 图片尺寸就绪
        onready = function () {
            newWidth = img.width;
            newHeight = img.height;
            if (newWidth !== width || newHeight !== height || newWidth * newHeight > 1024) {
                ready && ready.call(img);
                onready.end = true;
            }
        };
        onready();

        // 完全加载完毕的事件
        img.onload = function () {
            // onload在定时器时间差范围内可能比onready快
            // 这里进行检查并保证onready优先执行
            !onready.end && onready();

            load && load.call(img);

            // IE gif动画会循环执行onload，置空onload即可
            img = img.onload = img.onerror = null;
        };

        // 加入队列中定期执行
        if (!onready.end) {
            list.push(onready);
            // 无论何时只允许出现一个定时器，减少浏览器性能损耗
            if (intervalId === null) intervalId = setInterval(tick, 40);
        }
    }
})();


(function(win, doc) {
    //= 取得九点定位的坐标
    function getOffset(el, offset) {
        var size = el.getSize(), x, y;
        var pos = {};
        switch (typeOf(offset)) {
        case 'array':
            pos.x = offset[0];
            pos.y = offset[1] || 'c';
            break;
        case 'string':
            pos.x = offset.charAt(0);
            pos.y = offset.charAt(1) || 'c';
            break;
        case 'object':
            pos.x = offset.x;
            pos.y = offset.y;
            break;
        default:
            pos = {
                x: 'c',
                y: 'c'
            };
        }

        switch (pos.x.toString().toLowerCase()) {
        case 'left':
        case 'l':
            x = 0;
            break;
        case '100%':
        case '1':
        case 'right':
        case 'r':
            x = size.x;
            break;
        case '50%':
        case '0.5':
        case 'center':
        case 'c':
            x = size.x / 2;
            break;
        default:
            x = pos.x.toInt() || 0;
            break;
        }
        switch (pos.y.toString().toLowerCase()) {
        case 'top':
        case 't':
            y = 0;
            break;
        case '100%':
        case '1':
        case 'bottom':
        case 'b':
            y = size.y;
            break;
        case '50%':
        case '0.5':
        case 'center':
        case 'c':
        case 'm':
            y = size.y / 2;
            break;
        default:
            y = pos.y.toInt() || 0;
            break;
        }

        return {
            x: x,
            y: y
        };
    }
    //= 设置元素xy坐标
    function setPosition(el, target, offsetParent, base, to, offset, intoView) {
        offset = typeOf(offset) == 'object' ? offset : typeOf(offset) == 'array' ? {x: offset.x || 0, y: offset.y || 0} : {x:0, y:0};
        offsetParent = offsetParent ? el.getOffsetParent() : null;
        var x = to.x - base.x + target.getPosition(offsetParent).x + target.getScroll().x + offset.x;
        var y = to.y - base.y + target.getPosition(offsetParent).y + target.getScroll().y + offset.y;

        if (intoView === 'in') {
            x = x.limit(0, win.getScroll().x + win.getSize().x - el.getSize().x);
            y = y.limit(0, win.getScroll().y + win.getSize().y - el.getSize().y);
        }

        el.setStyles({
            left: x,
            top: y
        });
    }
    Element.implement({
        //= 对元素定位
        position: function(options) {
            options = Object.merge({
                target: $(doc.body),
                to: 'cc', //定位到目标元素的基点
                from: 'cc', //此元素定位基点 --为数值时类似offset
                base: null, // {x: 'center', y: 'center'} 此元素定位基点(兼容旧的参数形式) --为数值时类似offset
                offset: {x: 0, y: 0}, // 偏移量,或[0,0]
                offsetParent: false,
                intoView: false, // true 或 to:滑动使this可视。in:把element限制在视窗内
                resize: false    // 是否随窗口改变位置
            }, options);

            if(this.getStyle('position') != 'absolute') this.setStyle('position', 'absolute');

            var target = $(options.target) || $(doc.body);
            var from = getOffset(this, options.from ? options.from : options.base);
            var to = getOffset(target, options.to);

            setPosition(this, target, options.offsetParent, from, to, options.offset, options.intoView);
            if (options.resize) {
                var resizePosition = function() {
                    if (!this.isVisible()) return;
                    clearTimeout(this.timer);
                    this.timer = setPosition.delay(100, this, [this, target, options.offsetParent, from, to, options.offset, options.intoView]);
                }.bind(this);
                this.store('resize_position', resizePosition);
                win.addEvent('resize', resizePosition);
            }

            if (options.intoView === true || options.intoView === 'to') try {
                new Fx.Scroll(doc, {link:'cancel', duration: 300}).toElementEdge(this);
            } catch(e) {}
            return this;
        }
    });
})(window, document);

//== 弹出新窗口
function openWindow(url, options) {
    var size = window.getSize();
    options = Object.append({
        width: size.x * 0.8,
        height: size.y * 0.9,
        left:0,
        top:0,
        scrollbars: 'yes',
        resizable: 'yes'
    }, options || {});
    if(options.maxmize) {
        options.width = screen.availWidth;
        options.height = screen.availHeight;
    }
    else {
        if(options.width&&options.width<=1) options.width = size.x*options.width;
        if(options.height&&options.height<=1) options.height = size.y*options.height;
    }
    if(options.position === 'center') {
        options.left = (size.x - options.width) / 2;
        options.top = (size.y - options.height) / 2;
    }
    var params = 'toolbar=no,location=no,status=no,menubar=no,scrollbars={scrollbars},resizable={resizable},top={top},left={left},width={width},height={height}';
    params = params.substitute(options);

    window.open(url || 'about:blank', '_blank', params);
};

//= 倒计时器(暂时去除)
/*var timeCount = new Class({
    init:function(timeStart,timeEnd,dom,isReload){
        this.isReload = isReload || true;
        var diff = Math.abs((timeStart.getTime() - timeEnd.getTime())/1000);
        var secondDiff = diff % 60;
        var minuteDiff = ((diff - secondDiff)/60) % 60;
        var hourDiff = (diff - secondDiff  - minuteDiff*60) / 3600;
        if(hourDiff > 24){
            var dayDiff = parseInt(hourDiff/24);
            hourDiff = hourDiff - dayDiff * 24;
            var timeDiff = [hourDiff,minuteDiff,secondDiff,dayDiff];
        }else{
            var timeDiff = [hourDiff,minuteDiff,secondDiff];
        }
        this.s = this.calcTime.periodical(1000,this,{
            time:timeDiff,
            dom:dom
        });
        if(document.getElement('.desc')){
        this.desc = 10;
        this.d = this.calcDesc.periodical(100,this);
        (function(){$('timer').setStyle('display','block')}).delay(1100);
        }
    },
    addZero:function(timeDiff){
        for(var i=0;i<timeDiff.length;i++){
            if(timeDiff[i].toString().length<2){
                timeDiff[i] = "0" + timeDiff[i].toString();
                return timeDiff;
            }
        }
    },
    formatToInt : function(timeDiff){
        for(var i=0;i<timeDiff.length;i++){
            parseInt(timeDiff[i]);
        }
        return timeDiff;
    },
    judgeTime : function(timeDiff){
        if(timeDiff[2]< 0  && timeDiff[1]>0){
            timeDiff[2] = 59;
            timeDiff[1]--;
            return timeDiff;
        }else if(timeDiff[2] <0 && timeDiff[1]==0 && timeDiff[0]>0){
            timeDiff[2] = 59;
            timeDiff[1] = 59;
            timeDiff[0]--;
            return timeDiff;
        }else if(timeDiff[2]==0 && timeDiff[1]==0 && timeDiff[0]==0){
            $clear(this.s);
            if(document.getElement('.desc')){ $clear(this.d); document.getElement('.desc').innerHTML = 0; }
            if(this.isReload){
                if(typeOf(this.isReload) == 'function'){
                    this.isReload();
                }else{
                    location.reload();
                }
            }
            return;
        }
    },
    calcTime : function (obj){
        if(!obj.dom) return;
        var _timeDiff = obj.time;
        this.addZero(_timeDiff);
        this.formatToInt(_timeDiff);
        _timeDiff[2]--;
        this.judgeTime(_timeDiff);
        this.addZero(_timeDiff);
        var dom = obj.dom;
        if(_timeDiff[3]){
            if(dom.day) dom.day.innerHTML = _timeDiff[3];
            if(dom.second){
                var domBox = dom.second.getParent('span');
                if(domBox) domBox.hide();
            }
            if(dom.minute) dom.minute.innerHTML = _timeDiff[1];
            if(dom.hour) dom.hour.innerHTML = _timeDiff[0];
        }else{
            if(dom.day) {
                var domBox = dom.day.getParent('span');
                if(domBox) domBox.hide();
            }
            if(dom.second){
                dom.second.innerHTML = _timeDiff[2];
            };
            if(dom.minute) dom.minute.innerHTML = _timeDiff[1];
            if(dom.hour) dom.hour.innerHTML = _timeDiff[0];
        }
    },
    calcDesc:function(){
        this.desc--;
        document.getElement('.desc').innerHTML = this.desc;
        if(this.desc == 0)
        this.desc = 10;
    }
});
*/