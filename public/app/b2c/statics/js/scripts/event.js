;(function (win, $) {

	//原生对象
	var Obj = Object,
		Arr = Array;

	//原生对象方法
	var toString = Obj.prototype.toString,
		hasOwnProperty = Obj.prototype.hasOwnProperty,
		slice = Arr.prototype.slice,
		splice = Arr.prototype.splice;

	/*
	 * 事件管理类
	 * @params name {string} 事件名(选择符.事件名)	如果是class选择器就不能写.
	 * @params callback {function} 事件处理函数
	 * @returns nothings
	*/
	function Event (name, callback) {
		if (!(this instanceof Event)) return new Event(name, callback);
		this.events = {}; //事件存储器
		this.on(name, callback);
	}

	Event.prototype = {
		
		//事件注册
		on: function (name, callback) {

			var	collection = name.split('.'), //获得选择符和事件集合
				selector = (/[#.]/i.test(collection[0])) ? collection[0] : ('.' + collection[0]),  //选择符
				eventName = collection[1] || 'click'; //事件名

			if (!selector) throw new Error('请填写一个标准的选择器');

			if (!(selector in this.events)) this.events[selector] = {};

			(!(eventName in this.events[selector])) && (this.events[selector][eventName] = []);

			this.events[selector][eventName].push(callback);
		
		},

		//只对相同的事件名注册一次事件处理
		once: function (name, callback) {
			var arr = null;
			this.remove(name);
			this.on(name, callback);
		},

		//事件删除
		remove: function (name) {
			var collection = name.split('.'),
				selector = collection[0],
				eventName = collection[1];

			if (selector && typeof selector === 'string') {
				if (eventName && (eventName in this.events[selector])) {
					delete this.events[selector][eventName];
				}
				else {
					delete this.events[selector];
				}
			}
			else {
				throw new Error('请输入一个有效的事件名');
			}			
		},

		//事件触发
		emit: function (name, e, data) {
			var collection = name.split('.'),
				selector = (/[#.]/i.test(collection[0])) ? collection[0] : ('.' + collection[0]),
				eventName = collection[1],
				callbacks = null;

			if (!this.events[selector]) throw new Error('事件不存在');
			callbacks = this.events[selector][eventName] || this.events[selector];

			callbacks.forEach(function (callback) {
				callback.call(null, e, data);
			});
		},

		//绑定元素
		bind: function (name, data) {
			var collection = name.split('.'),
				selector = (/[#.]/i.test(collection[0])) ? collection[0] : ('.' + collection[0]),
				eventName = collection[1],
				self = this;
			
			$(selector).bind(eventName, function (e) {
				self.emit(name, e, data);
			})
			return this;
		}
	}

	win.MyEvent = Event;

})(window, $);