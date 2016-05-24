	$(document).on("click",".tabs > span", function () {
		var $div_li = $(".tabs > span");
		var index = $div_li.index($(this));
		$(this).addClass("active").siblings().removeClass("active");
		$(".tabsbox > div").eq(index).show().siblings().hide();
	});
	
	
	$(document).on("click",".window-overlaybg",function(){
		$(".window-overlay").hide();
	});
	
	$(document).on("click",".disbtn",function(){
		$(".callhidebox").show();
	});
	$(document).on("click",".albtn",function(){
		$(".callhidebox").hide();
	});
	

	$(document).on("click",".cuidanjquery",function(){
		$(".cuidanhidebox").show();
	});
	$(document).on("click",".cuidanbottom",function(){
		$(".cuidanhidebox").hide();
	});
	


	$(document).on("click",".orderlieditIco",function(){
		$(".addresshidebox").show();
	});
	$(document).on("click",".deliclose,.wailibtn",function(){
		$(".addresshidebox").hide();
	});
	
	$(document).on("click",".delivedl",function(){
		$(this).addClass("active").siblings().removeClass("active");
	});
	
	
	$(document).on("click",".addressbtn",function(){
		$(".deliverlist").append($("#addressdiv").html());
		$(this).hide();
	});
	$(document).on("click",".resetdrebtn",function(){
		$(".deliverlist").find(".adressform").remove();
		$(".addressbtn").show();
	});
	
	$(document).on("click",".adddrebtn",function(){
		$(".deliverlist").find(".adressform").remove();
		$(".addressbtn").show();
	});