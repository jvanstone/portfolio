<?php
/**
 * The Template for the sidebar containing the main widget area
 *
 * @package  WordPress
 * @subpackage  Timber
 */
use Timber\Timber;

Timber::render( array( 'partial/sidebar.twig' ), $data );
