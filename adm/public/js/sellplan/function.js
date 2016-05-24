var colset = $('#con_act').attr('con')+$('#con_act').attr('act');
$(function(){
	
	var navFlag = true;
	//获取导航显示信号
	if(localStorage.getItem('menu') == 'false'){
		$('#main').css({'margin-left':0});
		$('#navbar-module').siblings().hide();
		navFlag = false;
		if($('#wrap').children('.falldiv').length>0){
			waterfall();
		}
	}
	//获取列设置信号
	if(sessionStorage.getItem(colset)){
		var setCol = sessionStorage.getItem(colset).split(',');
		for(var i=0;i<setCol.length;i++){
			if(setCol[i]=='no'){
				$('.set-window').children('span').eq(i).children('input').prop('checked',false);
				for(var j=0;j<$('tr').length;j++){
					if($('tr').eq(j).children('td').length>1 && $('tr').eq(j).parent().parent().is($('#table'))){
						$('tr').eq(j).children('td').eq(i+1).hide();
					}
				}
			}
		}
	}
	//logo点击 
	$('#navbar-module').click(function(){
		if($(window).width()>992){
			if(navFlag){
				$(this).siblings().hide();
				$('#main').css({'margin-left':0});
				navFlag = false;
				if($('#wrap').children('.falldiv').length>0){
					waterfall();
				}
			}else {
				$(this).siblings().show();
				$('#main').attr('style','');
				navFlag = true;
				if($('#wrap').children('.falldiv').length>0){
					waterfall();
				}
			}
		}
		//表头宽度
		var widthArr = [];
		for(var k=0;k<$theadTd.length;k++){
			widthArr[k] = $tbodyTd.eq(k+TableScrollIndex).innerWidth();
		}
		for(var i=0;i<$theadTd.length;i++){
			$theadTd.eq(i).css('width',widthArr[i]+'px');
			$tbodyTd.eq(i+TableScrollIndex).css('width',widthArr[i]+'px');
		}
		localStorage.setItem('menu',navFlag);
		
	})
	
	//返回顶部hover
	$('#top').hover(function(){
		$(this).children('p').html('返回顶部');
		$(this).children('img').hide();
	},function(){
		$(this).children('p').html('');
		$(this).children('img').show();
	})
	//返回顶部出现
	$(document).scroll(function(){
		if($(this).scrollTop()>500){
			$('#top').show();
			$('#top').click(function(){
				$(document).scrollTop(0);
			})
		}else {
			$('#top').hide();
		}
	})
	
})

//表头固定
if($('.scrollTable').length>0 && !$('.scrollTable').hasClass('noScrollTable')){
	var scrollFlag = true;
	var tableTop = $('.scrollTable').offset().top - 60;
	var $tbodyTr = $('.scrollTable').children('tbody').children('tr');
	var TableScrollIndex = 0;
	for(var j=0;j<$tbodyTr.length;j++){
		if($tbodyTr.eq(j).children('td').length>1){
			break;
		}else {
			TableScrollIndex++;
		}
	}
	var $theadTd = $('.scrollTable').children('thead').find('td');
	var $tbodyTd = $tbodyTr.children('td');
	$(document).scroll(function(){
		//console.log($(document).scrollTop()+':'+tableTop);
		//console.log(widthArr)
		if($(document).scrollTop()>tableTop){
			if(scrollFlag){
				var widthArr = [];
				for(var k=0;k<$theadTd.length;k++){
					widthArr[k] = $tbodyTd.eq(k+TableScrollIndex).innerWidth();
				}
				$('.scrollTable').addClass('fixed-table');
				for(var i=0;i<$theadTd.length;i++){
					$theadTd.eq(i).css('width',widthArr[i]+'px');
					$tbodyTd.eq(i+TableScrollIndex).css('width',widthArr[i]+'px');
				}
				scrollFlag = false;
			}
		}else{
			$('.scrollTable').removeClass('fixed-table');
			scrollFlag = true;
		}
	})
	
}

