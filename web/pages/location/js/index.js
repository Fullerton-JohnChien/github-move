$(function(){


	var _currentIndex;
	$('.area, .tag, .sub').hide();
	$('.menu .main-list a').on('click', function(event) {
		event.preventDefault();
		var _parentName = $(this).parent('li').attr('id');
		var _currentIndex = $(this).parent('li').index();
		$(this).parent('li').addClass('selected arrow-up').siblings('li').removeClass('selected arrow-up');
		$('.area, .tag').show();
		$('.sub').slideUp(300);
		switch (_parentName) {
			case "m01":
			case "m04":
				$('#' + _parentName).find('.sub').slideDown();
			break;
			default:
				$('.sub').slideUp(300);
		}


		$('.area .area-list li, .tag .tag-list li').removeClass('selected');
	});

	//main-list .sub hide
	$('.sub li').on('click', function() {
		$(this).parent('.sub').slideUp(300);
	});



	$('.area .area-list a').on('click', function(event) {
		event.preventDefault();
		var _parentName = $(this).parent('li').attr('id');

		$(this).parent('li').toggleClass('selected');

	});



	$('.item-list .img-collect').on('click', function() {
		$(this).find('i').toggleClass('fa-heart').toggleClass('fa-heart-o');
	});
	
	$('.tag-list li').on('click', function() {
		$(this).toggleClass('selected');
	});



	// select element styling
	/*$(.'div.select select').each(function(){
		var title = $(this).attr('title');
		if( $('option:selected', this).val() != ''  ) title = $('option:selected',this).text();
		$(this)
			.css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
			.after('<span class="select">' + title + '</span>')
			.change(function(){
				var val = $('option:selected',this).text();
				$(this).next().text(val);
				})
	});
*/
   
});