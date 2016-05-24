//全部订单切换
	$(document).on("touchend click",".tabs > span", function () {
		var $div_li = $(".tabs > span");
		var index = $div_li.index($(this));
		$(this).addClass("active").siblings().removeClass("active");
		$(".tabsbox > div").eq(index).show().siblings().hide();
	});
	//删除订单
	$(document).on("touchend click",".deleteOrder",function(){
		$(this).parent().parent().remove();
	});
	
//电话议价窗口	
	$(document).on("touchend click",".disbtn",function(){
		$(".callhidebox").show();
	});
	$(document).on("click",".del",function(){
		$(".callhidebox").hide();
	});
	
//待发货页面 催单窗口
	$(document).on("touchend click",".cuidanjquery",function(){
		$(".cuidanhidebox").show();
	});
	$(document).on("touchend click",".cuidanbottom",function(){
		$(".cuidanhidebox").hide();
	});
	
//地址相关
	$(document).on("touchend click",".orderlieditIco",function(){
		$(".addresshidebox").show();
	//删除地址
		$(".deleteAdress").click(function(){
			$(this).parent().parent().remove();
		})
	//更改地址
		$(".changeAdress").click(function(){
			//console.log($(".deliverlist").find(".active").children("dd").eq(0).html());
			var name = $(".deliverlist").find(".active").children("dd").eq(0).html();
			var ad = $(".deliverlist").find(".active").children("dd").eq(1).html();
			$(".orderpro").children("p").eq(0).html(name).children(".deleteAdress").remove();
			$(".orderpro").children("p").eq(1).html(ad);
		})
	});
	$(document).on("touchend click",".deliclose,.wailibtn",function(){
		$(".addresshidebox").hide();
	});
	
	$(document).on("touchend click",".delivedl",function(){
		$(this).addClass("active").siblings().removeClass("active");
	});
	
//新增地址
	$(document).on("touchend click",".addressbtn",function(){
		$(".deliverlist").append($("#addressdiv").html());
		$(".selectbtn").hide();
		$(".delivedl").remove();
		//下拉地址
//		$("#city").citySelect({
//			nodata: "none",
//			required: false
//		});
		$(".adressform ul li input").click(function(){
			$("html,body").animate({scrollTop:0});
		})
		$(".adddrebtn").click(function(){
		//用户地址
			var $name = $(".nameput").val(); 		
			var $tel = $(".telphput").val();
			var $prov = $(".prov").val();
			var $city = $(".city").val();
			var $dist = $(".dist").val() == null ? '' : $(".dist").val();		
			var $adrss = $(".detailput").val();
			
			if($name!=''&&$tel!=''&&$adrss!=''&&$prov!=''&&$city!=''){
//				var $dl = $('<dl class="delivedl active"><dt class="deliIco fl"></dt><dd><span>'+$name+'</span>'+$tel+'<span class="deleteAdress">删除</span></dd><dd>'+$prov+$city+$dist+$adrss+'</dd></dl>');
				var $dl1 = $('<span>'+$name+'</span>'+'<span>'+$tel+'</span>');
				var $dl2 = $prov+$city+$dist+$adrss;
				$('.addruser').html( $dl1 );
				$('.addraddress').html( $dl2 );
				var $html = "<input type='hidden' neme='province' value='"+$prov+"'>";
				$html += "<input type='hidden' class='hiddenipt' name='city' value='"+$city+"'>";
				$html += "<input type='hidden' class='hiddenipt' name='area' value='"+$dist+"'>";
				$html += "<input type='hidden' class='hiddenipt' name='address' value='"+$adrss+"'>";
				$html += "<input type='hidden' class='hiddenipt' name='name' value='"+$name+"'>";
				$html += "<input type='hidden' class='hiddenipt' name='tel' value='"+$tel+"'>";
//				$(".deliverlist").append($dl);
//				$(".deliverlist").find(".adressform").remove();
				$(".hiddenipt").remove();
				$('.submitbtn').before( $html );
//				$(".addressbtn").show();
//				$(".selectbtn").show();
				$(".addresshidebox").hide();
			} else {
				alert( '信息不完整，请填写完整' );
			}
			
		})
		$(this).hide();
	});
	
	$(document).on("touchend click",".resetdrebtn",function(){
		$(".deliverlist").find(".adressform").remove();
		$(".addressbtn").show();
	});
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
