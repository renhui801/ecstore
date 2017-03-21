// vim: set et st=4 ts=4 sts=4:
/*------- Author: Tyler Chao===tylerchao.sh@gmail.com -------*/

//＝基本表单验证
var validatorMap = {
    'required': [LANG_formplus['validate']['required'], function(element, v, type) {
        if (type == 'select-one' || type == 'select') {
            var index = element.selectedIndex;
            v = element.options[index].value;
            return index >= 0 && (v != '' && v != '_NULL_');
        }
        return v !== null && v.length !== 0;
        // return v !== null && v != '' && v != '_NULL_';
    }],
    'minLength': [function(element, v, props){
            if (typeOf(props) != 'null') {
                return LANG_formplus['validate']['minLength'].substitute({minLength: props, length: v.length});
            }
            return '';
        }, function(element, v, type, parent, props) {
            if (typeOf(props) != 'null') return v.length >= (props || 0);
            return true;
    }],
    'maxLength': [function(element, v, props){
            if (typeOf(props) != 'null') {
                return LANG_formplus['validate']['maxLength'].substitute({maxLength: props, length: v.length});
            }
            return '';
        }, function(element, v, type, parent, props) {
            if (typeOf(props) != 'null') return v.length <= (props || 10000);
            return true;
    }],
    'number': [LANG_formplus['validate']['number'], function(element, v) {
        return ! isNaN(v) && ! /^\s+$/.test(v);
    }],
    'msn': [LANG_formplus['validate']['msn'], function(element, v) {
        return v === null || v == '' || /\S+@\S+/.test(v);
    }],
    'skype': [LANG_formplus['validate']['skype'], function(element, v) {
        return ! /\W/.test(v) || /^[a-zA-Z0-9]+$/.test(v);
    }],
    'digits': [LANG_formplus['validate']['digits'], function(element, v) {
        return ! /[^\d]/.test(v);
    }],
    'unsignedint': [LANG_formplus['validate']['unsignedint'], function(element, v) {
        return (!/[^\d]/.test(v) && v > 0);
    }],
    'unsigned': [LANG_formplus['validate']['unsigned'], function(element, v) {
        return (!isNaN(v) && ! /^\s+$/.test(v) && v >= 0);
    }],
    'positive': [LANG_formplus['validate']['positive'], function(element, v) {
        return (!isNaN(v) && ! /^\s+$/.test(v) && v > 0);
    }],
    'alpha': [LANG_formplus['validate']['alpha'], function(element, v) {
        return v === null || v == '' || /^[a-zA-Z]+$/.test(v);
    }],
    'alphaint': [LANG_formplus['validate']['alphaint'], function(element, v) {
        return ! /\W/.test(v) || /^[a-zA-Z0-9]+$/.test(v);
    }],
    'alphanum': [LANG_formplus['validate']['alphanum'], function(element, v) {
        return ! /\W/.test(v) || /^[\u4e00-\u9fa5a-zA-Z0-9]+$/.test(v);
    }],
    'unzhstr': [LANG_formplus['validate']['unzhstr'], function(element, v) {
        return ! /\W/.test(v) || ! /^[\u4e00-\u9fa5]+$/.test(v);
    }],
    'date': [LANG_formplus['validate']['date'], function(element, v) {
        return v === null || v == '' || /^(19|20)[0-9]{2}-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12][0-9]|3[01])$/.test(v);
    }],
    'email': [LANG_formplus['validate']['email'], function(element, v) {
        return v === null || v == '' || /^[a-z\d][a-z\d_.]*@[\w-]+(?:\.[a-z]{2,})+$/i.test(v);
    }],
    'emaillist': [LANG_formplus['validate']['email'], function(element, v) {
        return v === null || v == '' || /^(?:[a-z\d][a-z\d_.]*@[\w-]+(?:\.[a-z]{2,})+[,;\s]?)+$/i.test(v);
    }],
    'mobile': [LANG_formplus['validate']['mobile'], function(element, v) {
        return v === null || v == '' || /^0?1[34578]\d{9}$/.test(v);
    }],
    'tel': [LANG_formplus['validate']['tel'], function(element, v) {
        return v === null || v == '' || /^(0\d{2,3}-?)?[2-9]\d{5,7}(-\d{1,5})?$/.test(v);
    }],
    'phone': [LANG_formplus['validate']['phone'], function(element, v) {
        return v === null || v == '' || /^0?1[3458]\d{9}$|^(0\d{2,3}-?)?[2-9]\d{5,7}(-\d{1,5})?$/.test(v);
    }],
    'zip': [LANG_formplus['validate']['zip'], function(element, v) {
        return v === null || v == '' || /^\d{6}$/.test(v);
    }],
    'url': [LANG_formplus['validate']['url'], function(element, v) {
        var pattern = "^((https|http|ftp|rtsp|mms)?://)"
        + "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" //ftp的user@
        + "(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP
        + "|" // 允许IP和域名
        + "([0-9a-z_!~*'()-]+\.)*" // 主机名 www.
        + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." // 二级域名
        + "[a-z]{2,6})" // 顶级域名 .com or .museum
        + "(:[0-9]{1,4})?" // 端口
        + "((/?)|" // 最后的/非必需
        + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$"; //查询参数
        var re=new RegExp(pattern, 'i');
        return v === null || v == '' || re.test(v);
    }],
    'area': [LANG_formplus['validate']['area'], function(element, v) {
        return element.getElements('select').every(function(sel) {
            var selValue = sel.get('value');
            if(!sel.isDisplayed()) return true;
            return selValue != '' && selValue != '_NULL_';
        });
    }],
    'equal': [LANG_formplus['validate']['equal'], function(element, v, type, parent, props){
        var sibling = parent.getElement('[name="' + props + '"]');
        return !sibling || v === sibling.value;
    }],
    'onerequired': [LANG_formplus['validate']['onerequired'], function(element, v, type, parent) {
        var name = element.name;
        return parent.getElements('input' + (name ? '[name="' + name + '"]' : '')).some(function(el) {
            if (['checkbox', 'radio'].contains(el.type)) return el.checked;
            return !!el.value;
        });
    }]
};

