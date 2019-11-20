

var bili_width 		= 138;//压缩后图片的宽度固定为138
var bili_height 	= 138;//压缩后图片的高度固定为138
var quality 		= 0.8;//压缩后图片的质量,数字越小图片越模糊
var radius          = 69;//圆角px

$(function () {
    $("#pro_file_img").on('change',function () {
    	
    	var fileSizeLimit   = 2048000;//图片最大size2M
    	var fileTypeArray   = ['jpg','JPG','png','PNG','jpeg','JPEG'];
    	
    	$("#add_error").html('');
    	$("#add_error").css("display","none");
        //文件改变时,获取文件,并转化为base64字符串
        var file  	   = this.files[0]
        var fileName   = file.name;
        var fileType   = fileName.split(".")[1];//文件类型集合
        var fileSize   = file.size;//文件大小限制
        if(fileSize>fileSizeLimit){
        	$("#add_error").html("文件超出了大小限制！请控制在2M以内！");
            $("#add_error").css("display","block");
            return false;
        }
        
        if($.inArray(fileType, fileTypeArray)==-1){
        	$("#add_error").html("图片类型不对！请上传后缀为jpg,png的文件！");
            $("#add_error").css("display","block");
            return false;
        }
        var ready = new FileReader()
        ready.readAsDataURL(file);
        ready.onload = function (e) {
            var base64Img = e.target.result;
            $("#pre").attr("src",base64Img)
            compress(base64Img)//执行压缩
        }
    })
    
    $("#pro_file_bp").on('change',function () {
    	var fileSizeLimit   = 20480000;//文件最大size20M
    	var fileTypeArray   = ['pdf'];
    	
    	$("#add_error").html('');
    	$("#add_error").css("display","none");
        //文件改变时,获取文件,并转化为base64字符串
        var file  	   = this.files[0]
        var fileName   = file.name;
        var fileType   = fileName.split(".")[1];//文件类型集合
        var fileSize   = file.size;//文件大小限制
        if(fileSize>fileSizeLimit){
        	$("#add_error").html("文件超出了大小限制！请控制在20M以内！");
            $("#add_error").css("display","block");
            return false;
        }
        
        if($.inArray(fileType, fileTypeArray)==-1){
        	$("#add_error").html("文件类型不对！请上传pdf文件");
            $("#add_error").css("display","block");
            return false;
        }
        var ready = new FileReader()
        ready.readAsDataURL(file);
        ready.onload = function (e) {
            var base64Pdf = e.target.result;
            $("#combpFile").val(base64Pdf);
            $(".fileName").html(fileName);
            $(".fileUploadContent").css("display","block");
        }
    });
    
    $("#wechatkf").hover(function(){
    	alert("kkkkk");
	    $("#wechatkf_on").css("display","none");
	    $("#wechatkf_off").css("display","block");
	},function(){
		$("#wechatkf_on").css("display","none");
		$("#wechatkf_off").css("display","block");
	}); 
    
})

function compress(base64Img) {
	var newWidth 	= bili_width;
	var newHeight 	= bili_height;
	var newquality  = quality;
    var img    		= new Image();//创建一个空白图片对象
    img.src    		= base64Img;//图片对象添加图片地址
    
    img.onload = function () {//图片地址加载完后执行操作
    	if(img.width>=img.height){
    		if(img.width<=bili_width){
        		newWidth   = img.width;
        		newHeight  = img.width;
        		radius     = img.width/2;
        	}
        	
    	}else{
    		if(img.height<=bili_height){
    			newHeight  = img.height;
        		newWidth   = img.height;
        		radius     = img.height/2;
        	}
    	}
    	
    	if(img.width<bili_width && img.height<bili_height){
    		newquality  = 1;
    	}
        
    	//开始画压缩图
        var canvas      = document.createElement("canvas");
        var ctx         = canvas.getContext("2d");
        canvas.width    = newWidth;//压缩图的宽度
        canvas.height   = newHeight;//压缩图的高度
        roundedRect(ctx, 0, 0, newWidth, newHeight, radius);
        ctx.clip();
        ctx.drawImage(img,0,0,newWidth,newHeight);
        var newBase64 = canvas.toDataURL("image/png",newquality);

        //压缩后预览
        $("#next").attr("src",newBase64);

        //添加压缩后属性
        $("#compressFile").val(newBase64);
    }
}

function roundedRect(ctx, x, y, width, height, radius) {
    ctx.strokeStyle = "#fff";
    ctx.beginPath();
    ctx.moveTo(x, y + radius);
    ctx.lineTo(x, y + height - radius);
    ctx.quadraticCurveTo(x, y + height, x + radius, y + height);
    ctx.lineTo(x + width - radius, y + height);
    ctx.quadraticCurveTo(x + width, y + height, x + width, y + height - radius);
    ctx.lineTo(x + width, y + radius);
    ctx.quadraticCurveTo(x + width, y, x + width - radius, y);
    ctx.lineTo(x + radius, y);
    ctx.quadraticCurveTo(x, y, x, y + radius);
    ctx.stroke();
}
