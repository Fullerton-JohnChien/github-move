$(function() {

	var _tourLeftValue = -300,
		_improveRightValue = 0;

	//hotel infomation panel toggle
	$('.tour-pane .toggle-button').on('click', function(event) {
		$(this).parent('.tour-pane').animate({
				left: _tourLeftValue
			},
			300,
			function() {
				$('.tour-pane .toggle-button').toggleClass('fa-angle-left').toggleClass('fa-angle-right');
				_tourLeftValue = (_tourLeftValue == -300) ? 0 : -300;
			});
	});


	// improving panel toggle
	var a=1;
	$('.improve-block .toggle-button').click(function() {
		if(a){
			$(".improve-block .contect").fadeIn(200);
			$(".overlay").fadeIn();
			$('i', this).toggleClass('fa-angle-right').toggleClass('fa-angle-left');
		} else{
			$(".improve-block .contect").hide();
			$(".overlay").hide();
			$('i', this).toggleClass('fa-angle-left').toggleClass('fa-angle-right');
		}
		a = a == 1? 0:1;
	});


	$('.img-collect').on('click', function() {
		$(this).find('i').toggleClass('fa-heart').toggleClass('fa-heart-o');
	});



	var _swiperSlideW = 214, //pic's width
		_num,
		_point,
		_finalNum;

	$(window).on('resize', function() {

		_num = ($(window).width() / _swiperSlideW).toFixed(1); //calculate slide number (get one decimal place)
		_point = Number(_num.split('.')[1]);

		$('.block-swiper .swiper-slide').addClass('opacity50');


		if (_point >= 5) _finalNum = Math.floor(_num);
		else _finalNum = Math.floor(_num - 1);

		for (var i = 1; i < _finalNum; i++) {
			$('.block-swiper .swiper-slide').eq(i).removeClass('opacity50').css({
				cursor: 'pointer'
			});
		}

		$('.block-swiper .arrow-right').css({
			right: 'auto',
			left: _swiperSlideW * _finalNum + 1
		});

	}).resize();



	$('.arrow-left, .arrow-right').on('click', function(e) {
		e.preventDefault();
		var _id = $(this).attr('id'),
			$list = $('.swiper-wrapper'),
			$slide = $('.swiper-wrapper .swiper-slide'),
			_current;


		$slide.addClass('opacity50');

		switch (_id) {
			case 'aLeft':

				$list.prepend($slide.eq(_finalNum - 1).css('margin-left', -_swiperSlideW).stop(true, true).animate({
					'margin-left': 0
				}, 500, 'easeOutSine'));
				$slide.eq(0).css('margin-left', 0);

				for (var i = 0; i < _finalNum - 1; i++) {
					$slide.eq(i).removeClass('opacity50').css({
						cursor: 'pointer'
					});
				}

				break;
			case 'aRight':
				$slide.eq(0).stop(true, true).animate({
					'margin-left': -_swiperSlideW
				}, 500, 'easeOutSine', function() {
					$list.append($(this).css('margin-left', ''));
				});

				for (var i = 2; i < _finalNum + 1; i++) {
					$slide.eq(i).removeClass('opacity50').css({
						cursor: 'pointer'
					});
				}
				break;
		}

	}); //arrow





})