//列设置
if($('#setWindow').length>0){
	$('#setWindow').click(function(e){
		$(this).next().slideToggle(300);
		e.stopPropagation();
	})
	
	var $th = $('#table tr').eq(0).children('td');
	
	//设置sessionStorage
	var colSet = [];
	for(var i=1; i<$th.length; i++){
		var $span = $('<span><input type="checkbox" checked="checked" />'+$th.eq(i).html()+'</span>');
		$('.set-window').append($span);
		var str = $th.eq(i).html()+'';
		colSet[i-1] = 'yes';
	}
		//如果不存在 设置一个
		if(!sessionStorage.getItem(colset)){
			sessionStorage.setItem(colset,colSet);
		}
	
	var session = sessionStorage.getItem(colset).split(',');
	var maxNum = $th.length-5;
	$('.set-window').children('span').click(function(e){
		e.stopPropagation();
		var num = 0;
		for(var i=0; i<$th.length; i++){
			if($('.set-window').find('input').eq(i).is(':checked')){
				
			}else {
				num ++;
			}
		}
		
		var index = $(this).index();
		
		if($(this).children('input').is(':checked')){
			if(num <= maxNum){			
				$(this).children('input').prop('checked',false);
				for(var i=0;i<$('tr').length;i++){
					if( $('tr').eq(i).children('td').length>1 && $('tr').eq(i).parent().parent().is($('#table'))){
						$('tr').eq(i).children('td').eq(index+1).hide();
					}
				}
				session[index] = 'no';		
			}
			
		}else {
			for(var i=0;i<$('tr').length;i++){
				if($('tr').eq(i).children('td').length>1 && $('tr').eq(i).parent().parent().is($('#table'))){
					$('tr').eq(i).children('td').eq(index+1).show();
				}
			}
			
			$(this).children('input').prop('checked',true);
			session[index] = 'yes';	
		}
		
		sessionStorage.setItem(colset,session);
	})
	
	$('.set-window').find('input').click(function(e){
		var num = 0;
		for(var i=0; i<$th.length; i++){
			if($('.set-window').find('input').eq(i).is(':checked')){
				
			}else {
				num ++;
			}
		}
		e.stopPropagation();
		var index = $(this).parent().index();
		if($(this).is(':checked')){
			for(var i=0;i<$('tr').length;i++){
				if($('tr').eq(i).children('td').length>1 && $('tr').eq(i).parent().parent().is($('#table'))){
					$('tr').eq(i).children('td').eq(index+1).show();
				}
			}
			session[index] = 'yes';	
		}else {
			if(num <= (maxNum+1)){			
				for(var i=0;i<$('tr').length;i++){
					if($('tr').eq(i).children('td').length>1 && $('tr').eq(i).parent().parent().is($('#table'))){
						$('tr').eq(i).children('td').eq(index+1).hide();
					}
				}
				session[index] = 'no';
			}else {
				$(this).prop('checked',true);
			}
		}
		sessionStorage.setItem(colset,session);
	})
	
	
}

//个人菜单下拉
$('.usercenter').parent().click(function(e) {
	e.stopPropagation();
	$('.usercenter').slideToggle(100);
})
$(document).click(function(){
	$('.usercenter').slideUp(100);
	$('#pageCountSelect').hide();
	if($('#highSearch').length>0){
		$('#highSearch').removeClass('active').children('.high-search').hide();
	}
	if($('#setWindow').length>0){
		$('#setWindow').next().slideUp(300);
	}
	if($('#batch').length>0){
		$('#batch').next().hide();
	}
	if($('.btn-search').children('.batch-menu').length>0){
		$('.btn-search').children('.batch-menu').hide();
	}
})

//导航按钮
var $navbtn = $('.navbtn');
$navbtn.click(function() {
	var $nav = $('.dev-sidebar');
	if($nav.css('display')=='none'){
		$nav.slideDown(300);
	}else {
		$nav.slideUp(300,function(){
			$nav.attr('style','');
		});
	}
})


