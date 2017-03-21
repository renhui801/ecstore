if ($('b2c-dis-point') && $('b2c-order-total-point') && $('b2c-order-total-overpoint')){
	var _max_point = $('b2c-order-total-point').value;
	var _point_dis_use = $('b2c-dis-point').value;
	var _over_point = $('b2c-order-total-overpoint').value;
	var _real_usage_point = $('b2c-order-real-dis-total-point').value;
	var _cur_total_usage_point = $('b2c-order-total-realpoint').value;
	var _cur_total_usage_money = $('b2c-order-total-cur-realmoney').value;
	var _hash_data = {};
	var _data = [];
	
	if (parseInt(_point_dis_use) > parseInt(_max_point)){
		MessageBox.error('<{t}>您兑换的积分数目过多！<{/t}>');
		if (parseInt(_max_point) > 0){
			if (parseInt(_real_usage_point) > _max_point)
				$('b2c-dis-point').value = _max_point;
			else {
				$('b2c-dis-point').value = _max_point = _real_usage_point;
			}
		}else{
			$('b2c-dis-point').value = _max_point = 0;
		}
		$('pointprofessional-order-checkout-cost-point').innerHTML = _max_point;
		_data = [$('pointprofessional-order-checkout-total-exchange').getElement('input[type="hidden"]').value.toFloat(),_max_point];
		_data.each(function(item,index){
			_hash_data[index]=item;
		});
		_data = JSON.encode(_hash_data);
		new Request({
			url:'<{link app=b2c ctl="site_tools" act="count_digist"}>',
			method:'post',
			data:"data="+_data+'&_method=number_multiple',
			onComplete:function(res){
				$('pointprofessional-order-checkout-dis-amount').innerHTML = res;
			}
		}).send();
	}
	if (_over_point == 'true'){
		var _has_error = false;
		if (_real_usage_point.toInt() < _max_point.toInt()){
			_max_point = _real_usage_point;
			if (_cur_total_usage_point.toInt() < _max_point){
				_max_point = _cur_total_usage_point;
			}
			MessageBox.error('<{t}>您兑换的积分数目过多！<{/t}>');
			_has_error = true;
		}
		else {
			MessageBox.error('<{t}>您的积分超过您所拥有的最大积分！<{/t}>');
			_has_error = true;
		}
		
		if (_max_point > 0){
			$('b2c-dis-point').value = _max_point;
		}else{
			$('b2c-dis-point').value = _max_point = 0;
		}
		if ($('pointprofessional-order-checkout-cost-point')&&$('pointprofessional-order-checkout-dis-amount')&&$('pointprofessional-order-checkout-total-exchange')){
			$('pointprofessional-order-checkout-cost-point').innerHTML = _max_point;
			_data = [$('pointprofessional-order-checkout-total-exchange').getElement('input[type="hidden"]').value.toFloat(),_max_point];
			_data.each(function(item,index){
				_hash_data[index]=item;
				//_hash_data.set(index,item);
			});
			_data = JSON.encode(_hash_data);
			new Request({
				url:'<{link app=b2c ctl="site_tools" act="count_digist"}>',
				method:'post',
				data:"data="+_data+'&_method=number_multiple',
				onComplete:function(res){
					$('pointprofessional-order-checkout-dis-amount').innerHTML = res;
				}
			}).send();			
		}
		
		if (_has_error)
			return false;
	}
}
