(($) => {
	"use strict";

	const $searchprojects = $('#search-projects');

	if ($searchprojects.length > 0) (() => {
	
		// 1. Creating the Select Options for the Search.
		const $taxonomyBadges   = $('.taxonomy-badges > li.badge');
		const $selectTechnology = $searchprojects.find('#select-technology');

		/**
		 * Putting the Badge titles into an Array.
		 * @param {string} items Badge Titles.
		 * @returns {array}
		 */
		const putBadgesIntoAnArray = (items) => {

			const labels = [];
			
			$(items).each((index, element) => {

				const label = $(element).text();

				if (!labels.includes(label)) labels.push(label);
			});

			return labels;
		};

		/**
		 * Create the options from an Array.;
		 * @param {string} element The select for the new options.
		 * @param {array} array The dataElements that will create the new options.  
		 */
		const createOptionsFromArray = (select, array) => {
			
			$(array).each((index, dataElement)=>{
				$(select).append(new Option(dataElement, dataElement.replace(/\s+/g, '').toLowerCase()));
			});
		};

		if ($selectTechnology) {
			const badgesArray = putBadgesIntoAnArray($taxonomyBadges);
			createOptionsFromArray($selectTechnology, badgesArray.sort());
		}

		// 2. Add the Search functionality.
		const $articles = $('.post-wrapper');
		const $noResultsDiv = $('#no-results');
		const $resetButtons = $('.btn-reset');
		
		const filterResults = (formData) => { 

			const inputKeywords = formData.get('search');
			const technologies = formData.getAll('technology');

			if (inputKeywords || technologies) {

				$articles.filter((index, article) => {

					const $article = $(article);

					let articleKeywords = $article.find('h2, p').text().toLowerCase();
					let articleBadges = putBadgesIntoAnArray($article.find('.taxonomy-badges > li.badge'));
		
					articleBadges = articleBadges ? String(articleBadges).split('|') : [];
					articleBadges = articleBadges.map(v => v.replace(/\s+/g, '').toLowerCase());

					if (technologies.length && !technologies.some((technology) => articleBadges[0].indexOf(technology.toLowerCase()) > -1)) {
						return true;
					}

					if (inputKeywords && articleKeywords.indexOf(inputKeywords.toLowerCase()) === -1) {
						return true;
					}
	
					return false;
				}).hide();

				const $filteredArticles = $articles.filter(':visible');

				if (!$filteredArticles.length) {
					showNoResults();
				} 
			}
		};

		const showNoResults = () => {
			$noResultsDiv.show();
		};

		const resetResults = () => {
			$noResultsDiv.hide();
			$resetButtons.hide();
			$articles.show().visible();
		};

		const getFilteredResults = (e) => {
			e.preventDefault();
	
			const formData = new FormData(e.currentTarget);
	
			resetResults();
			filterResults(formData);
			$resetButtons.show();
		};

		let resourceKeywordsTimeout = undefined;

		$searchprojects.on('keyup', (e) => {

			clearTimeout(resourceKeywordsTimeout);

			resourceKeywordsTimeout = setTimeout(() => {
				getFilteredResults(e);
			}, 500);
		}); 

		$searchprojects.on('change', (e) => {
			getFilteredResults(e); 
		});

		$searchprojects.on('submit', (e) => {
			getFilteredResults(e);
		});

		$resetButtons.each((index, element) => {
			const $button = $(element);

			$button.on('click', () => {
				resetResults();				
			});
		});
	})();
})(jQuery);