/**
 * Created by Administrator on 2017/9/6 0006.
 */

$(function(){
    //图片预览
    $('.add' ).on('click',function(){
        var imglen = $('.img img' ).length;
        var this_ = $(this );
        $('#upimg').click().off("change").on('change',function(){
            var size = ($(this)[0].files[0].size / 1024).toFixed(2);
            if(size <= 5120){
                var img = $(this)[0].files[0];
                var formData = new FormData();
                formData.append("picture",img);
                $.ajax({
                    type:"post",
                    url:"/home/File/uploadPicture",
                    timeout : 3000,
                    data:formData,
                    processData : false, // 告诉jQuery不要去处理发送的数据
                    contentType : false,  // 告诉jQuery不要去设置Content-Type请求头
                    beforeSend: function(XMLHttpRequest){
                        $(".showbox").show();
                        $('.swal2-confirm' ).css({'background-color':'#c1c1c1','border-left-color':'#c1c1c1','border-right-color':'#c1c1c1'})
                    },
                    success:function(data){
                        var msg = $.parseJSON(data);
                        if(msg.code == 1){
                            if(this_.hasClass('add')){
                                // 图片添加
                                if(imglen == 0){
                                    $('.add' ).fadeOut();
                                }
                                    $('.img').eq(imglen).removeClass('hide' )
                                    .after("<span class='closed'></span>")
                                    .append('<img src='+msg.data.path+' alt="图片" data-tab='+msg.data.id+'>');
                            }else{
                                // 图片修改
                                this_.find('img').remove();
                                this_.append('<img src='+msg.data.path+' alt="图片" data-tab='+msg.data.id+'>');
                            }
                            imgresize();
                            $(".showbox").hide();
                        } else {
                            $(".showbox").hide();
                            return;
                        }
                        $(".imgs .closed").on("click",function(e){
                            var Id = $(this).find("img").attr("data-tab");
                            var This = $(this);
                            swal({
                                title: "是否删除当前图片",
                                showConfirmButton: true,
                                showCancelButton: true,
                                confirmButtonText: "确定",
                                cancelButtonText: "取消",
                                confirmButtonColor: "#ec6c62"
                            }, function() {
                                if(!This.hasClass("hide")){
                                    This.prev(".img").find("img").remove();
                                    This.prev(".img").addClass("hide");
                                    This.remove();
                                    $(".add").show();
                                    $("#upimg").val("");
                                }
                            });
                        });
                    },
                    error : function(){
                        $(".showbox").hide();
                        swal({
                            title: ' ',
                            text: '上传失败，请重试',
                            type: 'error',
                            showConfirmButton:false,
                            timer:1500
                        });
                    }
                });
            } else {
                swal({
                    title: ' ',
                    text: '您的图片超过5M',
                    type: 'warning',
                    showConfirmButton:false,
                    timer:1500
                });
            }
        });

    });
});


//剪裁图片
function imgresize(){
    var font = parseInt($("html").css("font-size"))*2;
    setTimeout(function(){
        var img = $('.img img');
        var img2 = $('.img2 img');
        var img3 = $('.img3 img');
        var img4 = $('.img4 img');
        img.each(function(){
            if($(this).width() == $(this).height()){
                $(this).height('20vw');
                $(this).width('20vw');
            }else if($(this).width() > $(this).height()){
                $(this).height('20vw' ).css({'left':-$(this).width()/2+font/2});
            }else{
                $(this).width('20vw').css({'top':-$(this).height()/2+font/2});
            }
        });
    },100);
}