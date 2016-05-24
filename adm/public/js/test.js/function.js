var colset = $('#con_act').attr('con')+$('#con_act').attr('act');
if($('#con_act_status')){
    colset += $('#con_act_status').attr('data');
}
$(function(){
	
	var navFlag = true;
	//获取导航显示信号
	if(localStorage.getItem('menu') == 'false' && $(window).width()>992){
		$('#main').css({'margin-left':'50px'});
		$('#navbar-module').siblings().addClass('mini-sidebar');
		$('.nav').children('li').unbind('click').children('ul').attr('style','');
		navFlag = false;
		if($('#wrap').children('.falldiv').length>0){
			waterfall();
		}
	}else {
		$('.nav').children('li').click(function(){
	        $(this).children('ul').slideToggle(200);
	        $(this).siblings().children('ul').slideUp(200);
	        //$(this).addClass('active').siblings().removeClass('active');
	    })
	}
	//获取列设置信号
	if(sessionStorage.getItem(colset) && ($('#setWindow').length>0)){
		var setCol = sessionStorage.getItem(colset).split(',');
		for(var i=0;i<setCol.length;i++){
			if(setCol[i]=='no'){
				$('.set-window').children('span').eq(i).children('input').prop('checked',false);
				for(var j=0;j<$('tr').length;j++){
					if($('tr').eq(j).children('td').length>1 && $('tr').eq(j).parent().parent().is($('#table'))){
						$('tr').eq(j).children('td').eq(i+1).hide();
					}
				}
			}else if(setCol[i]=='yes'){
				$('.set-window').children('span').eq(i).children('input').prop('checked',true);
				for(var j=0;j<$('tr').length;j++){
					if($('tr').eq(j).children('td').length>1 && $('tr').eq(j).parent().parent().is($('#table'))){
						$('tr').eq(j).children('td').eq(i+1).show();
					}
				}
			}
		}
	}

	//logo点击 
	$('#navbar-module').click(function(){
		if($(window).width()>992){
			if(navFlag){
				$(this).siblings().addClass('mini-sidebar');
				$('#main').css({'margin-left':'50px'});
				$('.nav').children('li').unbind('click').children('ul').attr('style','');
				navFlag = false;
				if($('#wrap').children('.falldiv').length>0){
					waterfall();
				}
			}else {
				$(this).siblings().removeClass('mini-sidebar');
				$('#main').attr('style','');
				$('.nav').children('li').click(function(){
			        $(this).children('ul').slideToggle(200);
			        $(this).siblings().children('ul').slideUp(200);
			        //$(this).addClass('active').siblings().removeClass('active');
			    })
				navFlag = true;
				if($('#wrap').children('.falldiv').length>0){
					waterfall();
				}
			}
		}
		if($('.scrollTable').length>0){
			//表头宽度
			var $thead = $('.scrollTable').children('thead');
			var $theadTd = $thead.find('td');
			var $clone = $('.fixedThead');
			$clone.css('width',$thead.innerWidth()+'px');
			for(var k=0;k<$theadTd.length;k++){
				if($theadTd.eq(k).is(':hidden')){
					$clone.find('td').eq(k).css('display','none');
				}else{
					$clone.find('td').eq(k).css('width',$theadTd.eq(k).innerWidth()+'px');
				}
			}
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
		var $top = $('#top');
		if($(this).scrollTop()>500){
			$top.show();
			$top.click(function(){
				$(document).scrollTop(0);
			})
		}else {
			$top.hide();
		}
	})
	
	//表格操作菜单
	var $tdMenu = $('td .btn-search').children('.btn');
	$tdMenu.click(function(e){
		e.stopPropagation();
		if($(this).parent().children('ul').length>0){
			$tdMenu.not(this).parent().children('ul').hide();
			$(this).next().toggle();
			var $thisHeight = $(this).next().height() + $(this).next().offset().top;
			var $parentHeight = $('.tablescroll').height() + $('.tablescroll').offset().top;
			if($thisHeight > $parentHeight - 20){
				$(this).next().css({'top':'auto','bottom':'100%','margin-bottom':'2px'});
			}
		}
	})
	
})

//表头固定
function scrollTable(){
	var $scrollTable = $('.scrollTable');
	if($scrollTable.length>0){
		var scrollFlag = true;
		var tableTop = $scrollTable.offset().top - 60;
		var $thead = $scrollTable.children('thead');
		var $theadTd = $thead.find('td');
		var $clone = $thead.clone();
		
//		console.log($thead.innerWidth());
		var width = $thead.innerWidth();
		
		$(document).scroll(function(){
			if($(document).scrollTop()>tableTop){
				if(scrollFlag){
					$scrollTable.parent().append($clone);
					if($('#checkBox').prop('checked')){
						$clone.find('input[type=checkbox]').prop('checked',true);
					}
					$clone.find('input[type=checkbox]').click(function(){
						if(!checkAll){
							$("#table").find("tr input").prop({"checked":true});
							checkAll = true;
						}else if(checkAll) {
							checkAll = false;
							$("#table").find("tr input").prop({"checked":false});
						}
					})
//					console.log($thead.innerWidth());
					$clone.css('width',$thead.innerWidth()+'px');
					for(var k=0;k<$theadTd.length;k++){
						if($theadTd.eq(k).is(':hidden')){
							$clone.find('td').eq(k).hide();
						}else{
							$clone.find('td').eq(k).show();
							$clone.find('td').eq(k).css('width',$theadTd.eq(k).innerWidth()+'px');
						}
					}
					scrollFlag = false;
				}
				$clone.addClass('fixedThead');
				var theadTop =($(document).scrollTop()-tableTop)+'px';
				$clone.css({'top':theadTop});
			}else {
				$clone.remove();
				scrollFlag = true;
			}
		})
	}	
}
scrollTable();

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
		if($th.eq(i).hasClass('none')){
			var $span = $('<span><input type="checkbox" />'+$th.eq(i).html()+'</span>');
			colSet[i-1] = 'no';
		}else {
			var $span = $('<span><input type="checkbox" checked="checked" />'+$th.eq(i).html()+'</span>');
			colSet[i-1] = 'yes';
		}
		$('.set-window').append($span);
		var str = $th.eq(i).html()+'';
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
$("body").children().click(function () {
    //这里不要写任何代码,解决苹果手机冒泡
});



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

function showOrderInfo(obj){
	if(obj.attr('toggle')=='yes'){
		$orderDetail.show();
		
		//关闭其他
		$btn.attr('toggle','yes');
		//打开当前
		obj.attr('toggle','no');
		obj.parents('tr').addClass('active').after($orderDetail).siblings().removeClass('active');
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
		obj.attr('toggle','yes');
		obj.parents('tr').removeClass('active');
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

//td的复选框
$('tbody tr').find('td:first').click(function(){
	var $thisInp = $(this).children('input')
	if($thisInp.length>0){
		if($thisInp.is(':checked')){
			$thisInp.prop('checked',false)
		}else {
			$thisInp.prop('checked',true)
		}
	}
})
$('tbody tr').find('td:first').children('input').click(function(e){
	e.stopPropagation()
})

//字符串，确定函数（必须），取消函数（非必需）
function $_confirm(str, fn, fn2) {
	var $style = $('<style>.confirm_modal{position:fixed;top:0;left:0;width:100%;height:100%;z-index:1050;overflow:hidden;-webkit-overflow-scrolling:touch;outline:0;background:rgba(0,0,0,0.7)}.confirm_modal-dialog{width:400px;max-width:90%;margin:220px auto 0;background:#FFF}.confirm_modal-content{border:1px solid rgba(0,0,0,.2);border-radius:6px;-webkit-box-shadow:0 3px 9px rgba(0,0,0,.5);box-shadow:0 3px 9px rgba(0,0,0,.5)}.confirm_modal-header{text-align:center;padding:15px;max-height:400px; overflow:auto}.confirm_modal-title{margin:0;font-size:18px;font-weight:300}.confirm_modal-footer{padding:15px;text-align:center;border-top:1px solid #e5e5e5}.confirm_btn{display:inline-block;padding:6px 12px;margin-bottom:0;font-size:14px;line-height:1.42857143;text-align:center;white-space:nowrap;cursor:pointer;border:1px solid transparent;border-radius:4px;margin-left:5px}.confirm_btn-primary{color:#fff;background-color:#337ab7;border-color:#2e6da4}.confirm_btn-default{color:#333;background-color:#fff;border-color:#ccc}</style>');
	var $confirm = $('<div class="confirm_modal">\
						<div class="confirm_modal-dialog">\
							<div class="confirm_modal-content">\
								<div class="confirm_modal-header">\
									<h4 class="confirm_modal-title">' + str + '</h4>\
								</div>\
								<div class="confirm_modal-footer">\
									<span type="button" class="confirm_btn confirm_btn-default">取消</span>\
									<button type="button" class="confirm_btn confirm_btn-primary">确定</button>\
								</div>\
							</div>\
						</div>\
					</div>');
	$('body').append($confirm);
	$('head').append($style);
	if(!fn && !fn2){
		$confirm.find('.confirm_btn-default').remove();
	}
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











