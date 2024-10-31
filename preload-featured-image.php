<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Name: Preload Featured Image
 * Plugin URI: https://wordpress.org/plugins/preload-featured-image/
 * Description: This plugin preloads the featured image in posts to increase the PageSpeed score.
 * Version: 1.0
 * Author: Yoo Digital
 * Author URI: https://yoodigital.co
 * Text Domain: preload-featured-image
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

add_action('wp_head', 'preload_featured_image');

function preload_featured_image() {
    if (is_single() && has_post_thumbnail()) {
        $options = get_option('preload_featured_image_settings');
        $preload_enabled = isset($options['preload_enable']) ? $options['preload_enable'] : false;

        if ($preload_enabled) {
            $featured_image_url = wp_get_attachment_image_url(get_post_thumbnail_id(), 'full');
            echo '<link rel="preload" href="' . esc_url($featured_image_url) . '" as="image">';
        }
    }
}

add_action('admin_menu', 'preload_featured_image_menu');

add_action('admin_menu', 'preload_featured_image_menu_link');

function preload_featured_image_menu_link() {
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'preload_featured_image_settings_link');
}

function preload_featured_image_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=preload-featured-image">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

function preload_featured_image_menu() {
    add_options_page('Preload Featured image Settings', 'Preload Featured image', 'manage_options', 'preload-featured-image', 'preload_featured_image_settings_page');
}

function preload_featured_image_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('preload_featured_image');
            do_settings_sections('preload_featured_image');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'preload_featured_image_register_settings');

function preload_featured_image_register_settings() {
    register_setting('preload_featured_image', 'preload_featured_image_settings');
    
    add_settings_section('preload_featured_image_section', 'Preload Featured image Settings', 'preload_featured_image_section_callback', 'preload_featured_image');
    
    add_settings_field('preload_image_size', 'Select Image Size', 'preload_image_size_callback', 'preload_featured_image', 'preload_featured_image_section');
    add_settings_field('preload_enable', 'Enable Preload', 'preload_enable_callback', 'preload_featured_image', 'preload_featured_image_section');
}

function preload_featured_image_section_callback() {
    echo 'Customize Preload Featured image settings below:';
}

add_option('preload_featured_image_settings', array('preload_enable' => true), '', 'no');

function preload_image_size_callback() {
    $options = get_option('preload_featured_image_settings');
    $image_size = isset($options['preload_image_size']) ? $options['preload_image_size'] : 'full';
    
    $sizes = get_intermediate_image_sizes();
    
    echo '<select name="preload_featured_image_settings[preload_image_size]">';
    foreach ($sizes as $size) {
        echo '<option value="' . esc_attr($size) . '" ' . selected($image_size, $size, false) . '>' . esc_html($size) . '</option>';
    }
    echo '</select>';
}

function preload_enable_callback() {
    $options = get_option('preload_featured_image_settings');
    $preload_enabled = isset($options['preload_enable']) ? $options['preload_enable'] : false;

    echo '<label><input type="checkbox" name="preload_featured_image_settings[preload_enable]" value="1" ' . checked(1, $preload_enabled, false) . '> Enable Preload</label>';
}