//= 统一表单验证入口
var validate = function(container,type, parent) {
    container = (container || container === 0) ? document.id(container) : null;
    type = type || '';
    if (!container) return true;
    var formElements = container.match('form') || type == 'all' ? container.getElements('[vtype], [pattern]') : Array.from(container);
    var errElements = [];
    formElements.each(function(element, i) {
        if(!(element.isVisible() && element.get('vtype'))) return true;

        var vtypes = element.get('vtype').split('&&');

        var pattern = element.get('pattern');
        var re = new RegExp(pattern);
        var msg = element.get('data-caution') || '';
        var ckey = 'custom_' + element.get('uid');
        if(pattern) {
            if(!validatorMap[ckey]) {
                validatorMap[ckey] = [element.get('data-attention') || '', function(element, v){
                    return v === null || v === '' || re.test(v);
                }];
            }
            vtypes = vtypes.clean().include(ckey);
        }

        if (element.get('required')) {
            vtypes = ['required'].combine(vtypes);
            element.erase('required');
        }
        if (!vtypes.length) return true;

       var flag = vtypes.every(function(key, i) {
            var tips = '';
            var keyprops = key.split(':');
            key = keyprops[0];
            if(keyprops.length > 1) {
                var props = keyprops[1];
            }
            var validator = validatorMap[key];
            if (!validator) return true;
            validator = typeOf(validator) === 'array' ? validator : ['', validator];
            if(msg) {
                tips = msg.split('&&');
                tips = tips[i] || '';
            }
            var caution = {
                el: element.getNext('.caution'),
                msg: tips || (typeOf(validator[0]) === 'function' ? validator[0](element, element.get('value'), props) : validator[0])
            };
            validator = validator[1]||function(){return true};
            var input = ['input','select','textarea'].contains(element.get('tag'));

            if (validator(element, element.get('value'), element.type, (container.match('[vtype]') || container.match('[pattern]')) ? parent || container.getParent('form') || container.getParent() : container, props)) {
                if(caution.el) {
                    element.retrieve('tips_instance', {hide:function(){}}).hide(true);
                }
                input && element.removeClass('caution-input');
                return true;
            }else{//(caution.el ? caution.el.addClass('error') : new Element('span.error.caution.notice-inline').inject(element, 'after')).set('html', caution.msg);
                caution.el && caution.el.destroy();
                if (caution.msg) {
                    formTips.error(caution.msg,element).store();
                }
                if (element.bindEvent !== false) {
                    var evt = input?'onblur':'onmouseout';
                    element[evt] = function(event) {
                        var e = event || window.event;
                        if(!(/mouseout$/i.test(e.type) && this.contains($(e.target || e.srcElement)))) {
                            validate(this, '', (container.match('[vtype]') || container.match('[pattern]')) ? container.getParent('form') || container.getParent() : container);
                            caution.el && this.retrieve('tips_instance', {hide: function(){}}).hide(true);
                            input && this.removeClass('caution-input');
                        }
                        this[evt] = null;
                    };
                }
                input && element.addClass('caution-input');
                return false;
            }

        });
        if(!flag) errElements.push(element);
    });

    if(errElements.length){
        errElements.shift().focus();
        return false;
    }
    return true;
};

