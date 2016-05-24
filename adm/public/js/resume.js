$('#baseform').click(function(){
	if($.trim($('.baseform').find("input[name='name']").val()) == '') {
		alert('姓名不能为空');
		return false;
	}
	if($.trim($('.baseform').find("input[name='nickname']").val()) == '') {
		alert('昵称不能为空');
		return false;
	}
	if($.trim($('.baseform').find("input[name='phone']").val()) == '') {
		alert('手机号码不能为空');
		return false;
	}
	if($.trim($('.baseform').find("select[name='birthday']").val()) == '') {
		alert('出生年月不能为空');
		return false;
	}
	if($.trim($('.baseform').find("select[name='degrees']").val()) == '') {
		alert('学历不能为空');
		return false;
	}
	if($.trim($('.baseform').find("select[name='workyear']").val()) == '') {
		alert('工作年限不能为空');
		return false;
	}
	if($.trim($('.baseform').find("select[name='wages']").val()) == '') {
		alert('薪酬不能为空');
		return false;
	}
	$.post('/resume/userbase',$('.baseform').serialize(),function(result){
		$('#basedata').html('');
        if(result.success == 1) {
        	var sex = '';
        	if(result.data.Sex == 0) {
        		sex = '';
        	} else if(result.data.Sex == 1) {
        		sex = '男';
        	} else if(result.data.Sex == 2) {
        		sex = '女';
        	} else if(result.data.Sex == 3) {
        		sex = '保密';
        	}
        	var str = '<div class="sumedivli"><span class="nickname">'+result.data.Name+'</span>|<span class="sex">'+sex+'</span>|<span class="degree">'+result.data.Degrees+'</span></div>';
				str += '<div class="sumedivli"><span class="experience">'+result.data.WorkYear+'年工作经验</span>|<span class="phone">'+result.data.Phone+'</span></div>';
        	$('#basedata').append(str);
			var curbodyTop = $(".window-overlay").attr("offsetTop");
        	$(".window-overlay").fadeOut();
        	$("body").css({"height":"auto","overflow":"visible"});
        	$("html").css({"height":"auto","overflow":"visible"});
        	$(document).scrollTop(curbodyTop);
        } else {
            alert(result.error);
        }
    },'json')
	 return false;
});
$('#workform').click(function(){
	if($.trim($('.workform').find("input[name='name']").val()) == '') {
		alert('公司名称不能为空');
		return false;
	}
	if($.trim($('.workform').find("input[name='position']").val()) == '') {
		alert('职位不能为空');
		return false;
	}
	if($.trim($('.workform').find("select[name='start']").val()) == '') {
		alert('工作开始时间不能为空');
		return false;
	}
	if($.trim($('.workform').find("select[name='end']").val()) == '') {
		alert('工作结束时间不能为空');
		return false;
	}
	if($.trim($('.workform').find("select[name='start']").val()) > $.trim($('.workform').find("select[name='end']").val())) {
		alert('开始时间不能大于结束时间不能为空');
		return false;
	}
	if($.trim($('.workform').find("textarea[name='contents']").val()) == '') {
		alert('工作内容不能为空');
		return false;
	}
	var wid = $('.workform').find('.workid').val();
	$.post('/resume/userwork',$('.workform').serialize(),function(result){
		if(result.success == 1) {
			if(!wid) {
				var str = '<li class="sumeulli"><span class="Timeslot">';
				str += '<span class="infoworkstart">'+result.data.StartTime+'</span>年-<span class="infoworkend">'+result.data.EndTime+'</span>年</span>';
				str += '<span class="company">'+result.data.Company+'</span><span class="editsumeli editworksbtn" data-d="'+result.data.Id+'"></span><span class="delsumeli delwork" data-d="'+result.data.Id+'"></span></li>';
				$('#workdata').append(str);
			} else {
				$('.editworksbtn').each(function(){
					if($(this).attr('data-d') == wid) {
						$(this).parent().find('.infoworkstart').text(result.data.StartTime);
						$(this).parent().find('.infoworkend').text(result.data.EndTime);
						$(this).parent().find('.company').text(result.data.Company);
					}
				})
			}
			
			var curbodyTop = $(".window-overlay").attr("offsetTop");
			$(".window-overlay").fadeOut();
			$("body").css({"height":"auto","overflow":"visible"});
			$("html").css({"height":"auto","overflow":"visible"});
			$(document).scrollTop(curbodyTop);
		} else {
			alert(result.error);
		}
	},'json')
	return false;
});
$('#projectform').click(function(){
	if($.trim($('.projectform').find("input[name='name']").val()) == '') {
		alert('项目名称不能为空');
		return false;
	}
	if($.trim($('.projectform').find("select[name='start']").val()) == '') {
		alert('项目开始时间不能为空');
		return false;
	}
	if($.trim($('.projectform').find("select[name='end']").val()) == '') {
		alert('项目结束时间不能为空');
		return false;
	}
	if($.trim($('.projectform').find("select[name='start']").val()) > $.trim($('.projectform').find("select[name='end']").val())) {
		alert('开始时间不能大于结束时间不能为空');
		return false;
	}
	if($.trim($('.projectform').find("textarea[name='describe']").val()) == '') {
		alert('项目描述不能为空');
		return false;
	}
	if($.trim($('.projectform').find("textarea[name='duty']").val()) == '') {
		alert('项目描述不能为空');
		return false;
	}
	var pid = $('.projectform').find('.projid').val();
	$.post('/resume/userproject',$('.projectform').serialize(),function(result){
		if(result.success == 1) {
			if(!pid) {
				var str = '<li class="sumeulli"><span class="Timeslot">';
				str += '<span class="infoworkstart">'+result.data.StartTime+'</span>年-<span class="infoworkend">'+result.data.EndTime+'</span>年</span>';
				str += '<span class="projectname">'+result.data.Name+'</span><span class="editsumeli editprojectbtn" data-d="'+result.data.Id+'"></span><span class="delsumeli delproj" data-d="'+result.data.Id+'"></span></li>';
				$('#projectdata').append(str);
			} else {
				$('.editprojectbtn').each(function(){
					if($(this).attr('data-d') == pid) {
						$(this).parent().find('.infoworkstart').text(result.data.StartTime);
						$(this).parent().find('.infoworkend').text(result.data.EndTime);
						$(this).parent().find('.projectname').text(result.data.Name);
					}
				})
			}
			var curbodyTop = $(".window-overlay").attr("offsetTop");
			$(".window-overlay").fadeOut();
			$("body").css({"height":"auto","overflow":"visible"});
			$("html").css({"height":"auto","overflow":"visible"});
			$(document).scrollTop(curbodyTop);
		} else {
			alert(result.error);
		}
	},'json')
	return false;
});
$('#eduform').click(function(){
	if($.trim($('.eduform').find("input[name='name']").val()) == '') {
		alert('学校名称不能为空');
		return false;
	}
	if($.trim($('.eduform').find("input[name='major']").val()) == '') {
		alert('所学专业不能为空');
		return false;
	}
	if($.trim($('.eduform').find("select[name='degrees']").val()) == '') {
		alert('学历不能为空');
		return false;
	}
	if($.trim($('.eduform').find("select[name='time']").val()) == '') {
		alert('毕业年份不能为空');
		return false;
	}
	var eid = $('.eduform').find('.eduid').val();
	$.post('/resume/usereducation',$('.eduform').serialize(),function(result){
		if(result.success == 1) {
			if(!eid) {
				var str = '<li class="sumeulli"><span class="Timeslot">';
				str += '<span class="edutime">'+result.data.GraduateTime+'</span>年</span>';
				str += '<span class="college">'+result.data.SchoolName+'</span>';
				str += '<span class="profession">'+result.data.Major+'</span>';
				str += '<span class="Undergraduate">'+result.data.Degrees+'</span>';
				str += '<span class="editsumeli editeducation" data-d="'+result.data.Id+'"></span><span class="delsumeli deledu" data-d="'+result.data.Id+'"></span></li>';
				$('#edudata').append(str);
			} else {
				$('.editeducation').each(function(){
					if($(this).attr('data-d') == eid) {
						$(this).parent().find('.edutime').text(result.data.GraduateTime);
						$(this).parent().find('.college').text(result.data.SchoolName);
						$(this).parent().find('.profession').text(result.data.Major);
						$(this).parent().find('.Undergraduate').text(result.data.Degrees);
					}
				})
			}
			var curbodyTop = $(".window-overlay").attr("offsetTop");
			$(".window-overlay").fadeOut();
			$("body").css({"height":"auto","overflow":"visible"});
			$("html").css({"height":"auto","overflow":"visible"});
			$(document).scrollTop(curbodyTop);
		} else {
			alert(result.error);
		}
	},'json')
	return false;
});
$('#productionform').click(function(){
	if($.trim($('.productionform').find("input[name='title']").val()) == '') {
		alert('作品标题不能为空');
		return false;
	}
	if($.trim($('.productionform').find("textarea[name='describe']").val()) == '') {
		alert('作品标题不能为空');
		return false;
	}
	var pid = $('.productionform').find('.prodid').val();
	$.post('/resume/userproduction',$('.productionform').serialize(),function(result){
		if(result.success == 1) {
			if(!pid) {
				var str = '<li class="sumeulli"><span class="production">'+result.data.Title+'</span>';
				str += '<span class="editsumeli editproduct" data-d="'+result.data.Id+'"></span><span class="delsumeli delprod" data-d="'+result.data.Id+'"></span></li>';
				$('#productiondata').append(str);
			} else {
				$('.editproduct').each(function(){
					if($(this).attr('data-d') == pid) {
						$(this).parent().find('.production').text(result.data.Title);
					}
				})
			}
			
			var curbodyTop = $(".window-overlay").attr("offsetTop");
			$(".window-overlay").fadeOut();
			$("body").css({"height":"auto","overflow":"visible"});
			$("html").css({"height":"auto","overflow":"visible"});
			$(document).scrollTop(curbodyTop);
		} else {
			alert(result.error);
		}
	},'json')
	return false;
});

