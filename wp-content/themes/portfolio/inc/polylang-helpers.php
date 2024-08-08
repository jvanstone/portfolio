<?php 
/**
 * Polylang Helpers.
 */

/**
 * Get the proper link for page depending on the translation.
 * @param string $page_slug The slug the will need the correct ID.
 * @return string
 */
function get_translated_link( $page_slug ) {
	if ( 'projects' !== $page_slug ) {
		return get_the_permalink( pll_get_post( get_page_by_path( $page_slug )->ID ));
	} 
}