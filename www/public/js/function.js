$(document).on("touchend click", ".delprobtn", function () {
	var productInfo = $(this).parent(".productInfo");
    productInfo.fadeOut(500);
    setTimeout(function () {productInfo.remove()}, 500);
});
$(document).on("touchend click", ".deltags", function () {
	var protag = $(this).parent(".protag");
    protag.fadeOut(500);
    setTimeout(function () {protag.remove()}, 500);
});

$(document).on("touchend click", ".tagshref", function () {
	$(this).addClass("active").siblings().removeClass("active");
	$(this).parents(".tagsboxdiv").find(".addtagput").val('');
	$(this).parents(".tagsboxdiv").find(".addtagput").val($(this).text());
});
$(document).on("click", ".searchgoods", function () {
	$(this).addClass("active").siblings().removeClass("active");
	$('#searchtext').val('');
	$('#searchtext').val($(this).text());
});

$(document).on("click", ".tagsubmitbtn", function () {
	var paraval = $(this).parents(".tagsboxdiv").find(".addtagput").val();
	var codeinfo = '<span class="protag"><span class="protaganme">'+ paraval +'</span><i class="deltags"></i> <input type="hidden" name="tags[]" value="'+paraval+'" /></span>';
	$('.protagbox').append($(codeinfo).hide().fadeIn(500));
	$(".window-overlay").fadeOut();
	$(this).parents(".tagsboxdiv").find(".addtagput").val('');
});
//弹层
$(document).on("click",".overlay",function(){
	$(".window-overlay").fadeIn();
});
$(document).on("click",".downgoods",function(){
	var $this = $(this);
	var data = $this.attr('data-id');
	$('#goodsId').val(data);
	$(".window-overlay").fadeIn();
});

$(document).on("click",".tagresetbtn",function(){
	$(".window-overlay").fadeOut();
});
/**添加参数*/
$(document).on('click','.addparaInfobtn',function(){
	var number = $(".big-notification").length;
	var codeinfo = '<div class="big-notification"><h4 class="uppercase"><input type="text" class="parameTitput" value="" placeholder="请输入参数" name="goods_key['+(number)+']" /></h4><a href="#" class="close-big-notification">x</a><p><input type="text" class="parameTxt" value="" placeholder="请输入参数值" name="goods_val['+(number)+']"/></p></div>';         
	$('.addparaInfobox').append($(codeinfo).hide().fadeIn(500));
});

$(document).on("click",".radioCustom",function(){
	$(this).addClass("radio-twoCus-checked").siblings().removeClass("radio-twoCus-checked");
	$(this).find("input").attr("checked","checked");
	$(this).siblings().find("input").removeAttr("checked");
});

$(document).on("click",".sellhide",function(){
	$(this).siblings(".sellpricehide").fadeOut(500);
});
$(document).on("click",".sellpricebtn",function(){
	$(this).next().fadeIn(500);
});


//$(document).on("click",".recommend",function(){
//	if($(this).hasClass("active")){
//		$(this).removeClass("active");
//		$(this).html("推荐");
//	}else{
//		$(this).addClass("active");
//		$(this).html("取消推荐");
//	}
//});


$(document).on("click", ".undercarriage.up", function () {
	$this = $(this);
    var id = $this.attr('data-id');
    $.post('/goods/ground',{'id':id},function(result) {
    	if(result.success == 1) {
    		var productinfo = $this.parents(".productlistinfo");
    	    productinfo.fadeOut(500);
    	    setTimeout(function () {productinfo.remove()}, 500);
    	} else {
    		alert(result.error);
    	}
    },'json');
	
});

