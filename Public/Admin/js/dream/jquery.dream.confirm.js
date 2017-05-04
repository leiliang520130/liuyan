/** 
 * 确认框
 * @param 
 * 		text 	显示文本
 * 		r		确认回调函数
 */
(function($) {
    $.extend({
        confirm:function(text,r){
        	$ct = $('<div class="confirm"></div>');
        	$wrapper = $('<div class="wrapper"><div class="hmid"><div><p>'+ text + '</p></div></div></div>');
        	$btns = $('<div></div>');
        	$ensure = $('<a class="btn btn-danger" href="javascript:void(0)">确&nbsp;&nbsp;&nbsp;定</a>');
        	$cancel = $('<a class="btn" href="javascript:void(0)">取&nbsp;&nbsp;&nbsp;消</a>');
        	$btns.append($ensure).append($cancel);
        	$ct.append($wrapper).append($btns);
			$('body').append($ct);
			
			$cancel.click(function(){
				$ct.remove();
			})
			
			var f = function(){
				r();
				$ct.remove();
			}
			
			$ensure.on('click',f);
			
        } 
    });
})(jQuery);