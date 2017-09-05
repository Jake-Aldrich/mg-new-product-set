<?php
/**
* Plugin Name: New Design Set Creator
* Plugin URI: http://momentgear.com/
* Description: Creates a csv list of products and their images using a single design
* Author: Jake Aldrich
* Version: 1.0
*/

/*
 * This function encapsulates creating the admin page
 * for this plugin
 */
function mg_csv_new_design_set() {
	// This function creates a link for our admin page
	// in the Products menu on the WordPress dashboard
	add_management_page( 'Create New Design Set', 'New Design Set', 'export', 'mg-csv-new-design-set', 'mg_csv_design_admin_form');
}

// The proper time to add Admin Menu Pages, is when
// the 'admin_menu' action takes place
add_action( 'admin_menu', 'mg_csv_new_design_set' );

function mg_csv_design_admin_form() {
	// This line gets the admin page from another file
	// in this directory
	require( dirname( __FILE__ ) . '/mg_csv_design_admin_form.php' );	
}

function mg_csv_new_product_set() {
	// This function creates a link for our admin page
	// in the Products menu on the WordPress dashboard
	add_management_page( 'Create New Product Set', 'New Product Set', 'export', 'mg-csv-new-product-set', 'mg_csv_product_admin_form');
}

// The proper time to add Admin Menu Pages, is when
// the 'admin_menu' action takes place
add_action( 'admin_menu', 'mg_csv_new_product_set' );

function mg_csv_product_admin_form() {
	// This line gets the admin page from another file
	// in this directory
	require( dirname( __FILE__ ) . '/mg_csv_product_admin_form.php' );	
}
?>