//登录
$(document).on("click", "#user_login", function () {
	var account = $("#login_account").val();
	if(!account || account.length < 1 ){
		alert('请输入账号');
		$("#login_account").val("");
		$("#login_account").focus();
		return false;
	}
	var password = $("#login_pwd").val();
	if(!password || password.length < 1 ){
		alert('请输入密码');
		$("#login_pwd").val("");
		return false;
	}
	$.post('/user/login',{'account':account,'password':password},function(result) {
		if(result.success == 1) {
			location.href = '/goods/list';
		} else {
			alert(result.error);
		}
	},'json')
});

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
$(document).on("click", '#addshop',function(){
	var name = $.trim($('.addshop .name').val());
	var province = $.trim($('.addshop .province').val());
	var city = $.trim($('.addshop .city').val());
	var area = $.trim($('.addshop .area').val());
	var contact = $.trim($('.addshop .contact').val());
	var tel = $.trim($('.addshop .tel').val());
	var weixin = $.trim($('.addshop .weixin').val());
	var account = $.trim($('.addshop .account').val());
	var pwd = $.trim($('.addshop .pwd').val());
	if(name == '') {
		alert('店铺名称不能为空');
		return false;
	}
	if(province == '') {
		alert('请选择省份');
		return false;
	}
	if(city == '') {
		alert('请选择城市');
		return false;
	}
	if(area == '') {
		alert('请选择地区');
		return false;
	}
	if(contact == '') {
		alert('请填写联系人');
		return false;
	}
	if(tel == '') {
		alert('请填写电话');
		return false;
	}
	if(weixin == '') {
		alert('请填写微信号');
		return false;
	}
	if(account == '') {
		alert('请填写账号');
		return false;
	}
	if(pwd == '') {
		alert('请填写密码');
		return false;
	}
	$.post('/shop/add',$('.addshop').serialize(),function(result) {
		if(result.success == 1) {
			alert('成功');
			location.reload();
		} else {
			alert(result.error);
		}
	},'json')
});
//上传文件点击
$(document).on("click", "#file_a", function () {
	$("#file").trigger("click");
});
//上传文件post
$(document).on("change","#file",function(){
    //空对象然后添加
    var fd = new FormData();
    fd.append("file", $(":file")[0].files[0]); //jQuery 方式
    //jQuery 方式发送请求
    $.ajax({
      type:"post",
      url:"/goods/uploadgoodsimg",
      data: fd,
      dataType:"json",
      processData: false,
      contentType: false
    }).done(function(res){
      //alert(res.success);
    var imghtml = '<div class="one-third productInfo"><a href="#"><img class="responsive-image" src="'+res.data.imgurl+'" alt="img"></a><span class="delprobtn"></span> <input type="hidden" name="image[]" value="'+res.data.path+'"/> </div>';
    $("#addproduct").before(imghtml);
    });
    return false;
});
//推荐状态判断
$(document).on("click", "#tuijianStatus", function () {
	
	if( $("#tuijianStatus > em ").hasClass("toggle-3-active-background") )
	{
		$("#isrecommend").attr("checked",true);
		$("#unrecommend").attr("checked",false);
	}else{
		$("#isrecommend").attr("checked",false);
		$("#unrecommend").attr("checked",true);
	}
});

//下架
$(document).on("click", "#downform", function () {
	$.post('/goods/downgoods',$('.downform').serialize(),function(result) {
		if(result.success ==1) {
			$(".window-overlay").fadeOut();
			location.reload();
		} else {
			alert(result.error);
			$(".window-overlay").fadeOut();
		}
	},'json');
});

//橱窗取消推荐
$(document).on("click",".unrecommend",function() {
	var $this = $(this);
	var data = $this.attr('data-id');
	$.post('/goods/unrecomm',{'id':data},function(result) {
		if(result.success == 1) {
			$this.parents('.productlistinfo').remove();
		} else {
			alert(result.error);
		}
	},'json');
});
//商品列表取消推荐
$(document).on("click",".unrecomm",function() {
	var $this = $(this);
	var data = $this.attr('data-id');
	$.post('/goods/unrecomm',{'id':data},function(result) {
		if(result.success == 1) {
			$this.removeClass("active");
			$this.removeClass("unrecomm");
			$this.addClass("recomm");
			$this.html("推荐");
		} else {
			alert(result.error);
		}
	},'json');
});
//推荐
$(document).on("click",".recomm",function() {
	var $this = $(this);
	var data = $this.attr('data-id');
	$.post('/goods/recomm',{'id':data},function(result) {
		if(result.success == 1) {
			$this.addClass("active");
			$this.addClass("active unrecomm");
			$this.removeClass("recomm");
			$this.html("取消推荐");
		} else {
			alert(result.error);
		}
	},'json');
});
//发货
$(document).on("click",".sendgoods",function() {
	var $this = $(this);
	if(confirm('确认发货吗？')) {
		var data = $this.attr('data-id');
		$.post('/order/send',{'id':data},function(result) {
			if(result.success == 1) {
				var html = '<span><a href="javascript:;" class="sendfinish" data-id="'+result.data+'">发货中</a></span>';
				$this.parents('.orderstatus').html(html);
			} else {
				alert(result.error);
			}
		},'json');
	} else {
		return false;
	}
	
});
//完成
$(document).on("click",".sendfinish",function() {
	var $this = $(this);
	if(confirm('确认完成吗？')) {
		var data = $this.attr('data-id');
		$.post('/order/sendfinish',{'id':data},function(result) {
			if(result.success == 1) {
				var html = '<span>完成</span>';
				$this.parents('.orderstatus').html(html);
			} else {
				alert(result.error);
			}
		},'json');
	} else {
		return false;
	}
	
});
