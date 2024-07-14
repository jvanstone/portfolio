import $ from 'jquery';
import 'slick-slider';
import 'bootstrap';

(($) => {
	"use strict";
	console.log('test');
	/** 
	 *  Featured Post Slider
	 * 
	 */
	$('.section--featured-posts__slider').slick({
		arrows: true,
		dots: true,
		infinite: true,
		speed: 300,
		slidesToShow: 4,
		slidesToScroll: 4,
		responsive: [
		{
			breakpoint: 1024,
			settings: {
			slidesToShow: 2,
			slidesToScroll: 2,
			infinite: true,
			dots: true
			}
		},
		{
			breakpoint: 600,
			settings: {
			slidesToShow: 2,
			slidesToScroll: 2
			}
		},
		{
			breakpoint: 480,
			settings: {
			slidesToShow: 1,
			slidesToScroll: 1
			}
		}
		]
	});

	/** offcanvasPrimary */
	$(window).scroll(function() {    
		var scroll = $(window).scrollTop();

		if (scroll >= 50) {
			$(".header").addClass("fixed-top");
		} else {
			$(".header").removeClass("fixed-top");
		}
	});

	$('#offcanvasPrimary a').click(function(){
		$('.offcanvas').offcanvas('hide');
	});


	/** Accordion and Media */

	const $sectionAccordions = $('.component--accordion-and-media'); 
	const $screenWidthMinimum = window.matchMedia("(min-width: 768px)");

	if ($sectionAccordions.length) (() => {

		$sectionAccordions.each((index, section) => {

			const $accordionContents = $(section).find('#accordion-content');
			const $imageContainer   = $(section).find('#accordion-thumbnail-desktop');
			const $openAccordion    = $accordionContents.find('.accordion-collapse.show');
			const $accordionButtons = $accordionContents.find('.accordion-button');
			
			const showNewThumbnail = (accordion) => {
				const $accordionThumbnail = accordion.find('.accordion-thumbnail');

				$imageContainer.attr('src', $accordionThumbnail.attr('src'));
				$imageContainer.attr('alt', $accordionThumbnail.attr('alt'));

				$accordionThumbnail.hide();
			};

			if ($openAccordion.length && $screenWidthMinimum.matches) showNewThumbnail($openAccordion);

			$accordionButtons.each((index, button) => {

				const $siblingContentID = $(button).data('bs-target');
				const $siblingContent = $(section).find($siblingContentID);
				
				$(button).on('click', () => {
					if ($screenWidthMinimum.matches) {
						showNewThumbnail($siblingContent);
					};
				});
			});

			window.onresize = () => {

				if ($openAccordion.length && $screenWidthMinimum.matches) {
					showNewThumbnail($openAccordion);
				}
			};
		});
	})();
})(jQuery);