//分页
if($('#Pagination').length>0){
	//每页数据个数
	var perpage = $('#perpage').text();
	
	$('#perpage').parent().click(function(e){
		e.stopPropagation();
		$('#pageCountSelect').toggle();
	})

	//修改每页个数
	$('#pageCountSelect li:gt(0)').click(function(){
		var perpage = $(this).text();
		$('#perpage').text(perpage);
		var href = $('#Pagination').find('span:eq(0)').attr('href');
		var url = href.replace(/\b(perpage=(?=\d+))\d+\b/,"$1"+perpage);
		url = url.replace(/\bpage=\d+\b/,"page=1");
        location.href= url;
	})
}

//获取地址栏参数
function GetQueryString(name){
	var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	var r = window.location.search.substr(1).match(reg);
	if(r!=null)return  decodeURI(r[2]); return null;
}

//订单页展示详情
if($('#orderDetail').length>0){
	var $orderDetail = $('#orderDetail');
	$orderDetail.remove();
}

function showOrderInfo(obj,txt){
	if(obj.text()==txt){
//		$('#table tbody').append($orderDetail);
//		if($(window).width()>992){
//			//换货点击
//			$('#orderDetail td').find('#change').click(function(){
//				$("#addNewWindow").css({"display":"block"});
//				$("#addNewWindow").find("#table tr input").prop({"checked":false});
//			})
//		}
		$orderDetail.show();
		
		//关闭其他
		$btn.text(txt);
		$btn.attr('style','');
		//打开当前
		obj.text('-');
		obj.css('background','#F2787A');
		obj.parent().parent().parent().addClass('active').after($orderDetail).siblings().removeClass('active');
		//详情tab	
		if($('#orderDetail .order-details').find('.btn').length>0){
			var $infoBtn = $('#orderDetail .order-details').find('.btn');
			$infoBtn.click(function(){
				$(this).addClass('active').siblings().removeClass('active');
				$('#orderDetail').find('.order-info .nomargin').eq($(this).index()).show().siblings().hide();
			})
		}
		
		$orderDetail.addClass('active');
		
	}else{
		$orderDetail.remove();
		obj.text(txt);
		obj.attr('style','');
		obj.parent().parent().parent().removeClass('active');
	}
}

//滚动条滑动出现
function showScrollBar(jq){
	var topValue = 0, // 上次滚动条到顶部的距离
		interval = null; // 定时器
	var flag = true;
	$(jq).scroll(function() {
		$(jq).removeClass('scrollbar');
		flag = false;
		if (interval == null){
			interval = setInterval(function(){
				if ($(jq).scrollTop() == topValue) {
					$(jq).addClass('scrollbar');
					flag = true;
					clearInterval(interval);
					interval = null;
				}
			}, 1000);
		}
			
		topValue = $(jq).scrollTop();
	})
	
	$(jq).mousemove(function(e){
		if(flag){
			if(e.offsetX>($(this).width()-8)){
				$(jq).removeClass('scrollbar');
			}else{
				$(jq).addClass('scrollbar');
			}
		}
		
	})
}
//删除图片函数
function delImg(a){
	$(a).parent().remove()
}

