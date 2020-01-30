var _batch_vals = new Array();
var gboy=(function(){
	
	return {
		//file 上传	
		uploads : function() {
			
			$('input[type="file"]').ace_file_input({
				no_file:'请上传文件',
				btn_choose:' 浏览 ',
				btn_change:'重新上传',
				droppable:true,
				onchange:null,
				thumbnail:false, //| true | large
				//whitelist:'gif|png|jpg|jpeg',
				//blacklist:'exe|php',
				allowExt: ['gif','png','jpg','jpeg'],
				allowMime: ["image/jpg", "image/jpeg", "image/png", "image/gif", "image/bmp"]
			 }).on('file.error.ace', function(event, info) {
                var objUrl = getObjectURL(this.files[0]);
                //alert(objUrl);
				 //nsDialog.jAlert("请上传excel格式文件","提示");
			});
		   
		},
		
		//提交
		send : function(){
			
			$('button[type=button].btn-info').click(function(){

				var form_obj=$(this).parents('form');
				var url=form_obj.attr('action');
				var data=form_obj.serialize();

                form_obj.submit();
				return false;

				if(url=='' || url==undefined) return false;

                $(form_obj).ajaxSubmit({
                    type:'post',
					url:url,
                    dataType:'json',
                    success:function(data){
                        if(data.status=='0'){

                            gboy.msg(data.message,'',5);

                        }else{

                            gboy.msg(data.message,data.referer,6);
                        }
                    },
                    error:function(){
                       gboy.msg('上传发生错误','',5)
                    }
                });

                // $.post(url,data,function(data){
                //
                //
				//
				//
				// },'json');

				
				return false;
			})
		
		},
		
		//提示
		msg : function(msg,url,ico){
			if(ico=='') ico='5';
			layer.alert(msg, {icon: ico},function(index){
				 layer.close(index);
				if(url) window.location.href = url;
			});
		},


		//设置file值
		file_value:function(){


            $('.widget-main').each(function(){
				var value=$(this).attr('data-title');
				if(value!='' && value!=undefined){

                    $(this).find('.file-name').attr('data-title',value);
                    $(this).find('i').eq(0).removeClass('icon-upload-alt').addClass('icon-picture');
                    $(this).find('label').eq(0).addClass('selected');
				}


			});
		},

		checked:function () {


        },




		//初始化
		init  : function(){
			
			gboy.uploads();
			gboy.send();
			gboy.file_value();
			gboy.checked();


		}
		
		
	}
	
	
})();

var _batch_vals = new Array();
jQuery(function($){




	gboy.init();


    //全选中与全不选中
    $("#check-all").on("click", function () {

        if($(this).is(":checked")){
            $("input[type='checkbox']:not(:disabled)").prop("checked", true);

        }else{
            $("input[type='checkbox']:not(:disabled)").prop("checked", false);

        }
        _batch_vals = [];
        $(".check-option").each(function(){
            if($(this).children("input").is(":checked")){

                _batch_vals.push($(this).children("input").val());
            }
        })


    });


    //全选状态下点击单个取消选中则取消全选状态
    $(".check-option input").on("click", function () {
        if(!$(this).is(":checked")){
            $("#check-all").attr("checked", false);
            $(this).parents(".tr").removeClass('selected');
        }else{
            $(this).parents(".tr").addClass('selected');
        }
        _batch_vals = [];
        $(".check-option").each(function(){
            if($(this).children("input").is(":checked")){
                _batch_vals.push($(this).children("input").val());
            }
        })
    });



    //工具条批量操作
    $("[data-ajax]").on("click", function(){

        var $key = $(this).data("ajax");

        if(_batch_vals.length < 1) {
            alert("请选择您要操作的数据！");
            return false;
        }
        var message = $(this).data("message") || "您是否确定进行此操作？";
        if(!confirm(message)) return false;
        var data = {};
        data[$key] = _batch_vals;
        $.post($(this).attr("href"), data, function(ret){
            alert(ret.message);
            if(ret.status==1){
                window.location.href=window.location.href;
            }
        },'json');
        return false;
    })


    $("[data-confirm]").on('click', function() {
        var message = $(this).data('confirm') || '您确定执行本操作？';
        return confirm(message);
    })

    $(document).on('click',"[data-iframe=true]", function() {
        var url=$(this).attr('href');
        var title=$(this).data('iframe-title');
        var w=$(this).data('iframe-width')+'px';
        var h=$(this).data('iframe-height')+'px';
        parent.layer.open({
            type: 2,
            title: title,
            btn: ['确定','取消'],
            shadeClose: false,
            resize:false,
            fixed:false,
            shade: 0.3,
            area: [w,h],
            content: url,
            yes: function(index, layero){

                var data=$(layero).find('iframe').contents().find('form').serialize();
                $.post(url,data,function(data){
                    alert(data.message);
                    if(data.status=='1'){
                            layer.closeAll();
                    }

                },'json');
            },
            no: function(index, layero){

            },

            success: function (layero) {

             }
        });

        return false;
    })






	
})