//= 验证提示信息框
var formTips = new Class({
    Implements: Options,
    options: {
        form: 'inline',
        type: 'error', // warn, error, notice, success
        'class': 'notice-inline',
        msg: '',
        target: document.body,
        /*where: null,
        single: false,
        store: false,*/
        destroy: false,
        position: ['ct', 'cb'],
        offset: [0,-9],
        intoView: true,
        autohide: 3
    },
    initialize: function(options) {
        this.setOptions(options);
        this.hidden = true;
        // this.toElement();
        return this;
    },
    toElement: function() {
        if(!this.element) {
            var options = this.options;
            this.uid = options.target.get('uid');
            this.uid++;
            var tag = options.form == 'inline' ? 'span' : 'div';
            var id = '_build_tips_' + (options.form ? options.form : '') + '_' + options.type + '_' + this.uid;
            this.element = options.single && document.id(id) ? document.id(id) : new Element(tag, {
                id: id,
                'class': 'caution '+ options.type + ' ' + options['class'],
                'style': 'display:none;',
                'html': '<q class="icon">' + (options.type === 'success' ? '&#x25;' : '&#x21;') + '</q><span class="caution-content"></span>'
            });
            this.element.inject(options.target, options.where);
        }
        return this.element;
    },
    store: function (element) {
        ($(element) || this.options.target).store('tips_instance', this);
        return this;
    },
    eliminate: function (element) {
        ($(element) || this.options.target).eliminate('tips_instance');
        return this;
    },
    position: function(options) {
        if(!this.element) document.id(this);
        var position = {
            target: options.target,
            from: tyeOf(options.position) == 'array' ? options.position[0] : 'cb', //此元素定位基点 --为数值时类似offset
            to: tyeOf(options.position) == 'array' ? options.position[0] : options.position, //定位到目标元素的基点
            offset: options.offset // 偏移量
        }
        return this.element.position(position);
    },
    show: function(msg, options) {
        if(typeOf(msg) == 'object') {
            options = msg;
            msg = options.msg;
        }
        if(!this.hidden) return this;
        options = Object.merge(this.options, options||{});
        if(!this.element) document.id(this);
        if(options.form && options.form != 'inline') this.element.position(options);
        if (options.intoView) try {
            new Fx.Scroll(document, {link:'cancel', duration: 300}).toElementEdge(this.element);
        } catch(e) {}

        if(msg) this.element.getElement('.caution-content').innerHTML = msg;
        this.element.show();
        this.hidden = false;
        if(!isNaN(options.autohide) && options.autohide > 0) {
            clearTimeout(this.timer);
            this.timer = this.hide.delay(options.autohide * 1000, this);
        }
        return this.options.store ? this.store(this.options.store) : this;
    },
    hide: function(destroy) {
        destroy = destroy || this.options.destroy;
        if(this.hidden) return this;
        if(!this.element) document.id(this);
        // if(this.element) {
        if(destroy !== false) {
            this.element.destroy();
            this.element = null;
        }
        else this.element.hide();
        // }
        this.hidden = true;
        return this.eliminate(this.options.store);
    }
}).extend({
    tip: function(element, msg) {
        return new formTips({type:'notice', target: element || document.body, where: 'after', autohide: 0}).show(msg);
    },
    error: function(msg, element, options) {
        return new formTips({type: 'error', target: element || document.body, where: 'after', autohide: 0}).show(msg, options);
    },
    success: function(msg, element, options) {
        return new formTips({type:'success', target: element || document.body, where: 'after', autohide: 0}).show(msg, options);
    },
    warn: function(msg, element, options) {
        return new formTips({type: 'warn', target: element || document.body, where: 'top', autohide: 0, 'class': 'caution-inline', single: true, store: true}).show(msg, options);
    }
});

