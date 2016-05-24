/*
 * 手机绑定页面
 */
	//重发函数
	function repeat() {
		var $repeat = $(".phonerightpan").html();
		if ($repeat == "重发") {
			readSecond();
		}
	}
	//读秒函数
	function readSecond() {
		var i = 60,
			timer = setInterval(function() {
				i--;
				$(".phonerightpan").html(i + "秒后重发");
				if (i == 0) {
					$(".phonerightpan").html("重发");
					clearInterval(timer);
				}
			}, 1000);
	}
	//提交手机号
	function subPhoneNum(phoneNum){
		$.ajax({
			type:"get",
			url:"",
			data:{},
		});
	}
/*
 * index页面
 */

	

	//列表页瀑布流
	function waterfall() {
		var maxHeight = 0;
		Div = document.getElementsByClassName("imgList");
		for(var j=0;j<Div.length;j++){
			imgs = Div[j].getElementsByTagName("dl");
			for (var i = 0; i < 2; i++) {
				var obj = new Object();
				obj.left = i * imgs[0].offsetWidth + (i + 1) * 3;
				obj.top = 0;
				positionArray.push(obj);
			}
		
		
			for (var k = 0; k < imgs.length; k++) {
				var index = minTopIndex();
				imgs[k].style.left = positionArray[index].left + "px";
				imgs[k].style.top = positionArray[index].top + "px";
				positionArray[index].top += imgs[k].offsetHeight+10;
			}
			
			var maxIndex = maxTopIndex();
			Div[j].style.height = positionArray[maxIndex].top+'px';
			if(positionArray[maxIndex].top>maxHeight){
				maxHeight = positionArray[maxIndex].top;
			}
			positionArray = new Array(); 
		}
		
		document.getElementsByClassName('main')[0].style.height = maxHeight+'px';
		
	
	}
	
	function minTopIndex() {
		var minTop = positionArray[0].top;
		var index = 0;
		for (var i = 0; i < positionArray.length; i++) {
			if (positionArray[i].top < minTop) {
				minTop = positionArray[i].top;
				index = i;
			}
		}
		return index;
	}
	

	function maxTopIndex() {
		var minTop = positionArray[0].top;
		var index = 0;
		for (var i = 0; i < positionArray.length; i++) {
			if (positionArray[i].top > minTop) {
				minTop = positionArray[i].top;
				index = i;
			}
		}
		return index;
	}
