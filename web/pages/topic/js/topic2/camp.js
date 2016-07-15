  $(function(){

    //Tab
    var _showTab = 0;

    var $defaultLi = $('ul.tabs li').eq(_showTab).addClass('active');
        $('.tab_content').eq($defaultLi.index()).siblings().hide();
        
        $('ul.tabs li').click(function() {
            var $this = $(this),
                _index = $this.index();
            $this.addClass('active').siblings('.active').removeClass('active');
            $('.tab_content').eq(_index).stop(false, true).fadeIn().siblings().hide();

            return false;
        }).find('a').focus(function(){
            this.blur();
    });

    function scrollNav() {
      $('.left-column a').click(function(){  
        //Toggle Class
        //Animate
        $('html, body').stop().animate({
            scrollTop: $( $(this).attr('href') ).offset().top - 50
        }, 400);
        return false;
      });

    }
    scrollNav();

});

