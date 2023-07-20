jQuery(document).ready(function ($) {
	// Content slider
	
	$('.yt-content-slider').each(function () {
		var $slider = $(this),
			$panels = $slider.children('div'),
			data = $slider.data();
		// Remove unwanted br's
		$slider.children(':not(.yt-content-slide)').remove();
		// Apply Owl Carousel
		$slider.owlCarousel({
			items:data.items,
			responsiveClass: true,
			mouseDrag: true,
			video:true,
			animateIn: data.transitionin,
    		animateOut: data.transitionout,
    		lazyLoad: (data.lazyload == 'yes') ? true : false,
			autoplay: (data.autoplay == 'yes') ? true : false,
			autoHeight: (data.autoheight == 'yes') ? false : false,
			autoplayTimeout: data.delay * 1000,
			smartSpeed: data.speed * 1000,
			autoplayHoverPause: (data.hoverpause == 'yes') ? true : false,
			center: (data.center == 'yes') ? true : false,
			loop: (data.loop == 'yes') ? true : false,
            dots: (data.pagination == 'yes') ? true : false,
            nav: (data.arrows == 'yes') ? true : false,
            margin: data.margin,
            navText: ['','']
		});
		
		
		if ($slider.find('.yt-testimonial')) {
			$slider.addClass('yt-testimonials-slider');
			if ($slider.find('.yt-testimonial').hasClass('yt-testimonial-style-1')) {
				$slider.addClass('yt-tmstyle1');
			}
			else if ($slider.find('.yt-testimonial').hasClass('yt-testimonial-style-2')) {
				$slider.addClass('yt-tmstyle2');
			}
			else if ($slider.find('.yt-testimonial').hasClass('yt-testimonial-style-3')) {
				$slider.addClass('yt-tmstyle3');
			}
		}

	});
});