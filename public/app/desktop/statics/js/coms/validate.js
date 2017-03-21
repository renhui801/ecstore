var validatorMap = new Hash({
    'required': [LANG_Validate['required'], function(element, v, type) {
        if (type == 'select-one' || type == 'select') {
            var index = element.selectedIndex;
            v = element.options[index].value;
            return index >= 0 && (v != '' && v != '_NULL_');
        }
        return v !== null && v.length !== 0;
    }],
    'number': [LANG_Validate['number'], function(element, v) {
        return v == null || v == '' || ! isNaN(v) && ! /^\s+$/.test(v);
    }],
    'digits': [LANG_Validate['digits'], function(element, v) {
        return v == null || v == '' || ! /[^\d]/.test(v);
    }],
    'unsignedint': [LANG_Validate['unsignedint'], function(element, v) {
        return v == null || v == '' || (!/[^\d]/.test(v) && v > 0);
    }],
    'unsigned': [LANG_Validate['unsigned'], function(element, v) {
        return v == null || v == '' || (!isNaN(v) && ! /^\s+$/.test(v) && v >= 0);
    }],
    'positive': [LANG_Validate['positive'], function(element, v) {
        return v == null || v == '' || (!isNaN(v) && ! /^\s+$/.test(v) && v > 0);
    }],
    'alpha': [LANG_Validate['alpha'], function(element, v) {
        return v == null || v == '' || /^[a-zA-Z]+$/.test(v);
    }],
    'alphaint': [LANG_Validate['alphaint'], function(element, v) {
        return v == null || v == '' || ! /\W/.test(v) || /^[a-zA-Z0-9]+$/.test(v);
    }],
    'alphanum': [LANG_Validate['alphanum'], function(element, v) {
        return v == null || v == '' || ! /\W/.test(v) || /^[\u4e00-\u9fa5a-zA-Z0-9]+$/.test(v);
    }],
    'date': [LANG_Validate['date'], function(element, v) {
        return v == null || v == '' || /^(19|20)[0-9]{2}-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12][0-9]|3[01])$/.test(v);
    }],
    'email': [LANG_Validate['email'], function(element, v) {
        return v == null || v == '' || /(\S)+[@]{1}(\S)+[.]{1}(\w)+/.test(v);
    }],
    'mobile': [LANG_Validate['mobile'], function(element, v) {
        return v == null || v == '' || /^0?1[3458]\d{9}$/.test(v);
    }],
    'tel': [LANG_Validate['tel'], function(element, v) {
        return v == null || v == '' || /^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/.test(v);
    }],
    'phone': [LANG_Validate['phone'], function(element, v) {
        return v == null || v == '' || /^0?1[3458]\d{9}$|^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/.test(v);
    }],
    'zip': [LANG_Validate['zip'], function(element, v) {
        return v == null || v == '' || /^\d{6}$/.test(v);
    }],
    'url': [LANG_Validate['url'], function(element, v) {
        return v == null || v == '' || /^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*)(:(\d+))?\/?/i.test(v);
    }],
    'area': [LANG_Validate['area'], function(element, v) {
        return element.getElements('select').every(function(sel) {
            if(sel.isDisplay()) {
                var selValue = sel.getValue();
                sel.focus();
                return selValue != '' && selValue != '_NULL_';
            }
            else return true;
        });
    }],
    'greater': [LANG_Validate['greater'], function(element) {
        var prev=element.getPrevious('input[type=text]');
        return  element.getValue()==='' || element.getValue().toInt()>prev.getValue().toInt();
    }],
    'requiredcheckbox': [LANG_Validate['requiredonly'], function(element, v) {
        var parent =  element.getParent();
        var name = element.name;
        return parent.getElements('input[type=checkbox]' + (name ? '[name="' + name + '"]' : '')).some(function(el) {
            return el.checked === true;
        });
    }],
    'requiredradio': [LANG_Validate['requiredonly'], function(element,v) {
        var parent =  element.getParent();
        var name = element.name;
        return parent.getElements('input[type=radio]' + (name ? '[name="' + name + '"]' : '')).some(function(el) {
            return el.checked === true;
        });
    }]
});

var validate = function(_form) {
    if (!_form) return true;
    var formElements = _form.match('form') ? _form.getElements('[vtype]') : [_form];
    var err_log = false;
    var _return = formElements.every(function(element) {
        var vtype = element.get('vtype');
        if (!$chk(vtype)) return true;
        if (!element.isDisplay() && element.get('type') != 'hidden') return true;
        var valiteArr = vtype.split('&&');
        if (element.get('required')) {
            valiteArr = ['required'].combine(valiteArr.clean());
        }
        return vtype.split('&&').every(function(key) {
            if (!validatorMap[key]) return true;
            var _caution = element.getNext('.caution');
            var cautionInnerHTML = element.get('caution') || validatorMap[key][0];
            if (validatorMap[key][1](element, element.getValue())) {
                if (_caution && _caution.hasClass('error')) {
                    _caution.remove();
                }
                return true;
            }
            if (!_caution || ! _caution.hasClass('caution')) {
                new Element('span', {
                    'class': 'error caution notice-inline',
                    'html': cautionInnerHTML
                }).injectAfter(element);
                //由于时间控件失焦时无法移除，故换用onblur绑定事件 -- by Tyler Chao
                element.onblur = function() {
                    if (validate(element)) {
                        if (_caution && _caution.hasClass('error')) {
                            _caution.remove();
                        }
                    }
                }
            } else if (_caution && _caution.hasClass('caution') && _caution.get('html') != cautionInnerHTML) {
                _caution.set('html', cautionInnerHTML);
            }
            if(element.type!='hidden'&&element.isDisplay()&&!err_log)err_log=element;
            return false;
        });
    });
    if(_form.match('form')&&err_log){try{err_log.focus();}catch(e){}}
    return _return;
};
