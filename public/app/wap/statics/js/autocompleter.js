(function($) {

// event type detect
var eventType = function(e) {
    var touch = $.os.phone || $.os.tablet;
    var evt = {
        click : 'touchend',
        mousedown: 'touchstart',
        mouseup: 'touchend',
        mousemove: 'touchmove',
        mouseover: 'touchstart',
        mouseout: 'touchend',
        mouseenter: 'touchstart',
        mouseleave: 'touchend'
    };
    return touch ? evt[e] : e;
};

var plugin_name = "autocompletion", defaults = {
    caching: true,
    delay: 500,
    postVar: '',
    container: '<ul class="autocompletion"></ul>',
    item: '<li class="autocompletion-item"></li>',
    shim: '<div class="autocompletion-shim"></div>',
    source: []
};
function Plugin(element, options) {
    this.options = $.extend({}, defaults, options);
    this.customize = this.options.customize || this.customize;
    this.fill = this.options.fill || this.fill;
    this.$container = $(this.options.container);
    this.$element = $(element);
    // this.$shim = $(this.options.shim);
    this._attr_value = "data-item-value";
    this._cache = {};
    // this._class_current = "current";
    this._defaults = defaults;
    this._name = plugin_name;
    this.init();
}
Plugin.prototype = {
    init: function() {
        this.bind();
    },
    bind: function() {
        var that = this, item_selector = "[" + this._attr_value + "]";
        this.$element.on("blur", $.proxy(this.blur, this))
        .on("input", function(){setTimeout($.proxy(that.keyup, that), that.options.delay);})
        .on('changes', $.proxy(this.change, this));
        this.$container.on(eventType("mouseenter"), function() {
            that.mousein = true;
        }).on(eventType("mouseleave"), function() {
            that.mousein = false;
        // }).on("mouseenter", item_selector, function(e) {
        // that.$container.find("." + that._class_current).removeClass(that._class_current);
        // $(e.currentTarget).addClass(that._class_current);
        }).on(eventType("click"), item_selector, $.proxy(this.click, this));
    },
    blur: function() {
        // Hide only when cursor outside of the container.
        // This is to ensure that the browser did not hide container before the clue clicked.
        if (!this.mousein) {
            this.hide();
        }
    },
    keyup: function() {
        var source = this.options.source;
        this.q = this.$element.val();
        this.q_lower = this.q.toLowerCase();
        var that = this;
        if (!this.q) {
            return this.hide();
        }
        if (this.options.caching && this._cache[this.q_lower]) {
            // pass to render method directly
            this.render(this._cache[this.q_lower]);
        } else if (typeof(source) === 'string') {
            $.post(source, '' + this.options.postVar + '=' + encodeURIComponent(this.q_lower), function(rs) {
                if(rs) {
                    source = JSON.parse(rs);
                    that.suggest(source);
                }
            });
        } else if ($.isFunction(source)) {
            // if it's a function, then run it and pass context
            source(this.q, $.proxy(this.suggest, this));
        } else if($.isArray(source)) {
            this.suggest(source);
        }
    },
    click: function(e) {
        e.stopPropagation();
        e.preventDefault();
        this.select(e);
    },
    change: function(e) {
        this.$element.parents('form')[0].submit();
    },
    suggest: function(items) {
        var that = this,
        filtered_items = $.grep(items, function(item) {
            return item.toLowerCase().indexOf(that.q_lower) !== -1;
        });
        // cache if needed
        if (this.options.caching) {
            this._cache[this.q_lower] = filtered_items;
        }
        this.render(filtered_items);
    },
    render: function(items) {
        if (!items.length) {
            return this.hide();
        }
        var that = this,
            items_dom = $.map(items, function(item) {
                return $(that.options.item).attr(that._attr_value, item).html(that.highlight(item))[0];
            }), position = this.$element.position();
        // render container body
        var css = {
            left: position.left + "px",
            top: position.top + this.$element.height() + "px"
        };
        this.customize(this.$container.css(css).html(items_dom)[0]);
        this.$container.insertAfter(this.$element);
        // this.$shim.css(css).insertAfter(this.$container);
        this.show();
    },
    highlight: function(item) {
        var q = this.q.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
        return item.replace(new RegExp("(" + q + ")", "ig"), function($1, match) {
            return "<strong>" + match + "</strong>";
        });
    },
    customize: function(clues) {
        return;
    },
    select: function(e) {
        var $el = $(e.target), //this.$container.find("." + this._class_current),
        value = $el.attr(this._attr_value);
        if (value) {
            this.hide();
            this.$element.val(value).trigger('changes');
        }
    },
    fill: function(value) {
        return value;
    },
    show: function() {
        if (!this.visible) {
            this.visible = true;
            this.$container.show();
            // this.$shim.css({
            //     width: this.$container.width(),
            //     height: this.$container.height()
            // }).show();
        }
    },
    hide: function() {
        if (this.visible) {
            this.visible = false;
            this.$container.hide();
            // this.$shim.hide();
        }
    }
};
$.fn[plugin_name] = function(options) {
    return this.each(function() {
        if (!$(this).data("plugin_" + plugin_name)) {
            $(this).data("plugin_" + plugin_name, new Plugin(this, options));
        }
    });
};

})(Zepto);