$('#applyform').click(function(){
	if($.trim($('.applyform').find("textarea[name='contents']").val()) == '') {
		alert('申请内容不能为空');
		return false;
	}
	$.post('/resume/apply',$('.applyform').serialize(),function(result){
		if(result.success == 1) {
			$('.applybtn').text('评定中，请耐心等待结果!');
			$('.applybtn').removeClass('resetpan');
			$('.applybtn').removeClass('applybtn');
			var curbodyTop = $(".window-overlay").attr("offsetTop");
			$(".window-overlay").fadeOut();
			$("body").css({"height":"auto","overflow":"visible"});
			$("html").css({"height":"auto","overflow":"visible"});
			$(document).scrollTop(curbodyTop);
		} else {
			alert(result.error);
		}
	},'json');
	return false;
});

$('.delwork').click(function() {
	var $this = $(this);
	var wid = $(this).attr('data-d');
	
	if(confirm('确实要删除吗?')) {
		$.post('/resume/delwork',{'id':wid},function(result) {
			if(result.success == 1) {
				$this.parent().remove();
			} else {
				alert(result.error);
			}
		},'json');
	}
});

$('.delproj').click(function() {
	var $this = $(this);
	var pid = $(this).attr('data-d');
	if(confirm('确实要删除吗?')) {
		$.post('/resume/delproject',{'id':pid},function(result) {
			if(result.success == 1) {
				$this.parent().remove();
			} else {
				alert(result.error);
			}
		},'json');
	}
});

$('.deledu').click(function() {
	var $this = $(this);
	var eid = $(this).attr('data-d');
	if(confirm('确实要删除吗?')) {
		$.post('/resume/deleducation',{'id':eid},function(result) {
			if(result.success == 1) {
				$this.parent().remove();
			} else {
				alert(result.error);
			}
		},'json');
	}
});

$('.delprod').click(function() {
	var $this = $(this);
	var did = $(this).attr('data-d');
	if(confirm('确实要删除吗?')) {
		$.post('/resume/delproduction',{'id':did},function(result) {
			if(result.success == 1) {
				$this.parent().remove();
			} else {
				alert(result.error);
			}
		},'json');
	}
});
