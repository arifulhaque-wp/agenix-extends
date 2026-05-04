<?php
/**
 * Plugin Name: Agenix Extends
 * Plugin URI: https://www.example.com/agenix-extends
 * Description: Magnify product images on WooCommerce single product pages. Includes an Agenix-style dashboard.
 * Version: 1.0.0
 * Author: Agenix Team
 * Author URI: https://www.example.com/agenix-extends
 * Requires at least:   5.2
 * Requires PHP:        7.2
 * Text Domain: agenix-extends
 * Domain Path:         /languages
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AGENIX_EXTENDS_DIR', plugin_dir_path( __FILE__ ) );
define( 'AGENIX_EXTENDS_URL', plugin_dir_url( __FILE__ ) );

/* ------------------------------------------------------------------
 * Admin menu + Dashboard
 * ------------------------------------------------------------------ */
add_action( 'admin_menu', function() {
    add_menu_page(
        'Agenix Extends',
        'Agenix Extends',
        'manage_options',
        'agenix-extends',
        'agenix_extends_dashboard',
        'dashicons-block-default',
        30
    );
});

/* ------------------------------------------------------------------
 * Register settings for Magnify + Forms
 * ------------------------------------------------------------------ */
require AGENIX_EXTENDS_DIR.'inc/woo-magnify.php'; // magnify settings + logic
require AGENIX_EXTENDS_DIR.'inc/forms-cf7.php';   // CF7 settings + logic

/* ------------------------------------------------------------------
 * Add Settings link on Plugins page
 * ------------------------------------------------------------------ */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=agenix-extends')) . '">' . __('Settings', 'agenix-extends') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

/* ------------------------------------------------------------------
 * Dashboard output with tabs
 * ------------------------------------------------------------------ */
function agenix_extends_dashboard() {
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    ?>
    <div id="agenix-di-wrap" class="agenix-di-wrap">
        <h1><?php esc_html_e('Agenix Extends', 'agenix-extends'); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url(add_query_arg('tab','general')); ?>" class="nav-tab <?php echo $tab==='general'?'nav-tab-active':''; ?>">General</a>
            <a href="<?php echo esc_url(add_query_arg('tab','magnify')); ?>" class="nav-tab <?php echo $tab==='magnify'?'nav-tab-active':''; ?>">Magnify</a>
            <a href="<?php echo esc_url(add_query_arg('tab','forms')); ?>" class="nav-tab <?php echo $tab==='forms'?'nav-tab-active':''; ?>">Forms</a>
            <a href="<?php echo esc_url(add_query_arg('tab','contacts')); ?>" class="nav-tab <?php echo $tab==='contacts'?'nav-tab-active':''; ?>">Contacts</a>
        </h2>
        <div class="agenix-di-content">
            <?php
            if($tab==='general'){
                echo '<h3>General Information</h3><p>Agenix Extends adds extended features to other plugins.</p>';
            }
            elseif($tab==='magnify'){
                echo '<form method="post" action="options.php">';
                settings_fields('agenix_extends_magnify_group');
                do_settings_sections('agenix-extends-magnify');
                submit_button(__('Save Settings','agenix-extends'));
                echo '</form>';
            }
            elseif($tab==='forms'){
                echo '<form method="post" action="options.php">';
                settings_fields('agenix_extends_cf7_group');
                do_settings_sections('agenix-extends-cf7');
                submit_button(__('Save Settings','agenix-extends'));
                echo '</form>';
                agenix_extends_forms_ui();
            }


            elseif($tab==='contacts'){
                require AGENIX_EXTENDS_DIR.'inc/contact-info.php';
            }
            ?>
        </div>
    </div>
    <?php
}

/* ------------------------------------------------------------------
 * Admin CSS + JS
 * ------------------------------------------------------------------ */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'toplevel_page_agenix-extends' !== $hook ) return;
    wp_enqueue_style( 'agenix-extends-admin', AGENIX_EXTENDS_URL . 'assets/css/admin.css', [], '1.0.0' );
    wp_enqueue_script( 'agenix-extends-admin', AGENIX_EXTENDS_URL.'assets/js/admin.js',['jquery'],'1.0.0',true );
});