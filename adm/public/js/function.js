$(document).on("change", '#province',function() {
	var province = $(this).val();
    var city = $('#city');
    var area = $('#area');
    city.html('<option value="">请选择城市</option>');
    area.html('<option value="">请选择地区</option>');
    $.post('/shop/city',{'province':province},function(result) {
    	var html = '';
        if(result.success == 1) {
           	html += '<option value="">请选择城市</option>';
          	for(var i=0;i<result.data.length;i++){
          	    html += '<option value="'+result.data[i].id+'">'+result.data[i].name+'</option>';
          	}
        } else {
           html += '<option value="">请选择城市</option>';
        }
    	city.html(html);
    },'json')
});
$(document).on("change", '#city',function() {
	var city = $(this).val();
    var area = $('#area');
    area.html('<option value="">请选择地区</option>');
    $.post('/shop/area',{'city':city},function(result) {
    	var html = '';
        if(result.success == 1) {
           	html += '<option value="">请选择地区</option>';
          	for(var i=0;i<result.data.length;i++){
          	    html += '<option value="'+result.data[i].id+'">'+result.data[i].name+'</option>';
          	}
        } else {
           html += '<option value="">请选择地区</option>';
        }
    	area.html(html);
    },'json')
});
$(document).on("change", '#category1',function() {
	var pid = $(this).val();
	var category2 = $('#category2');
	var category3 = $('#category3');
	category2.html('<option value="">请选择二级类目</option>');
	category3.html('<option value="">请选择三级类目</option>');
	$.post('/goods/category',{'pid':pid},function(result) {
		var html = '';
		if(result.success == 1) {
			html += '<option value="">请选择二级类目</option>';
			for(var i=0;i<result.data.length;i++){
				html += '<option value="'+result.data[i].id+'">'+result.data[i].name+'</option>';
			}
		} else {
			html += '<option value="">请选择二级类目</option>';
		}
		category2.html(html);
	},'json')
});
$(document).on("change", '#category2',function() {
	var pid = $(this).val();
	var category3 = $('#category3');
	category3.html('<option value="">请选择三级类目</option>');
	$.post('/goods/category',{'pid':pid},function(result) {
		var html = '';
		if(result.success == 1) {
			html += '<option value="">请选择三级类目</option>';
			for(var i=0;i<result.data.length;i++){
				html += '<option value="'+result.data[i].id+'">'+result.data[i].name+'</option>';
			}
		} else {
			html += '<option value="">请选择三级类目</option>';
		}
		category3.html(html);
	},'json')
});
$(document).on("change", '#category3',function() {
	var $this = $(this);
	var pid = $this.val();
	$.post('/goods/getPara',{'pid':pid},function(result) {
		var html = '';
		if(result.success == 1) {
			for(var i=0;i<result.data.length;i++){
				html += '<div class="btn-block">';
                html += '<div class="btn-group">';
                html += '参数名 ：&nbsp;<select name="key[]" class="form-control" style="display:inline;width:160px">';
                html += '<option value="'+result.data[i].name+'-'+result.data[i].id+'">'+result.data[i].name+'</option>';
                html += '</select></div>';                
                html += '<div class="btn-group">';
                html += '&nbsp;参数值 ：&nbsp;<select name="value[]" class="form-control" style="display:inline;width:160px">';
                html += '<option value="">请选择参数值</option>';
                for( var j = 0; j < result.data[i].child.length; j++) {
                	html += '<option value="'+result.data[i].child[j].name+'-'+result.data[i].child[j].id+'">'+result.data[i].child[j].name+'</option>';
                }
                html += '</select></div></div>';
			}
           
            $('.addPar').parent().parent().before(html);
		} else {
			alert('该类目下没有参数，请重新选择');
		}
	},'json')
});