//=密码强度检测
var passwordStrength = function(value, key, className){
    //最小最大长度
    var minLength = 6;
    var maxLength = 20;

    //密码复杂度定义
    var lower = /[a-z]/g;
    var upper = /[A-Z]/g;
    var numberic = /\d/g;
    var symbols = /[\W_]/g;
    var repeat = new RegExp('(.{' + parseInt(value.length / 2) + ',})\1', 'g');

    //初始状态
    var status = 'poor';
    var strength = -1;

    if(!value || value.length < minLength) {
        strength = -1;
    }
    else {
        strength = parseInt(value.length / minLength) - 1;
    }
    if(value.match(repeat)) {
        strength --;
    }
    if(value.match(lower) || value.match(upper)) {
        strength ++;
    }
    if(value.match(numberic)) {
        strength ++;
    }
    if(value.match(symbols)) {
        strength ++;
    }
    if(value.length > minLength && strength < 2) {
        strength ++;
    }

    switch(strength) {
        case -1:
        case 0:
        case 1:
            status = 'poor';
            break;
        case 2:
            status = 'weak';
            break;
        case 3:
            status = 'good';
            break;
        default:
            status = 'strong';
            break;
    }
    key.className = (className ? className + ' ' : '') + 'password-' + status;
}.extend({
    init: function(element, key) {
        Array.from(element).each(function(el){
            if(!el) return;
            if(key) {
                key = el.parentNode.getElement(key);
            }
            else {
                key = el.getNext();
            }
            if(!key) return;
            key.style.visibility = 'visible';
            var className = key.className;
            el.addEvent('inputchange', function(e){
                var prev = passwordStrength.prev;
                var value = this.value;
                if(prev !== value) {
                    passwordStrength(value, key, className);
                }
                passwordStrength.prev = value;
            });
        });
    }
});

//点击更换验证码
function changeVerify(element, hasEvent) {
    Array.from(element).each(function(el){
        var url;
        var img;
        if(el.tagName === 'IMG') {
            img = el;
            url = el.getAttribute('src').split('?')[0];
        }
        else if(el.tagName === 'A') {
            img = el.getPrevious('img');
            url = el.getAttribute('href');
        }
        if(hasEvent) el.addEvent('click', changeCode.bind(el, img, url));
        else changeCode(img, url);
    });
}
function changeCode(img, url){
    url = url || img.src.split('?')[0];
    var random = +new Date;
    img.src = url + '?' + random;
    return false;
}
//=全选
function checkAll(el, elements) {
    elements.set('checked', el.checked);
}

