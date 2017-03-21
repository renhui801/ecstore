// vim: set et st=4 ts=4 sts=4:
/*------- Author: Tyler Chao===tylerchao.sh@gmail.com -------*/

/*商品详细通用函数*/
var priceControl = {
    spec: {
        "decimals":2,
        "dec_point":".",
        "thousands_sep":"",
        "fonttend_decimal_type":0,
        "fonttend_decimal_remain":2,
        "sign":"\uffe5"
    },
    format: function(num, force) {
        var part;
        var sign = this.spec.sign || '';
        if (!(num || num === 0) || isNaN(+num)) return num;
        var num = parseFloat(num);
        if (this.spec.cur_rate) {
            num = num * this.spec.cur_rate;
        }
        num = num.round(this.spec.decimals) + '';
        var p = num.indexOf('.');
        if (p < 0) {
            p = num.length;
            part = '';
        } else {
            part = num.substr(p + 1);
        }
        while (part.length < this.spec.decimals) {
            part += '0';
        }
        var curr = [];
        while (p > 0) {
            if (p > 2) {
                p -= 3;
                curr.unshift(num.substr(p, 3));
            } else {
                curr.unshift(num.substr(0, p));
                break;
            }
        }
        if (!part) {
            this.spec.dec_point = '';
        }
        if (force) {
            sign = '<span class="price-currency">' + sign + '</span>';
        }
        return sign + curr.join(this.spec.thousands_sep) + this.spec.dec_point + part;
    },
    number: function(format) {
        if (!format) return null;
        if (isNaN(+format)) {
            if (typeOf(format) === 'element') format = format.get(format.tagName === 'INPUT' ? 'value': 'text');
            if (format.indexOf(this.spec.sign) == 0) format = format.split(this.spec.sign)[1];
        }
        return +format;
    },
    calc: function(calc, n1, n2, noformat) {
        if (!(n1 || n1 === 0)) return null;
        if (!n2) {
            n1 = this.number(n1);
        }
        else {
            calc = !calc || calc == 'add' ? 1 : - 1;
            var t1 = 1,
            t2 = 1;
            if (typeOf(n1) === 'array') {
                t1 = n1[1];
                n1 = n1[0];
            }
            if (typeOf(n2) === 'array') {
                t2 = n2[1];
                n2 = n2[0];
            }
            var decimals = Math.pow(10, this.spec.decimals * this.spec.decimals);
            n1 = Math.abs(t1 * decimals * this.number(n1) + calc * t2 * decimals * this.number(n2)) / decimals;
        }
        if (!noformat) n1 = this.format(n1);
        return n1;
    },
    add: function(n1, n2, flag) {
        return this.calc('add', n1, n2, flag);
    },
    diff: function(n1, n2, flag) {
        return this.calc('diff', n1, n2, flag);
    }
};

function bindDatepicker(elements) {
    if(!elements) elements = 'input.calendar';
    elements = $$(elements);
    var path = window.Shop ? Shop.url.datepicker : '';
    if(elements.length) {
        try{
            $LAB.script(path + '/datepicker.js').wait(function(){
                elements.each(function(el){
                    var options;
                    try{
                        options = JSON.decode(el.get('data-calendar-options'));
                    } catch(e){}
                    new DatePickers(el, options);
                });
            });
        }catch(e){}
    }
}

window.addEvent('domready', function() {

    //= 注册datepicker
    bindDatepicker();

    //= 加入收藏夹
    var fav_url = window['Shop'] ? Shop.url.fav_url : '';
    var MEMBER = Memory.get('member');
    var FAVCOOKIE = new Memory('gfav.' + MEMBER, 365);

    var setStar = function(item, gid, set) {
        if (!gid) return;
        if (item.hasClass('fav-on')) return;
        var p = item.getParent('.p-action');
        if(set) return setText(p);
        // FAVCOOKIE.write(Array.from((FAVCOOKIE.read() || '').split(',')).include(gid).clean().join(','));
        var _type = item.get('_type') ? item.get('_type') : 'goods';
        new Request({
            url: fav_url,
            onRequest: function() {
                this.FAVHTML = p.innerHTML;
                p.innerHTML = '<span class="fav-loading">收藏中...</span>';
            },
            onSuccess: function(rs) {
                rs = JSON.decode(rs);
                if (rs && rs.success) {
                    p.innerHTML = '<span class="fav-success"><q class="icon">&#x25;</q> 收藏成功</span>';
                    setText.delay(2000, this, p);
                }else p.innerHTML = this.FAVHTML;
            }
        }).post({
            type: _type,
            gid: gid
        });
        function setText(p){
            p.innerHTML = '<span class="fav-on">已收藏</span> <i>|</i> <a href="javascript:void(0);" class="btn-delete">移除</a>';
        }
    };

    var setFav = function(item, gid, set){
        if(!gid) return;
        if(set) return setText();
        // FAVCOOKIE.write(Array.from((FAVCOOKIE.read() || '').split(',')).include(gid).clean().join(','));
        var _type = item.get('data-type') ? item.get('data-type') : 'goods';
        new Request({
            url: fav_url,
            onRequest: function() {
                this.FAVHTML = item.innerHTML;
                item.innerHTML = '<span class="fav-loading">收藏中...</span>';
            },
            onSuccess: function(rs) {
                rs = JSON.decode(rs);
                if (rs.success) {
                    setText();
                }
            }
        }).post({
            type: _type,
            gid: gid
        });
        function setText() {
            var el = item.getParent().getElement('[rev=' + item.get('rel') + ']');
            el.setStyle('display', '').replaces(item);
        }
    }

    var splatFC = Array.from((FAVCOOKIE.read() || '').split(','));
    $$('a[rel=_addfav_]').each(function(item){
        var GID = item.get('data-gid');
        if (splatFC.contains(GID)) {
            setStar(item, GID, true);
        }
    });

    $$('[rel=_favbtn_]').each(function(item){
        var GID = item.get('data-gid');
        if (splatFC.contains(GID)) {
            setFav(item, GID, true);
        }
    });

    if(window.Event_Group) {
        Object.merge(Event_Group, {
            '_addfav_': {
                fn: function(e) {
                    // e.stop && e.stop();
                    var el = $(e.target) || $(e);
                    setStar(el, el.get('data-gid'));
                    return false;
                }
            },
            '_favbtn_': {
                fn: function(e) {
                    // e.stop && e.stop();
                    var el = $(e.target) || $(e);
                    setFav(el, el.get('data-gid'));
                    return false;
                }
            }
        });
    }
});

//星星评分效果
function starGrade(container, value, itemEl){
    container = $(container);
    value = parseInt(value);
    if(!container) return;
    var items = container.getElements(itemEl || '.scores-item');

    items.each(function(item){
        var stari = item.getElement('.stars');
        var stars = item.getElements('[class*=star-]');
        var input = item.getElement("input[type=hidden]");
        var score = item.getElement('.score');
        if(value || value === 0) {
            stari.className = 'stars stars-' + value;
            score.set('text', value);
            input.value = value;
            return;
        }
        stari.addEvent('mouseleave', function(){
            var init = input.value;
            this.className = 'stars stars-' + init;
            score.set('text', init);
        });
        stars.each(function(star, i){
            star.addEvents({
                'mouseenter': function(){
                    this.getParent('.stars').className = 'stars stars-' + (i + 1);
                    score.set('text', i + 1);
                },
                'click': function(){
                    input.value = i + 1;
                }
            });
        });
    });
}