function uploadImage(num,input,area){

    parent.layer.open({
        type: 2,
        title: false,
        shadeClose: true,
        resize:false,
        fixed:false,
        shade: 0.3,
        area: ['900px', '580px'],
        content: '/index.php/attachment/index/webuploader?num='+num+'&input='+input+'&area='+area
    });
}

function getObjectURL(file) {
    var url = null;
    if (window.createObjectURL != undefined) { // basic
        $("#oldcheckpic").val("nopic");
        url = window.createObjectURL(file);
    } else if (window.URL != undefined) { // mozilla(firefox)
        $("#oldcheckpic").val("nopic");
        url = window.URL.createObjectURL(file);
    } else if (window.webkitURL != undefined) { // webkit or chrome
        $("#oldcheckpic").val("nopic");
        url = window.webkitURL.createObjectURL(file);
    }
    return url;
}

function iframe_val(value,obj){

    $parent=$(window.parent.document.body).find("#main_frame");
    if($parent.size()>0){

        $parent.contents().find(obj).val(value);
    }else{

        $(window.parent.document.body).find(obj).val(value);
    }

    parent.layer.closeAll();
}


function html_encode(str){
    str = str.replace(/&gt;/g, ">");
    str = str.replace(/&lt;/g, "<");
    str = str.replace(/&gt;/g, ">");
    str = str.replace(/&nbsp;/g, " ");
    str = str.replace(/&#39;/g, "\'");
    str = str.replace(/&quot;/g, "\"");
    return str;
}


/*
jQuery(function($) {
	
	//表格列表
	  var lang = {
		"sProcessing": "处理中...",
		"sLengthMenu": "每页 _MENU_ ",
		"sZeroRecords": "没有匹配结果",
		"sInfo": "当前第 _START_ 至 _END_ 页，共 _TOTAL_ 页。",
		"sInfoEmpty": "当前显示第 0 至 0 项，共 0 项",
		"sInfoFiltered": "(由 _MAX_ 项结果过滤)",
		"sInfoPostFix": "",
		"sSearch": "搜索:",
		"sUrl": "",
		"sEmptyTable": "表中数据为空",
		"sLoadingRecords": "载入中...",
		"sInfoThousands": ",",
		"oPaginate": {
			"sFirst": "首页",
			"sPrevious": "上页",
			"sNext": "下页",
			"sLast": "末页",
			"sJump": "跳转"
		},
		"oAria": {
			"sSortAscending": ": 以升序排列此列",
			"sSortDescending": ": 以降序排列此列"
		}
	  };
	  
  
	var oTable1 = $('#sample-table').dataTable( {
		"aoColumnsDefs":[
			{
				'aTargets':[0,6],
				'orderable':false
			}
		],
		"aoColumns": [{ "bSortable": true },null, null,null, null, null,{ "bSortable": true}] ,
		"oLanguage":lang,
		"bPaginate": true, //翻页功能  
		"bLengthChange": true, //改变每页显示数据数量  
		"bFilter": true, //过滤功能  
		"bSort": true, //排序功能  
		"bInfo": true,//页脚信息  
		"bAutoWidth": true//自动宽度
		
	});	

	$('table th input:checkbox').on('click' , function(){
		var that = this;
		$(this).closest('table').find('tr > td:first-child input:checkbox')
		.each(function(){
			this.checked = that.checked;
			$(this).closest('tr').toggleClass('selected');
		});
			
	});


	$('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
	function tooltip_placement(context, source) {
		var $source = $(source);
		var $parent = $source.closest('table')
		var off1 = $parent.offset();
		var w1 = $parent.width();

		var off2 = $source.offset();
		var w2 = $source.width();

		if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
		return 'left';
	}
 
	
})
*/