//= placeholder兼容性实现
//页面初始化时对所有input做初始化
//Placeholder.init();
//或者单独设置某个元素
//Placeholder.create($('t1'));
var Placeholder = {
    support: (function() {
        return 'placeholder' in document.createElement('input');
    })(),
    //提示文字的样式
    className: 'placeholder',
    init: function() {
        if (!this.support) {
            this.create($$('input, textarea'));
        }
    },
    build: function(input, html) {
        var parent = input.getParent();
        var $this = Placeholder;
        if(parent.getStyle('position') == 'static') {
            parent.setStyle('position', 'relative');
        }
        var placeholder = input.getPrevious('.' + this.className) || new Element('span.' + this.className, {
            html: html,
            style: 'visibility:hidden;'
        }).inject(input, 'before')
        .position({target: input, from:'lc', to:'lc', offset:{x:4}, offsetParent:true})
        .addEvent('click', function(e){
            $this.hide(this);
            input.focus();
        });
        return placeholder;
    },
    create: function(inputs) {
        var $this = this;
        Array.from(inputs).each(function(el){
            if (!$this.support && el.get && el.get('placeholder')) {
                var value = el.get('placeholder');
                el.store('placeholder', $this.build(el, value));

                $this.show(el);

                el.addEvents({
                    'focusin': function(e) {
                        $this.hide(this);
                    },
                    'focusout': function(e) {
                        $this.show(this);
                    }
                });
            }
        });
    },
    show: function(el) {
        if(!this.support && el.value === '' && el.isVisible() && el.getStyle('visibility') !== 'hidden') {
            el.retrieve('placeholder').setStyle('visibility', 'visible');
        }
    },
    hide: function(el) {
        if(!this.support) el.retrieve('placeholder', el).setStyle('visibility', 'hidden');
    }
};

window.addEvent('domready', function(e){
    var forms = document.forms;
    if(forms.length) {
        //= 自动检测密码强度
        passwordStrength.init($$('form .auto-password-check-handle'));
        //= 自动绑定更换验证码
        changeVerify($$('form .auto-change-verify-handle'), true);
        //= 记住帐号
        $$('form .action-remember-account').each(function(el){
            el.addEvent('change',function(e){
                if(this.checked) {
                    Memory.set('sign.remember', '1', 365);
                }
                else {
                    Memory.set('sign.remember', '0', 365);
                }
            }).fireEvent('change');
        });
        //= 记住密码
        $$('form .action-auto-signin').addEvent('change',function(e){
            if(this.checked) {
               Memory.set('sign.auto', '1', 14);
            }
            else {
                Memory.clean('sign.auto');
            }
        });
    }
    Placeholder.init();
});

