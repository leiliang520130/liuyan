$(document).ready(function() {
      //初始化
      coSense.loader.init(true);
      //登录注册忘记密码
      proing();
      //轮播图片初始化
      /*new Slider($('div.character-stage'), {
            time: 3000,
            delay: 400,
            event: 'hover',
            auto: true,
            mode: 'top',
            controller: $()
        });*/
      jQuery("#slideBox").slide({mainCell:".character-stage ul#top",autoPage:false,effect:"leftLoop",autoPlay:true,mouseOverStop:false});
      //coSense.loader.bannerWheel($('.slideBox'),1);
      coSense.loader.scrollDown();
});