<?php

$_BannersZones = array(
	'top_banner' => array(
		'title' => 'Top Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'reseller' => false,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
	'listings_banner' => array(
		'title' => 'Listings Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'reseller' => false,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),

	'side_banner' => array(
		'title' => 'Side Banner',
		'width' => '60',
		'height' => '160',
		'resize' => 'width',
		'reseller' => true,
		'maxWidth' => false, // true: max width 100% in app, false: use image width
	),
);