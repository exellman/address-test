<?php
/**
 * Plugin Name: Address Custom Post Type
 * Description: Address Custom Post Type
 * Version: 1.0
 * Author: Eugeniy Kanaiev
 */

if (!defined('ABSPATH')) {
    exit;
}

// Registration of custom post type "Address"
function register_address_cpt() {
    $args = array(
        'labels' => array(
            'name' => __('Addresses', 'textdomain'),
            'singular_name' => __('Address', 'textdomain'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'address/%address_category%', 'with_front' => false), // add category slug to permalink
        'menu_position' => 5,
        'menu_icon' => 'dashicons-location',
        'supports' => array('title', 'editor'),
        'show_in_rest' => true
    );

    register_post_type('address', $args);
}
add_action('init', 'register_address_cpt');

// Registration of custom taxonomy "Address Category"
function register_address_category_taxonomy() {
    $args = array(
        'labels' => array(
            'name' => __('Categories', 'textdomain'),
            'singular_name' => __('Category', 'textdomain'),
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'address', 'hierarchical' => true),
        'show_in_rest' => true
    );

    register_taxonomy('address_category', 'address', $args);
}
add_action('init', 'register_address_category_taxonomy');

// Add custom rewrite rules for address CPT
function address_custom_permalink($post_link, $post) {
    if ($post->post_type == 'address') {
        $terms = get_the_terms($post->ID, 'address_category');
        if ($terms && !is_wp_error($terms)) {
            $post_link = str_replace('%address_category%', array_pop($terms)->slug, $post_link);
        } else {
            $post_link = str_replace('%address_category%', 'uncategorized', $post_link);
        }
    }
    return $post_link;
}
add_filter('post_type_link', 'address_custom_permalink', 10, 2);

// Add Address page on plugin activation
function create_address_archive_page() {
    $page = get_page_by_path('address');
    if (!$page) {
        wp_insert_post(array(
            'post_title'     => 'Address',
            'post_name'      => 'address',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_content'   => '',
            'post_author'    => get_current_user_id(),
        ));
    }
}
register_activation_hook(__FILE__, 'create_address_archive_page');

// Add disable permalink metabox to Address CPT
function add_permalink_disable_metabox() {
    add_meta_box(
        'disable_permalink',
        'Disable Permalink',
        'disable_permalink_metabox_callback',
        'address',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_permalink_disable_metabox');

// Add checkbox to address CPT edit screen
function disable_permalink_metabox_callback($post) {
    $value = get_post_meta($post->ID, '_disable_permalink', true);
    wp_nonce_field('disable_permalink_nonce', 'disable_permalink_nonce_field');
    ?>
    <label for="disable_permalink">
        <input type="checkbox" name="disable_permalink" id="disable_permalink" value="1" <?php checked($value, '1'); ?> />
        Disable permalink for this address
    </label>
    <?php
}

// Save the value of the checkbox
function save_disable_permalink_metabox($post_id) {
    if (!isset($_POST['disable_permalink_nonce_field']) || !wp_verify_nonce($_POST['disable_permalink_nonce_field'], 'disable_permalink_nonce')) {
        return;
    }
    
    if (isset($_POST['disable_permalink'])) {
        update_post_meta($post_id, '_disable_permalink', '1');
    } else {
        delete_post_meta($post_id, '_disable_permalink');
    }
}
add_action('save_post', 'save_disable_permalink_metabox');

// Check if permalink is disabled and redirect to 404 page
function check_disable_permalink() {
    if (is_singular('address')) {
        global $post;
        $disable_permalink = get_post_meta($post->ID, '_disable_permalink', true);
        if ($disable_permalink == '1') {
            wp_redirect(home_url('404'));
            exit;
        }
    }
}
add_action('template_redirect', 'check_disable_permalink');

// Add tamplate for address CPT and taxonomy
function address_template_include($template) {
    if (is_singular('address')) {
        $template = plugin_dir_path(__FILE__) . '/templates/single-address.php';
    } elseif (is_tax('address_category')) {
        $template = plugin_dir_path(__FILE__) . '/templates/taxonomy-address_category.php';
    }
    return $template;
}
add_filter('template_include', 'address_template_include');

?>