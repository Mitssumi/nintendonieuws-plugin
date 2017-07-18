(function( $ ) {
 
    "use strict";

$(window).load(function(){
  $(window).scroll(function() {
    var wintop = $(window).scrollTop(), docheight = $('.site-container').height(), winheight = $(window).height();
    console.log(wintop);
    var totalScroll = (wintop/(docheight-winheight))*100;
    console.log("total scroll" + totalScroll);
    $(".KW_progressBar").css("width",totalScroll+"%");
  });

});

})(jQuery);