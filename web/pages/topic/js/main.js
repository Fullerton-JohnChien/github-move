  $(function(){

    //Tab
    var _showTab = 0;

    var $defaultLi = $('ul.tabs li').eq(_showTab).addClass('highlight');
        $('.tab_content').eq($defaultLi.index()).siblings().hide();
        
        $('ul.tabs li').click(function() {
            var $this = $(this),
                _index = $this.index();
            $this.addClass('highlight').siblings('.highlight').removeClass('highlight');
            $('.tab_content').eq(_index).stop(false, true).fadeIn().siblings().hide();
            $('.navigation li:first-child').addClass('active').siblings('.active').removeClass('active');

            return false;
        }).find('a').focus(function(){
            this.blur();
    });


    function scrollNav() {
      $('.market-list-left li a').click(function(){  
        //Toggle Class
        $(".highlight").removeClass("highlight");      
        $(this).closest('li').addClass("highlight");

        var theClass = $(this).attr("class");
        $('.'+theClass).parent('li').addClass('highlight');
        //Animate
        $('html, body').stop().animate({
            scrollTop: $( $(this).attr('href') ).offset().top - 50
        }, 400);
        return false;
      });
    }
    scrollNav();

    if($(window).width()<1500){
                $('.left-side img').attr('src','img/nearby/block1-banner8-s.jpg')
            }
        $(window).resize(function() {
            if($(window).width()<1500){
                $('.left-side img').attr('src','img/nearby/block1-banner8-s.jpg')
            }
        });
   
});

