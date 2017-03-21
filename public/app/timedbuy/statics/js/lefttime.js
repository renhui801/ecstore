/* $Id : lefttime.js 4865 2010-10-12 15:33 chenping $ */

/* *
 * 给定一个剩余时间（s）动态显示一个剩余时间.
 * 当大于一天时。只显示还剩几天。小于一天时显示剩余多少小时，多少分钟，多少秒。秒数每秒减1 *
 */

var LeftTime = new Class({
	Implements:[Options],
    options: {
		 auctionDate:0,
		 _GMTEndTime:0,
		 showTime:"leftTime",
		 _day:'天',
		 _hour:'小时',
		 _minute:'分',
		 _second:'秒',
		 _end:'限时抢购结束',
		_pre:'离限时抢购还有：',
		stag:"<font>",
		etag:"</font>",
		end_word:'',
		forenotice:false,
		 //cur_date:new Date(),
		 //startTime = cur_date.getTime();
		 //Temp;
		 timerID:null,
		 timerRunning:true,
		_reload:false
    },
    initialize: function(el, options){
        this.el = $(el);
        this.setOptions(options);
			/*
		  if (_GMTEndTime > 0)
		  {
			if (now_time == undefined)
			{
			  var tmp_val = parseInt(_GMTEndTime) - parseInt(cur_date.getTime() / 1000 + cur_date.getTimezoneOffset() * 60);
			}
			else
			{
			  var tmp_val = parseInt(_GMTEndTime) - now_time;
			}
			if (tmp_val > 0)
			{
			  auctionDate = tmp_val;
			}
		  }*/
		this.macauclock();
        
    },
	showtime:function(){
		  now = new Date();
		  var ts = this.options.auctionDate.toInt() - (now.getTime()/1000).toInt();
		  var dateLeft = 0;
		  var hourLeft = 0;
		  var minuteLeft = 0;
		  var secondLeft = 0;
		  var hourZero = '';
		  var minuteZero = '';
		  var secondZero = '';
		  var Temp;
		  if (ts < 0)
		  {
			ts = 0;
			var CurHour = 0;
			var CurMinute = 0;
			var CurSecond = 0;
		  }
		  else
		  {
			dateLeft = parseInt(ts / 86400);
			ts = ts - dateLeft * 86400;
			hourLeft = parseInt(ts / 3600);
			ts = ts - hourLeft * 3600;
			minuteLeft = parseInt(ts / 60);
			secondLeft = ts - minuteLeft * 60;
		  }

		  if (hourLeft < 10)
		  {
			hourZero = '0';
		  }
		  if (minuteLeft < 10)
		  {
			minuteZero = '0';
		  }
		  if (secondLeft < 10)
		  {
			secondZero = '0';
		  }

		  if (dateLeft > 0)
		  {
			Temp = this.options.stag+dateLeft+this.options.etag + this.options._day +this.options.stag+ hourZero + hourLeft+this.options.etag + this.options._hour + this.options.stag+minuteZero + minuteLeft +this.options.etag+ this.options._minute + this.options.stag+secondZero + secondLeft+this.options.etag + this.options._second;
		  }
		  else
		  {
			if (hourLeft > 0)
			{
			  Temp = this.options.stag+hourLeft+this.options.etag + this.options._hour + this.options.stag+minuteZero + minuteLeft+this.options.etag + this.options._minute + this.options.stag+secondZero + secondLeft+this.options.etag + this.options._second;
			}
			else
			{
			  if (minuteLeft > 0)
			  {
				Temp = this.options.stag+minuteLeft+this.options.etag + this.options._minute + this.options.stag+secondZero + secondLeft+this.options.etag + this.options._second;
			  }
			  else
			  {
				if (secondLeft > 0)
				{
				  Temp = this.options.stag+secondLeft+this.options.etag + this.options._second;
				}
				else
				{
				  Temp = '';
				}
			  }
			}
		  }

		  if ((this.options.auctionDate <= 0 || Temp == '') && this.options.timerRunning)
		  {
			Temp = "<strong>" + this.options._end + "</strong>";
			this.stopclock();
		  }else{
			Temp = this.options._pre+Temp+'	'+this.options.end_word;
		  }

		  this.el.set('html',Temp);
		  if(this.options.timerRunning){
			this.options.timerID = setTimeout(arguments.callee.bind(this), 1000);
		  }
		  this.options.timerRunning = true;
		  
	},
	stopclock:function(){
		if(this.options.timerID){
			$clear(this.options.timerID);
		}
		this.options.timerRunning = false;
		if(this.options._reload){
			//window.location.reload();
		}
	},
	macauclock:function(){
		/*this.stopclock();*/
		this.showtime();
	}

});

