
/**
 * 登录页登录方式变化
 * @param id1
 * @param id2
 * @returns
 */
function clickTitle(id1,id2){ 
	$("#head"+id1).attr("class","login_head_actived");
	$("#head"+id2).attr("class","login_head_active");
	$("#login"+id1).css('display','block'); 
	$("#login"+id2).css('display','none');
}

/**
 * 帐号登录
 * @returns
 */
function loginPswCheck(){
	var phone 		  = $("#loginPswModal").find("input[name='phone_psw']").val();
    var password 	  = $("#loginPswModal").find("input[name='password']").val();
    var rember 	      = $("#loginPswModal").find("input[name='rember']").val();
    var jump_url 	  = $("#loginPswModal").find("input[name='loginPsw_url']").val();
    console.log(jump_url);
    var phone_pattern = /^1\d{10}$/;
    if (phone == '') {
    	$("#loginPsw_error").html("请填写手机号");
		$("#loginPsw_error").css('display','block'); 
        return false;
    }
    if(phone_pattern.exec(phone) == null){
        $("#loginPsw_error").html("手机号输入不正确");
        $("#loginPsw_error").css('display','block'); 
        return false;
    }
    
    if (password == '') {
        $("#loginPsw_error").html("请填写密码");
        $("#loginPsw_error").css('display','block'); 
        return false;
    }
    //$("#loginPswModal").submit();
    
    $.ajax({
        url: "/home/pub/dologinpsw",
        type: 'POST',
        dataType: 'json',
        data: {"phone_psw": phone,"password": password,"rember":rember},
        success: function(res){
        	if (res.code == 200) {
        		console.log(jump_url);
        		 window.location.href = jump_url;
            }else {
            	$("#loginPsw_error").html(res.msg);
                $("#loginPsw_error").css('display','block'); 
            }
        }
    })
}

/**
 * 手机登录
 * @returns
 */
function loginCheck(){
	var phone 		  = $("#loginModal").find("input[name='phone']").val();
    var imgcode 	  = $("#loginModal").find("input[name='imgcode']").val();
    var regcode 	  = $("#loginModal").find("input[name='regcode']").val();
    var jump_url 	  = $("#loginModal").find("input[name='login_url']").val();
   
    var phone_pattern = /^1\d{10}$/;
    if (phone == '') {
    	$("#login_error").html("请填写手机号");
    	$("#login_error").css('display','block'); 
        return false;
    }
    if(phone_pattern.exec(phone) == null){
        $("#login_error").html("手机号输入不正确");
        $("#login_error").css('display','block'); 
        return false;
    }
    
    if (imgcode == '') {
        $("#login_error").html("请填写图形验证码");
        $("#login_error").css('display','block'); 
        return false;
    }
    
    if (regcode == '') {
        $("#login_error").html("请填写手机验证码");
        $("#login_error").css('display','block'); 
        return false;
    }
    $("#login_error").css('display','none'); 
    $.ajax({
        url: "/home/pub/dologin",
        type: 'POST',
        dataType: 'json',
        data: {"phone": phone,"imgcode": imgcode,"regcode":regcode},
        success: function(res){
        	if (res.code == 200) {
        		if(jump_url==""){
        			window.location.href ="/home/member/index";
        		}else{
        			window.location.href = jump_url;
        		}
            }else {
            	$("#login_error").html(res.msg);
                $("#login_error").css('display','block'); 
            }
        }
    })
}


