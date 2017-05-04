var coSense = {
	loader:{
		init:function(type){
			this.catTab();
			this.wheelHeight(type);
		},
		catTab:function(){
			var flag = true;
			  $("div.side-btn").on("click",function(e){
			              e.stopPropagation();
			              if(!flag) return;
			              flag = false;
			              if($(this).hasClass('icon-close')){
				                $("span.show_sc").click();
				                $(this).removeClass('iconfont icon-close');
				                var t4,t5,t6;
			                              t4 = setTimeout(function(){
				                                   $(".menu-title").removeClass('opens');
				                                   t5 = setTimeout(function(){
				                                   		clearTimeout(t4);
					                                   $(".menu-list").removeClass('opens');
					                                   t6 = setTimeout(function(){
					                                   	clearTimeout(t5);
						                                   $(".menu-fllow").removeClass('opens');
						                                   flag = true;
					                                }, 0);
				                                }, 200);
			                                }, 200);
			                                clearTimeout(t6);	
			                	    //$("nav.menu").removeClass('opens');
		                                 	    setTimeout(function(){
		                                   		$("nav.menu").removeClass('open');
		                                   		flag = true;
		                                	}, 600);
			              }
			              else{
				                $(this).addClass('iconfont icon-close');
			                            $('.dz-mod').removeClass('open');
			                            if(!$("div.menu-btn-outer").hasClass('stalogi')){
				               	$(".menu-btn-outer>div").removeClass('on');
				                }				                
				                $("nav.menu").addClass('open');
				                var t1,t2,t3;
			                              t1 = setTimeout(function(){
				                                   $(".menu-title").addClass('opens');
				                                   t2 = setTimeout(function(){
				                                   		clearTimeout(t1);
					                                   $(".menu-list").addClass('opens');
					                                   t3 = setTimeout(function(){
					                                   	clearTimeout(t2);
						                                   $(".menu-fllow").addClass('opens');
						                                   flag = true;
					                                }, 200);
				                                }, 200);
			                                }, 200);
			                                clearTimeout(t3);			                                
				              }
			    });
			  /*hover***********menu*/
			 
			    $("div.menu-left div.menu-list ul li a").on('mouseenter',function(e) {
			    			
			              e.stopPropagation();
			              $("a.hover").removeClass('hover');
			              $(this).addClass('hover');
			              $("#"+$(this).parent().attr("class")).addClass('showm').siblings().removeClass('showm');
			    });
			    //menu-btn登录出现
			    $("span.menu-search").on("click",function(e){
			        e.stopPropagation();
			        var _this = $(this);
			        $(this).toggleClass('show_sc');
			        $("#menu0").toggleClass('show_sc');
			    });
			    $("div.menutop").on('mouseleave',function(e) {
			              e.stopPropagation();
			              if($(this).parents("menutop").length > 0){
			                   return;
			              }else{
			                  $("a.hover").removeClass('hover');
			                  $(".showm").removeClass('showm');
			              }
			    });
			    //menu-btn登录出现
			    $("div.menu-btn,div.menu-btn1").on("click",function(e){
			        e.stopPropagation();
			        var _this = $(this);
			        if($("div.side-btn").hasClass('icon-close')){
				        $("div.side-btn").click();
			        }
			        if(_this.hasClass('menu-btn1')){
			            _this.removeClass('on');
			            setTimeout(function(){
			                _this.prev().removeClass('on');
			            },0);
			        }else{
			             _this.addClass('on');
			              setTimeout(function(){
			                _this.next().addClass('on');
			            },0);
			        }
			        if($(this).hasClass('on')){
			            $(".dz-mod").addClass('open');
		                        $('.text-area').show();
		                        $('.js_popBox').hide();
		                        $('.js_popBox.js_loginBox').show();
		                        $(".js_emailRegSuc_jhuo").hide();
			        }else{
			            $(".dz-mod").removeClass('open');
			        }
			    });
		},
		wheelHeight:function(type){},
		bannerWheel:function(box,type){			
			if(type  == 1){
				var h1=box.height() + 80;
			}
			if(type == 2){
				var h1=box.height();
			}
			var h0 = parseInt($('.list_box').eq(0).css('padding-top'));
			var iPagerCount=5;
			var iPageNumber=0;
			var aTop=[0,h1];
			var sTop=$(window).scrollTop();
			if(sTop>=aTop[0]&&sTop<aTop[1]){iPageNumber=0;}		             
			//滑动滚动条翻屏效果
			$("html,body").bind("mousewheel",function(event,intDelta){
				var $this=$(this),
				timeoutId=$(this).data('timeoutId');
				if($(document).scrollTop() < h1){
					if(timeoutId){
						clearTimeout(timeoutId);
					}
					$this.data('timeoutId',setTimeout(function(){
						intDelta>0?pageUp():pageDown();
						$this.removeData('timeoutId');
						$this=null;
					},150));
					return false;
				}
			});
			
			function pageUp(){
				iPageNumber=iPageNumber<=0?0:iPageNumber-1;slide(aTop[iPageNumber]);
			}
			function pageDown(){
				iPageNumber=iPageNumber>=iPagerCount-1?iPageNumber:iPageNumber+1;slide(aTop[iPageNumber]);
			}	
			function slide(length){
				$("body,html").stop().animate({scrollTop:length},300,'easeOutExpo');
			}

			//扩展动画
			$.extend($.easing,{
				easeOutExpo:function(e,f,a,h,g){
					return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a
				},easeOutBounce:function(x,t,b,c,d){
					if((t/=d)<(1/2.75)){
						return c*(7.5625*t*t)+b;
					}else if(t<(2/2.75)){
						return c*(7.5625*(t-=(1.5/2.75))*t+.75)+b;
					}else if(t<(2.5/2.75)){
						return c*(7.5625*(t-=(2.25/2.75))*t+.9375)+b;
					}else{
						return c*(7.5625*(t-=(2.625/2.75))*t+.984375)+b;
					}
				}
			});
			
		},
		scrollDown:function(){
			$(document).on("click","p.frame-bottom_down",function(){
	    	var $this=$(this);
	    	var docScrollTop=$(document).scrollTop(),
	    	    docHeight=$(document).outerHeight(),
			    winHeight=$(window).height()-130,
			    screenNumb=parseInt((docHeight-docScrollTop)/winHeight);
			    if(docHeight%winHeight>100){
			    	screenNumb++;
			    };
			
			var scrollDistance=docScrollTop+winHeight;
				if(scrollDistance<=docHeight){
					$("body").stop().animate({scrollTop:scrollDistance},300);
				};
				
				if(docHeight-scrollDistance<winHeight){
					$this.removeClass("on");
					$("p.frame-bottom_up").addClass("on");
				}
	    });
	    $(window).scroll(function(){
	    	var docScrollTop=$(document).scrollTop(),
	    	    docHeight=$(document).outerHeight(),
	    	     winHeight=$(window).height();
	    	     if(docHeight-docScrollTop<=winHeight){
	    	     	$("p.frame-bottom_down").removeClass("on");
					$("p.frame-bottom_up").addClass("on");
	    	     }else{
	    	     	$("p.frame-bottom_up").removeClass("on");
					$("p.frame-bottom_down").addClass("on");
	    	     }
	    });
	    $(document).on("click","p.frame-bottom_up",function(){
	    	var $this=$(this);
	    	$("body").stop().animate({scrollTop:0},300);
	    	$this.removeClass("on");
			$("p.frame-bottom_down").addClass("on");
	    });
			/*function getStyle(el) {
		                        return document.defaultView &&document.defaultView.getComputedStyle ?document.defaultView.getComputedStyle(el, '') : el.currentStyle;
		             }
		             function getOffset(el) {
		                         var _t = 0, _l = 0;
		                         while (el.offsetParent) {
		                                       _t += el.offsetTop;
		                                       _l += el.offsetLeft;
		                                       el = el.offsetParent;
		                          }
		                          return [_t, _l];
		             }
		             if(id1){
		             	var s1 = document.getElementById(id1).getBoundingClientRect().top-80;
		             }
		             if(id2){
		             	var s2 = document.getElementById(id2).getBoundingClientRect().top-80;
		             }
		             if(id3){
		             	var s3 = document.getElementById(id3).getBoundingClientRect().top-80;
		             }			
			var s4 = document.getElementById('footer').getBoundingClientRect().top;
			var h0 = parseInt($('.list_box').eq(0).css('padding-top'));
			var h1=box.height() + 80;
			var h2=$('.list_box').eq(0).outerHeight() + h1 + 100;
			var h3=$('.list_box').eq(1).outerHeight() + $('.column').eq(0).height() + h2;
			var h4=$('.list_box').eq(2).outerHeight() + $('.column').eq(1).height() + h3 + 100;*/
			/*$(document).on("click","p.frame-bottom_down",function(){
				if($(document).scrollTop() >= 0 && $(document).scrollTop() <= h1){
					window.scrollTo(0,s1+1);					
				}
				else if($(document).scrollTop() > h1 && $(document).scrollTop() <= h2){
					window.scrollTo(0,s2+1);					
				}
				else if($(document).scrollTop() > h2 && $(document).scrollTop() <= h3){
					window.scrollTo(0,s3+80);					
				}
				else if($(document).scrollTop() > h3 && $(document).scrollTop() <= h4){
					window.scrollTo(0,s4+500);					
				}
			});*/
			/*返回顶部*/
			/*$(document).on('click', '.frame-bottom_up', function(event) {
			             event.preventDefault();
			             $("p.frame-bottom_up").removeClass('on').removeClass('bottoms');
			             $("p.frame-bottom_down").addClass("on");
			             window.scrollTo(0,1);
			             location.reload();
			});*/
			/*window.onscroll = function(){
			             var _t = document.documentElement.scrollTop || document.body.scrollTop;
			             var documentHeight = document.body.clientHeight || document.documentElement.clientHeight;
			             if (window.innerHeight)
			                          var winHeight = window.innerHeight;
			             else if ((document.body) && (document.body.clientHeight))
			                          var winHeight = document.body.clientHeight;
			             var dh = documentHeight - winHeight;
			             if(_t >= dh){
			                          $("p.frame-bottom_up").addClass('on').find("a").addClass('bottoms');
			                          $("p.frame-bottom_down").removeClass('on');
			                          //console.log("到底部了");
			             }else{
			                          $("p.frame-bottom_up").removeClass('on').removeClass('bottoms');
			                          $("p.frame-bottom_down").addClass("on");
			             }     
			}*/
		},
		toFooter:function(){
			function getStyle(el) {
		                        return document.defaultView &&document.defaultView.getComputedStyle ?document.defaultView.getComputedStyle(el, '') : el.currentStyle;
		             }
		             function getOffset(el) {
		                         var _t = 0, _l = 0;
		                         while (el.offsetParent) {
		                                       _t += el.offsetTop;
		                                       _l += el.offsetLeft;
		                                       el = el.offsetParent;
		                          }
		                          return [_t, _l];
		             }
		             var domHeight = document.body.clientHeight || document.documentElement.clientHeight;
		             $("p.frame-bottom_down").click(function(){
				window.scrollTo(0,domHeight);
				$("p.frame-bottom_up").addClass('on').find("a").addClass('bottoms');
			             $("p.frame-bottom_down").removeClass('on');
			});

		             /*返回顶部*/
			$(document).on('click', '.frame-bottom_up', function(event) {
			             event.preventDefault();
			             $("p.frame-bottom_up").removeClass('on').removeClass('bottoms');
			             $("p.frame-bottom_down").addClass("on");
			             $(document).scrollTop(81);
			             location.reload();
			});
			window.onscroll = function(){
			             var _t = document.documentElement.scrollTop || document.body.scrollTop;
			             var documentHeight = document.body.clientHeight || document.documentElement.clientHeight;
			             if (window.innerHeight)
			                          var winHeight = window.innerHeight;
			             else if ((document.body) && (document.body.clientHeight))
			                          var winHeight = document.body.clientHeight;
			             var dh = documentHeight - winHeight;
			             if(_t >= dh){
			                          $("p.frame-bottom_up").addClass('on').find("a").addClass('bottoms');
			                          $("p.frame-bottom_down").removeClass('on');
			                          //console.log("到底部了");
			             }else{
			                          $("p.frame-bottom_up").removeClass('on').removeClass('bottoms');
			                          $("p.frame-bottom_down").addClass("on");
			             }     
			}
		},
		originalJS:function(){
		   //作品最新筛选
		   $('.js_filter_btn').on('click',function(){
		       if($(this).find(".abtn").hasClass('on')){
		       		$(this).find(".abtn").removeClass('on');
		           		$(this).next('.js_downUl').fadeOut();
		       }
		       else{
		       		$(this).find(".abtn").addClass('on');
		           		$(this).next('.js_downUl').fadeIn();
		       	}
		    });
            $('.js_global_btn').on('click',function(){
                if($(this).hasClass('on')){
                    $(this).removeClass('on').next('.js_downDiv').fadeOut();
                }
                else{
                    $(this).addClass('on').next('.js_downDiv').fadeIn();
                }
            });
            $(".js_downDiv a.btn-ok").on("click",function(){
                $(this).parent().fadeOut().prev().removeClass('on');
            });
		    $(document).click(function(e){
		        var target=e.target;
		        if($(target).closest(".js_filter_btn").length>0){
		            return;
		        }else{
		            $(".js_downUl").hide();
		            $('.js_filter_btn').removeClass('on');
		        }
		    });
		},
		showreg:function(){
		    $("span.login_text").click();
		    $(".js_emailReg").show();
		    $(".js_register_box").hide();
		    $(".js_loginBox").hide();
		}
	}
};
//向上滚动一定高度
// function onload(){
//     var $js_perClum = $('#js_perClum');
//     var oH = $js_perClum.offset().top;
//     // $(document.body).animate({scrollTop:oH-80},10,function(){
//        $(document.body).scrollTop(oH-80);
//     	var scrollTop = $(window).scrollTop(),
// 	    totalheight = parseFloat($(window).height()) + parseFloat(scrollTop);
//     	if(($(document).height()) <= totalheight) {
//                    $("p.frame-bottom_up").addClass('on').find("a").addClass('bottoms');
//                    $("p.frame-bottom_down").removeClass('on');
//               }else{
//                      $("p.frame-bottom_up").removeClass('on').removeClass('bottoms');
//                      $("p.frame-bottom_down").addClass("on");
//               }
//     // });

//      coSense.loader.wheelHeight();
// }



/**
 * 获取url参数
 * @param $string 需要获取的url字段名字
 * @return string
 */
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURIComponent(r[2]);
    return null;
}
/**
 * 判断手机号码是否正确
 * @param $string 需要验证的字符串
 * @return boolean
 */
function isMobile(value) {
    return /^1[34578]\d{9}$/.test(value);
}
/**
 * 判断邮箱是否正确
 * @param $string 需要验证的字符串
 * @return boolean
 */
function isEmail(str){
    var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
    return reg.test(str);
}