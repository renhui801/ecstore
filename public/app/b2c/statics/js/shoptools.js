// vim: set et st=4 ts=4 sts=4:
/*------- Author: Tyler Chao===tylerchao.sh@gmail.com -------*/

//=　设置用Cookie存储信息
var Memory = new Class({
    Implements: Cookie,
    options: {
        path: window['Shop'] ? Shop.base_url.toLowerCase() : '',
        domain: false,
        duration: false,
        secure: false,
        document: document,
        encode: true
    },
    initialize: function(key, options){
        key = key || '';
        if(key.indexOf('S[') == 0) {
            key = key.substr(1);
        }
        var index = key.indexOf('[');
        this.key = 'S';
        if(index > -1) {
            if(key.lastIndexOf(']') == key.length - 1) {
                this.key += (index ? '[' + key.substr(0, index) + ']' : '') + (index ? key.substr(index) : key);
            }
            else this.key += key;
        }
        else this.key += '[' + key.replace('.', '][') + ']';
        this.key = this.key.toUpperCase();
        if(!isNaN(options)) options = {duration: options};
        this.setOptions(options);
    },
    write: function(value){
        if (this.options.encode) value = encodeURIComponent(value);
        if (this.options.domain) value += '; domain=' + this.options.domain.toLowerCase();
        if (this.options.path) value += '; path=' + this.options.path.toLowerCase();
        if (this.options.duration){
            var date = new Date();
            date.setTime(date.getTime() + this.options.duration * 24 * 60 * 60 * 1000);
            value += '; expires=' + date.toGMTString();
        }
        if (this.options.secure) value += '; secure';
        this.options.document.cookie = this.key + '=' + value;
        return this;
    }
}).extend({
    set: function(key, value, options) {
        return new Memory(key, options).write(value);
    },
    get: function(key) {
        return new Memory(key).read();
    },
    clean: function(key, options) {
        return new Memory(key, options).dispose();
    }
});
//=设置并获取模块
var Module = function(m1,m2,m3) {
    this.get = function(value) {
        value = value.split(/[._]/);
        if(value.length < 2) return null;
        var page = value[0];
        var mod = value[1];
        page = this[page];
        if(page) {
            return page[mod];
        }
    };
    this.mod = function(page, mod, value) {
        this.set(page, mod, value);
        var module = this[page];
        Object.each(module, function(v, k){
            module[k] = document.id(v['module']);
        });
        return this;
    };
    this.set = function(page, mod, value) {
        function setDef(a, b) {
            a[b] = a[b] || {};
            return a[b];
        }
        var pagemod = setDef(this, page);
        if(typeOf(mod) == 'object') {
            Object.each(mod, function(v,k){
                setDef(pagemod, k)['module'] = v;
            });
        }
        else if(typeOf(mod) == 'array') {
            mod.each(function(val){
                if(typeOf(val) == 'object') {
                    Object.each(val, function(v, k){
                        setDef(pagemod, k)['module'] = v;
                    });
                }
                else if(typeOf(val) == 'string') {
                    value = page + '_' + val;
                    setDef(pagemod,val)['module'] = value;
                }
            });
        }
        else if(typeOf(mod) == 'string') {
            value = value || page + '_' + mod;
            setDef(pagemod,mod)['module'] = value;
        }
        return this;
    };
    this.dom = function(method, mod, el) {
        var key = mod + '>' + el;
        if(this[key]) return this[key];
        this[key] = this.get(mod)[method](el);
        return this[key];
    };
    this.element = function(mod, el) {
        return this.dom('getElement', mod, el);
    };
    this.elements = function(mod, el) {
        return this.dom('getElements', mod, el);
    };
    if(m1) return this.mod(m1, m2, m3);
};
// 计算并设定大图模式尺寸
function setGridSize(rows, els, cols) {
    cols = cols || 0;
    var i=0, j = rows.length;
    if (j == 0) return;
    for(;i<j;i++) {
        var item = rows[i];
        var top = item.getPosition().y;
        if(i>0) {
            var prev = rows[i-1];
            if(!item.innerHTML.trim()) {
                item.setStyle('height', prev.getStyle('height'));
            }
            if(cols == 0) {
                var prevTop = prev.getPosition().y;
                if(top > prevTop) {
                    cols = i;
                }
            }
        }
    }
    if(cols == 0) cols = j;
    var cont = rows[0].getParent();
    var contSize = cont.getSize().x - cont.getPatch().x;
    var rowSize = parseInt(contSize / cols);
    rows.setStyle('width', rowSize - rows[0].getPatch().x);
    for (i = 0, j = rows.length / cols; i < j; i++) {
        var items = rows.slice(i * cols, cols * (i + 1));
        if(items.length) {
            els.each(function(el) {
                setEqualHeight(items.invoke('getElement', el));
            });
        }
    }
}

function getRowsHeight(rows) {
    var maxH = 0;
    rows.each(function(row) {
        if(row) {
            var Y = row.getStyle('height').toInt();
            if(Y > maxH) maxH = Y;
        }
    });
    return maxH;
}
function setEqualHeight(rows, height) {
    if(!height) height = getRowsHeight(rows);
    rows.each(function(row) {
        if(row) {
            var Y = row.getStyle('height').toInt();
            if(Y < height) row.setStyle('height', height);
        }
    });
}

/*fix Image size*/
var fixImageSize = function(images, tag) {
    if (!images && ! images.length) return;
    Array.from(images).each(function(img) {
        if(img.tagName !== 'IMG') {
            img = img.getElement('img');
        }
        if (!img.src) return;
        new Asset.image(img.src, {
            onload: function() {
                var parent = img.getParent(tag);
                if (!this || !this.get('width')) return parent.adopt(img);
                if(parent.getComputedStyle('width') == 'auto') parent = parent.getParent();
                var psize = {
                    x: parseInt(img.getStyle('max-width')) || parent.getSize().x - parent.getPatch('border', 'padding').x,
                    y: parseInt(img.getStyle('max-height')) || parent.getSize().y - parent.getPatch('border', 'padding').y
                };
                if (psize.x <= 0 || psize.y <= 0) return;
                img.zoomImg(psize.x, psize.y);
            }
        });
    });
};
//= 如需在ie6下自动缩放图片，只需为img加入data-img-zoom属性
window.addEvent('domready', function(e){
    fixImageSize($$('img[data-img-zoom]'));
});

function updateCartInfo(data) {
    var number = Memory.get('cart_number');
    var count = Memory.get('cart_count');
    var price = Memory.get('cart_total_price');
    try{
        if(number || number === 0) $$('.op-cart-number').set('text', number);
        if(count || count === 0) $$('.op-cart-count').set('text', count);
        if(price || price === 0) $$('.op-cart-total').set('text', price);
    }catch(e){}
}

(function() {
    browserStore = null;
    withBrowserStore = function(callback) {
        if (browserStore) return callback(browserStore);
        window.addEvent('domready', function() {
            if ((browserStore = new BrowserStore())) {
                callback(browserStore);
            } else {
                window.addEvent('load', function() {
                    callback(browserStore = new BrowserStore());
                });
            }
        });
    };
})();

