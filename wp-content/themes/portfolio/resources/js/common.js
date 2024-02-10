//import AOS from 'aos';
//import 'bootstrap/dist/js/bootstrap';
//import 'slick-slider';

(($) => {
	"use strict";
	//AOS.init();
	
	/** 
	 *  Featured Post Slider
	 * 
	 */
	$('.featured-posts__slider').slick({
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


	/** Accordion Side Image */

	const $sectionClients = $('.section--clients'); 
	const $screenWidthMinimum = window.matchMedia("(min-width: 768px)");

	if ($sectionClients.length) (() => {

		$sectionClients.each((index, section) => {

			const $accordionClients = $(section).find('#accordion-clients');
			const $imageContainer   = $(section).find('#client-thumbnail-desktop');
			const $openAccordion    = $accordionClients.find('.accordion-collapse.show');
			const $accordionButtons = $accordionClients.find('.accordion-button');
			
			const showNewThumbnail = (accordion) => {
				const $clientThumbnail = accordion.find('.client-thumbnail');

				$imageContainer.attr('src', $clientThumbnail.attr('src'));
				$imageContainer.attr('alt', $clientThumbnail.attr('alt'));

				$clientThumbnail.hide();
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

				console.log($openAccordion);
			
				if ($openAccordion.length && $screenWidthMinimum.matches) {
					showNewThumbnail($openAccordion);
				}
				
			};
		});
	})();
})(jQuery);
