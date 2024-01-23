//import $ from 'jquery';
//import slick from 'slick-carousel';

jQuery( document ).ready(function( $ ) {
  // Code that uses jQuery's $ can follow here.
	
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
});