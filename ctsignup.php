<?php
/*
Plugin Name: CTSignup
Plugin URI: https://www.calculatietool.com
Description: Calculatietool Signup client for CalculatieToo.com
Version: 1.1
Author: CalculatieTool.com
Author URI: https://www.calculatietool.com
License: BSD
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	exit;
}

define( 'CTSINGUP_VERSION', '1.1' );
define( 'CTSINGUP__MINIMUM_WP_VERSION', '3.2' );
define( 'CTSINGUP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ABSPATH . WPINC . '/pluggable.php' );
require_once( CTSINGUP__PLUGIN_DIR . 'class.ct.php' );

add_action( 'init', array( 'CalculatieTool', 'init') );

// Direct requests to observers
CalculatieTool::helper();

