var DatePickers = new Class({

    Implements: [Events, Options],

    options: {
        onShow: function(datepicker){
            if(Browser.ie6) $$('select').setStyle('visibility','hidden');
            datepicker.setStyle('visibility', 'visible');
        },
        onHide: function(datepicker){
            if(Browser.ie6) $$('select').setStyle('visibility','visible');
            datepicker.setStyles({'visibility': 'hidden', 'left': '', 'top': ''});
        },
        showDelay: 100,
        hideDelay: 100,
        className: 'datepicker',
        offsets: {x: 0, y: 0},

        dateformat: '-',

        days: LANG_formplus['days'], // days of the week starting at sunday
        months: LANG_formplus['months'],
        year: LANG_formplus['year'],
        weekFirstDay : 1 // first day of the week: 0 = sunday, 1 = monday, etc..
    },

    initialize: function(elements, options){
        this.setOptions(options || {});
        this.lock = false;

        this.build();

        if (elements || elements.length){
            $$(elements).each(function(el){
                if(!el.retrieve('bindDatePicker')) {
                    el.store('bindDatePicker',true);
                    this.attach(el);
                }
            }, this);
        }
    },

    build: function(){
        this.datepicker = new Element('div.' + this.options.className).addEvents({
            'mouseover': function(){
                this.lock = true;
            }.bind(this),
            'mouseout': function(){
                this.lock = false;
            }.bind(this)
        });

        // var top = new Element('div.' + this.options.className + '-top').inject(this.datepicker);
        this.container = new Element('div.' + this.options.className + '-content');
        // var bottom = new Element('div.' + this.options.className + '-bottom').inject(this.datepicker);
        this.table = new Element('table[cellpadding=0][cellspacing=0]');
        this.table.inject(this.container.inject(this.datepicker));
    },

    attach: function(element){
        // $$(elements).each(function(element){
        var dateformat = element.retrieve('datepicker:dateformat', element.get('accept'));
        if(!dateformat) {
            dateformat = this.options.dateformat;
            element.store('datepicker:dateformat', dateformat);
        }
        var datevalue = element.retrieve('datepicker:datevalue', element.value);
        if(!datevalue) {
            datevalue = this.format(new Date(), dateformat);
            element.store('datepicker:datevalue', datevalue);
        }
        element.store('datepicker:current', this.unformat(datevalue,dateformat));

        var inputFocus = element.retrieve('datepicker:focus', this.elementFocus);
        var inputBlur = element.retrieve('datepicker:blur', this.elementBlur);
        element.addEvents({focus: inputFocus.bind(this, element), blur: inputBlur.bind(this)});

        element.store('datepicker:native', element.get('accept'));
        element.erase('dateformat');
        // }, this);
        return this;
    },

    detach: function(elements){
        $$(elements).each(function(element){
            element.removeEvent('onfocus', element.retrieve('datepicker:focus') || function(){});
            element.removeEvent('onblur', element.retrieve('datepicker:blur') || function(){});
            element.eliminate('datepicker:focus').eliminate('datepicker:blur');
            var original = element.retrieve('datepicker:native');
            if (original) element.set('dateformat', original);
        });
        return this;
    },

    elementFocus: function(element){
        if(!this.datepicker.retrieve('injected')){
           this.datepicker.inject(document.id(this.options.inject) || document.body);
           this.datepicker.store('injected',true);
       }
       this.el = element;

       var current = element.retrieve('datepicker:current');
       this.curFullYear = current[0];
       this.curMonth = current[1];
       this.curDate = current[2];

       this.refresh();

       this.timer = clearTimeout(this.timer);
       this.timer = this.show.delay(this.options.showDelay, this);

       this.position(element);
    },

    elementChange: function() {
        if(this.el.get('real')){
            var curDateObj = new Date(this.curFullYear, this.curMonth, this.curDate);
            var now = new Date();

            if(curDateObj<new Date(now.getFullYear(),now.getMonth(),now.getDate())){
                return alert(LANG_formplus['dateerror']);
            }
        }

        this.el.store('datepicker:current', [this.curFullYear,this.curMonth,this.curDate]);
        this.el.value = this.format(new Date(this.curFullYear, this.curMonth, this.curDate),this.el.retrieve('datepicker:dateformat'));

        clearTimeout(this.timer);
        this.timer = this.hide.delay(this.options.hideDelay, this);
    },

    elementBlur: function(event){
        if(!this.lock) {
            clearTimeout(this.timer);
            this.timer = this.hide.delay(this.options.hideDelay, this);
        }
    },

    position: function(target){
        this.datepicker.position({
            target:target,
            from: 'lt',
            to: 'lb',
            offset: this.options.offsets,
            scrollIntoView:true
        });
        /*var size = window.getSize(), scroll = window.getScroll();
        var datepicker = this.datepicker.getSize();
        var props = {x: 'left', y: 'top'};
        for (var z in props){
            var pos = event.page[z] + this.options.offsets[z];
            if ((pos + datepicker[z] - scroll[z]) > size[z]) pos = event.page[z] - this.options.offsets[z] - datepicker[z];
            this.datepicker.setStyle(props[z], pos);
        }*/
    },

    show: function(){
        this.fireEvent('show', this.datepicker);
    },

    hide: function(){
        this.fireEvent('hide', this.datepicker);
    },

    refresh: function() {
        // Array.from(this.container.childNodes).each(Element.dispose);
        this.table.empty();
        this.caption().inject(this.table);
        this.thead().inject(this.table);
        this.tbody().inject(this.table);
    },

    // navigate: calendar navigation
    // @param type (str) m or y for month or year
    // @param d (int) + or - for next or prev
    navigate: function(type, d) {
        switch (type) {
            case 'm': // month
            var i = this.curMonth + d;

            if (i < 0 || i == 12) {
                this.curMonth = (i < 0) ? 11 : 0;
                this.navigate('y', d);
            }
            else
                this.curMonth = i;

            break;
            case 'y': // year
            this.curFullYear += d;

            break;
        }

        this.el.store('datepicker:current', [this.curFullYear,this.curMonth,this.curDate]);

        this.el.focus(); // keep focus and do automatique rebuild ;)
    },

    // caption: returns the caption element with header and navigation
    // @returns caption (element)
    caption: function(navigation) {
        // start by assuming navigation is allowed
        navigation = navigation || {
            prev: { 'month': true, 'year': true },
            next: { 'month': true, 'year': true }
        };

        var caption = new Element('caption');

        var prev = new Element('a.prev', {html:'&lsaquo;'}); // <
        var next = new Element('a.next', {html:'&rsaquo;'}); // >

        var year = new Element('span.year').inject(caption);
        if (navigation.prev.year) {
            prev.clone().addEvent('click', function() {
                this.navigate('y', -1);
            }.bind(this)).inject(year);
        }
        new Element('span.name', {
            html: this.curFullYear + this.options.year,
            events: {
                'mousewheel': function(e){
                    e.stop();
                    this.navigate('y', (e.wheel < 0 ? -1 : 1));
                    this.refresh();
                }.bind(this)
            }
        }).inject(year);
        if (navigation.next.year) {
            next.clone().addEvent('click', function() {
                this.navigate('y', 1);
            }.bind(this)).inject(year);
        }
        var month = new Element('span.month').inject(caption);
        if (navigation.prev.month) {
            prev.clone().addEvent('click', function() {
                this.navigate('m', -1);
            }.bind(this)).inject(month);
        }
        new Element('span.name', {
            html: this.options.months[this.curMonth],
            events: {
                'mousewheel': function(e){
                    e.stop();
                    this.navigate('m', (e.wheel < 0 ? -1 : 1));
                    this.refresh();
                }.bind(this)
            }
        }).inject(month);
        if (navigation.next.month) {
            next.clone().addEvent('click', function() {
                this.navigate('m', 1);
            }.bind(this)).inject(month);
        }
        return caption;
    },

    // thead: returns the thead element with day names
    // @returns thead (element)
    thead: function() {
        var tr = ['<tr>'];
        for (i = 0; i < 7; i++) {
            tr.push('<th>' + this.options.days[(this.options.weekFirstDay + i)%7].substr(0, 3) + '</th>');
        }
        tr.push('</tr>');
        tr.join('');
        return new Element('thead', {html: tr});
    },

    // tbody: returns the tbody element with day numbers
    // @returns tbody (element)
    tbody: function() {
        var d = new Date(this.curFullYear, this.curMonth, 1);

        var offset = ((d.getDay() - this.options.weekFirstDay) + 7) % 7; // day of the week (offset)
        var last = new Date(this.curFullYear, this.curMonth + 1, 0).getDate(); // last day of this month
        var prev = new Date(this.curFullYear, this.curMonth, 0).getDate(); // last day of previous month

        var v = (this.el.value) ? this.unformat(this.el.value,this.el.retrieve('datepicker:dateformat')) : false;
        var current = new Date(v[0], v[1], v[2]).getTime();
        var d = new Date();
        var today = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); // today obv

        var tbody = new Element('tbody', {
            events: {
                'mousewheel': function(e){
                    e.stop(); // prevent the mousewheel from scrolling the page.
                    this.navigate('m', (e.wheel < 0 ? -1 : 1));
                    this.refresh();
                }.bind(this)
            }
        });
        var tr;

        for (var i = 1; i < 43; i++) { // 1 to 42 (6 x 7 or 6 weeks)
            if ((i - 1) % 7 == 0) { tr = new Element('tr'); } // each week is it's own table row

            var td = new Element('td');
            var day = i - offset;
            var date = new Date(this.curFullYear, this.curMonth, day);

            if (day < 1) { // last days of prev month
                day = prev + day;
                td.addClass('inactive');
            }
            else if (day > last) { // first days of next month
                day = day - last;
                td.addClass('inactive');
            }
            else {
                td.addEvents({
                    'click': function(day) {
                        this.curDate = day;
                        this.elementChange();
                    }.bind(this, day),
                    'mouseenter': function(td) {
                        Browser.ie6 && td.addClass('current');
                    }.bind(this, td),
                    'mouseleave': function(td, date) {
                        if(date.getTime() != current && Browser.ie6) td.removeClass('current');
                    }.bind(this, td, date)
                }).addClass('active');

                if(date.getTime() == current) { td.addClass('current'); }
                else if (date.getTime() == today) { td.addClass('today'); } // add class for today
            }

            td.set('text',day).inject(tr.inject(tbody));
        }
        return tbody;
    },
    unformat: function(val, f) {
        var _dates=val.split(f);
        var dates=[3];
        if(_dates.length<3||!_dates[0]||!_dates[1]||!_dates[2]) return [new Date().getFullYear(),new Date().getMonth(),new Date().getDate()];
        dates[0] = _dates[0].toInt();
        dates[1] =_dates[1].toInt()-1;
        dates[2] =_dates[2].toInt();
        return dates;
    },
    format: function(date, format) {
        if (date) {
            var j = date.getDate(); // 1 - 31
            var w = date.getDay(); // 0 - 6
            var l = this.options.days[w]; // Sunday - Saturday
            var n = date.getMonth() + 1; // 1 - 12
            var f = this.options.months[n - 1]; // January - December
            var y = date.getFullYear() + ''; // 19xx - 20xx

            return [y,n,j].join(format);
        }else{
            return '';
        }
    }

});

Element.implement({
    makeCalable:function(options){
        if(this.retrieve('bindDatePicker')) return;
        return new DatePickers(this, options);
    }
});