(function() {
    var disabled = 'disabled',
        attr = 'rel';

    this.Sync = new Class({
        Extends: Request.HTML,
        options: {
            disabled: disabled,
            evalScripts: true,
            /*syncCache: false,
            inject: null,
            tipHidden: false,*/
            hideDelay: 3,
            showMessage: true,
            position: 'before',
            tipCls: '-tip',
            ajaxTip: 'ajax-tip'
        },
        initialize: function(target, options) {
            this.sponsor = target;
            if (target) options = this._getOptions(target, options);
            this.parent(options);
        },
        _getOptions: function(target, options) {
            options = options || {};
            var _options;
            try {
                _options = JSON.decode(target.get('data-ajax-config')) || {};
            } catch(e) {
                _options = {};
            }

            var dataForm, opt, isSubmit = target.type === 'submit';

            if (isSubmit) dataForm = this.dataForm = target.getParent('form');
            if (dataForm) opt = {
                data: dataForm.toQueryString() + '&response_json=true',
                url: dataForm.action,
                method: dataForm.method || 'post'
            };
            else opt = {
                url: target.get('href'),
                data: 'response_json=true',
                method: 'get'
            };

            _options = Object.merge(opt, options, target.retrieve('_ajax_config', {}), _options);
            return _options;
        },
        _defaultState: function() {
            this.sponsor && this.sponsor.removeClass(this.options.disabled);
        },
        onFailure: function() {
            this._defaultState();
            this.parent();
        },
        _getCache: function(sponsor) {
            return sponsor.retrieve('ajax_cache', false);
        },
        _clearCache: function(sponsor) {
            sponsor.eliminate('ajax_cache');
        },
        _setCache: function(sponsor, value) {
            sponsor.store('ajax_cache', value);
        },
        _progressCache: function(sponsor) {
            var cache = this._getCache(sponsor);
            if(!this.options.syncCache || !cache) return false;
            cache.success(cache.response.data);
            return true;
        },
        success: function(text, xml) {
            this.response.data = text;
            if ((/text\/jcmd/).test(this.getHeader('Content-type'))) return this._jsonSuccess(text);

            if (['update', 'append', 'filter'].some(function(n) {
                return this.options[n];
            },this)) return this.parent(text, xml);

            return this.onSuccess(this.processScripts(text), xml);
        },
        _jsonSuccess: function(text) {
            try {
                text = this.response.json = JSON.decode(text, this.options.secure);
            } catch(e) {}
            this.onSuccess(text);
        },
        onSuccess: function(text) {
            this._defaultState();
            if(this.options.syncCache && this.sponsor) this._setCache(this.sponsor, this);
            if (this.response.json) this._progress(text);
            this.parent(arguments);
        },
        _progress: function(rs) {
            if (!rs) return;
            if (this.options.progress) return this.options.progress.call(this, rs);
            var redirect = rs.redirect;
            var msg;

            if (!this.options.showMessage || ['error', 'success'].every(function(v) {
                msg = rs[v];
                // var show = true;
                // if(v === 'success' && !this.options.showSuccess) show = false;
                if (!msg) return true;
                if (this.options.inject) {
                    if (v === this.options.tipHidden) this._clearTip(v, msg);
                    else this._injectTip(v, msg);
                }
                else Message(msg, v, this.options.hideDelay, callback);
                // else return true;
                return false;
            }, this)) callback();

            function callback(){
                if (redirect) {
                    if (redirect == 'back') history.back();
                    else if (redirect == 'reload') location.reload();
                    else location.href = redirect;
                }
            }
        },
        _clearTip: function() {
            if (!this.inject || !this.tipElem) return;
            this.tipElem.destroy();
        },
        _injectTip: function(cls, html) {
            var options = this.options,
                inject = this.inject = document.id(options.inject),
                position = options.position,
                ajaxTip = options.ajaxTip,
                tipCls = options.tipCls,
                cls = cls + tipCls,
                tipBox;

            if (!inject) return;
            tipBox = inject.getParent();
            this.tipElem = tipBox.getElement('.' + ajaxTip);
            if (tipBox && this.tipElem) return this.tipElem.set('html', html);
            new Element('div.' + cls + '.' + ajaxTip, {'html': html}).inject(inject, position);
        },
        _request: function(sponsor) {
            sponsor.addClass(this.options.disabled);
        },
        _isCheck: function(options) {
            options = options || {};
            var dataElem = this.dataForm || options.data || this.options.data;

            if (typeOf(dataElem) === 'element' && !validate(dataElem)) return false;
            return true;
        },
        send: function(options) {
            var target = this.sponsor;
            if (target) {
                if (target.hasClass(this.options.disabled) || !this._isCheck(options) || this._progressCache(target)) return;
                this._request(target);
            }
            this.parent(options);
        }
    });

    this.async = function(elem, form, options) {
        if (elem.hasClass(disabled)) return false;
        if(typeOf(form) === 'object') {
            options = form;
            form = null;
        }
        else if (typeOf(form) === 'element') {
            if (!validate(form)) {
                elem.removeClass(disabled);
                return false;
            }
            if(options && options.async === false) return;// elem.addClass(disabled);
        }
        else {
            var sync = elem.retrieve('ajax_cache', false);
            if (sync) return sync.send();
        }
        new Sync(elem, options).send();
    };

    this.Event_Group = {
        _request: {
            fn: async
        }
    };

    $(document.html).addEvent('click', function(e) {
        var target = $(e.target),
            elem = target.nearest('type', 'submit') || target.nearest(attr);
        if (!elem || elem.nodeType === 9 || elem.disabled) return;
        var form = elem.getParent('form');
        if(form && form.get('async') === 'false') return;
        if (elem.type === 'submit' && form && form.get('target')) return async(elem, form, {async: false});
        if (elem.type === 'submit' && elem.get(attr) !== '_request') {
            return async(elem, form);
        }

        var type = elem.get(attr),
            eventType = Event_Group[type];
        if (eventType) {
            var fn = eventType['fn'],
                loader = eventType['loader'];

            e.preventDefault();
            if ($(elem).get && $(elem).get(type)) return elem;

            if (loader) {
                $LAB.script(loader).wait(function() {
                    fn && fn(elem, form);
                });
            }
            else {
                fn && fn(elem, form);
            }
        }
    });

})();