//图片放大
function showImg(obj){
	//图片排序页面
	if($('#showBox').length>0){
    	$('#showImg').css({'display':'block','line-height':$('#showImg').height()+'px'});
		$('#showImg').find('img').attr('src',$(obj).attr('src').replace(/_thumb_(\d+)x\1./,'_image.'));
		var index = $(obj).parent().index();
		var $tr = $(obj).parent().parent().children('li');
		//图片排序页面的上一张下一张
		$('#prev').click(function(e){
			index--;
			if(index < 0){
				index = 0;
			}
			e.stopPropagation();
			var src = $tr.eq(index).find('img').attr('src').replace(/_thumb_(\d+)x\1./,'_image.');
			$('#showImg').find('img').attr('src',src);
		})
		$('#next').click(function(e){
			index++;
			if(index > ($tr.length-1)){
				index = $tr.length - 1;
			}
			e.stopPropagation();
			var src = $tr.eq(index).find('img').attr('src').replace(/_thumb_(\d+)x\1./,'_image.');
			$('#showImg').find('img').attr('src',src);
		})
	}else {
		//表格页面
		$('#showImg').css({'display':'block','line-height':$('#showImg').height()+'px'});
		$('#showImg').find('img').attr('src',$(obj).attr('src').replace(/_thumb_(\d+)x\1./,'_image.'));
		var flag = false;
		if($(obj).parent().is('td')){
			var $tr = $(obj).parent().parent().parent().children('tr');
			var index = $(obj).parent().parent().index();
			flag = true;
		}
		//上一张下一张
		if(flag){
			$('#prev').click(function(e){
				index--;
				if(index < 0){
					index = 0;
				}
				e.stopPropagation();
				if($tr.eq(index).children('td').length>2 ){
					var src = $tr.eq(index).find('img').attr('src').replace(/_thumb_(\d+)x\1./,'_image.');
					$('#showImg').find('img').attr('src',src);
				}else if($tr.eq(index).children('td').length<2){
					index--;
					var src = $tr.eq(index).find('img').attr('src').replace(/_thumb_(\d+)x\1./,'_image.');
					$('#showImg').find('img').attr('src',src);
				}
			})
			$('#next').click(function(e){
				index++;
				if(index > ($tr.length-1)){
					index = $tr.length - 1;
				}
				e.stopPropagation();
				if($tr.eq(index).children('td').length>2 ){
					var src = $tr.eq(index).find('img').attr('src').replace(/_thumb_(\d+)x\1./,'_image.');
					$('#showImg').find('img').attr('src',src);
				}else if($tr.eq(index).children('td').length<2){
					index++;
					var src = $tr.eq(index).find('img').attr('src').replace(/_thumb_(\d+)x\1./,'_image.');
					$('#showImg').find('img').attr('src',src);
				}
				
			})
		}
    }
	
}
//关闭
$('#showImg').click(function(){
	$(this).css('display','none');
	$('#showImg').find('img').attr('src','');
})

//js的siblings
function JSsiblings(elm) {
	var a = [];
	var p = elm.parentNode.children;
	for(var i =0,pl= p.length;i<pl;i++) {
		if(p[i] !== elm) a.push(p[i]);
	}
	return a;
}


//字符串，确定函数（必须），取消函数（非必需）
function $_confirm(str, fn, fn2) {
	var $style = $('<style>.confirm_modal{position:fixed;top:0;left:0;width:100%;height:100%;z-index:1050;overflow:hidden;-webkit-overflow-scrolling:touch;outline:0;background:rgba(0,0,0,0.7)}.confirm_modal-dialog{width:400px;max-width:90%;margin:220px auto 0;background:#FFF}.confirm_modal-content{border:1px solid rgba(0,0,0,.2);border-radius:6px;-webkit-box-shadow:0 3px 9px rgba(0,0,0,.5);box-shadow:0 3px 9px rgba(0,0,0,.5)}.confirm_modal-header{text-align:center;padding:15px}.confirm_modal-title{margin:0;font-size:18px;font-weight:300}.confirm_modal-footer{padding:15px;text-align:center;border-top:1px solid #e5e5e5}.confirm_btn{display:inline-block;padding:6px 12px;margin-bottom:0;font-size:14px;line-height:1.42857143;text-align:center;white-space:nowrap;cursor:pointer;border:1px solid transparent;border-radius:4px;margin-left:5px}.confirm_btn-primary{color:#fff;background-color:#337ab7;border-color:#2e6da4}.confirm_btn-default{color:#333;background-color:#fff;border-color:#ccc}</style>');
	var $confirm = $('<div class="confirm_modal"><div class="confirm_modal-dialog"><div class="confirm_modal-content"><div class="confirm_modal-header"><h4 class="confirm_modal-title">' + str + '</h4></div><div class="confirm_modal-footer"><span type="button" class="confirm_btn confirm_btn-default">取消</span><button type="button" class="confirm_btn confirm_btn-primary">确定</button></div></div></div></div>');
	$('body').append($confirm);
	$('head').append($style);

	$confirm.click(function(e) {
		var $target = $(e.target);
		if ($target.hasClass('confirm_btn-primary')) {
			if(fn){
				fn();
			}
			$confirm.remove();
			$style.remove();
		} else if ($target.hasClass('confirm_btn-default')) {
			if (fn2) {
				fn2();
			}
			$confirm.remove();
			$style.remove();
		}
	})
}











