<?php
/*
Plugin Name: CPM Investors Profile
Description: A plugin to create a custom post type for Investors.
Version: 1.0
Author: Ranju and Prasna
License: GPL2
*/
// Enqueue Select2 for both front-end and admin
function cpm_investor_enqueue_scripts() {
    // Enqueue Select2 CSS
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    
    // Enqueue Select2 JS
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), null, true);
    
    // Enqueue custom script for initializing Select2
    wp_enqueue_script('cpm-initializer', plugins_url('cpm-initializer.js', __FILE__), array('jquery', 'select2-js'), null, true);
}
add_action('wp_enqueue_scripts', 'cpm_investor_enqueue_scripts');
add_action('admin_enqueue_scripts', 'cpm_investor_enqueue_scripts');

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to register the custom post type
function cpm_investor_register_post_type() {

    $labels = array(
        'name'                  => _x( 'Investors', 'Post Type General Name', 'textdomain' ),
        'singular_name'         => _x( 'Investor', 'Post Type Singular Name', 'textdomain' ),
        'menu_name'             => __( 'Investors', 'textdomain' ),
        'name_admin_bar'        => __( 'Investor', 'textdomain' ),
        'archives'              => __( 'Investor Archives', 'textdomain' ),
        'attributes'            => __( 'Investor Attributes', 'textdomain' ),
        'parent_item_colon'     => __( 'Parent Investor:', 'textdomain' ),
        'all_items'             => __( 'All Investors', 'textdomain' ),
        'add_new_item'          => __( 'Add New Investor', 'textdomain' ),
        'add_new'               => __( 'Add New', 'textdomain' ),
        'new_item'              => __( 'New Investor', 'textdomain' ),
        'edit_item'             => __( 'Edit Investor', 'textdomain' ),
        'update_item'           => __( 'Update Investor', 'textdomain' ),
        'view_item'             => __( 'View Investor', 'textdomain' ),
        'view_items'            => __( 'View Investors', 'textdomain' ),
        'search_items'          => __( 'Search Investor', 'textdomain' ),
        'not_found'             => __( 'Not found', 'textdomain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'textdomain' ),
        'featured_image'        => __( 'Featured Image', 'textdomain' ),
        'set_featured_image'    => __( 'Set featured image', 'textdomain' ),
        'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
        'use_featured_image'    => __( 'Use as featured image', 'textdomain' ),
        'insert_into_item'      => __( 'Insert into investor', 'textdomain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this investor', 'textdomain' ),
        'items_list'            => __( 'Investors list', 'textdomain' ),
        'items_list_navigation' => __( 'Investors list navigation', 'textdomain' ),
        'filter_items_list'     => __( 'Filter investors list', 'textdomain' ),
    );
    $args = array(
        'label'                 => __( 'Investor', 'textdomain' ),
        'description'           => __( 'Post Type for Investors', 'textdomain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
    );
    register_post_type( 'cpm_investor', $args );

}

// Hook into the 'init' action
add_action( 'init', 'cpm_investor_register_post_type', 0 );

// Shortcode to display the form
function cpm_investor_submission_form() {
    ob_start();
    ?>
<form action="" method="post" enctype="multipart/form-data">
    <label for="investor_name">Name of Investor:</label>
    <input type="text" id="investor_name" name="investor_name" required><br /><br />

    <label for="investor_description">Short Description:</label>
    <textarea id="investor_description" name="investor_description" rows="4" cols="50" required></textarea><br><br>

    <label for="investor_founded">Founded in:</label>
    <input type="date" id="investor_founded" name="investor_founded" required><br /><br />

    <label for="investor_type">Investor Type:</label>
    <select id="investor_type" name="investor_type[]" multiple="multiple" required>
        <option value="VC">VC</option>
        <option value="Accelerator">Accelerator</option>
    </select><br><br>

    <label for="investor_logo">Logo:</label>
    <input type="file" id="investor_logo" name="investor_logo" accept="image/*" required><br><br>

    <input type="submit" name="submit_investor" value="Submit">
</form>
<?php
    return ob_get_clean();
}
add_shortcode('cpm_investor_form', 'cpm_investor_submission_form');

// Handle form submission
function cpm_investor_handle_form_submission() {
    if ( isset( $_POST['submit_investor'] ) && isset( $_POST['investor_name'] ) && isset( $_POST['investor_description'] ) && isset( $_POST['investor_founded'] ) ) {
        $investor_name = sanitize_text_field( $_POST['investor_name'] );
        $investor_description = sanitize_textarea_field( $_POST['investor_description'] );
        $investor_founded = sanitize_text_field( $_POST['investor_founded'] );
        $investor_type = array_map('sanitize_text_field', $_POST['investor_type']);

        // Create a new post of type 'cpm_investor'
        $new_post = array(
            'post_title'   => $investor_name,
            'post_content' => $investor_description,
            'post_status'  => 'draft',
            'post_type'    => 'cpm_investor'
        );
        if ( array_key_exists( 'cpm_investor_type', $_POST ) ) {
            $investor_type = array_map( 'sanitize_text_field', $_POST['cpm_investor_type'] );
            update_post_meta(
                $post_id,
                'cpm_investor_type',
                $investor_type
            );
        }
 

        // Insert the post into the database
        $post_id = wp_insert_post( $new_post );

        // Save the 'founded in' year as post meta
        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, 'cpm_investor_founded', $investor_founded);
            update_post_meta($post_id, 'cpm_investor_type', $investor_type);
        }
        // Handle the logo upload and set it as the featured image
        if (!empty($_FILES['investor_logo']['name'])) {
            $file = $_FILES['investor_logo'];
            $upload = wp_handle_upload($file, array('test_form' => false));

            if (!isset($upload['error']) && isset($upload['file'])) {
                $filetype = wp_check_filetype(basename($upload['file']), null);
                $wp_upload_dir = wp_upload_dir();

                $attachment = array(
                    'guid' => $wp_upload_dir['url'] . '/' . basename($upload['file']),
                    'post_mime_type' => $filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($post_id, $attach_id);
            }
        }
    }
}

add_action( 'init', 'cpm_investor_handle_form_submission' );

// Display the 'founded in' year in the post edit screen
function cpm_investor_add_meta_box() {
    add_meta_box(
       'cpm_investor_meta_box',
        'Investor Details',
        'cpm_investor_meta_box_callback',
        'cpm_investor',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'cpm_investor_add_meta_box' );


 // Save the 'founded in' year and 'investor type' as post meta admin side 

function cpm_investor_meta_box_callback( $post ) {
    $value = get_post_meta( $post->ID, 'cpm_investor_founded', true );
    $type_value = get_post_meta($post->ID, 'cpm_investor_type', true);
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    if (!is_array($type_value)) {
        $type_value = array();
    }
    ?>
<label for="cpm_investor_founded">Founded in:</label>
<input type="date" id="cpm_investor_founded" name="cpm_investor_founded"
    value="<?php echo esc_attr( $value ); ?>"><br /><br />

<label for="cpm_investor_type">Investor Type:</label>
<select id="cpm_investor_type" name="cpm_investor_type[]" multiple="multiple">
    <option value="VC" <?php echo in_array('VC', $type_value) ? 'selected' : ''; ?>>VC</option>
    <option value="Accelerator" <?php echo in_array('Accelerator', $type_value) ? 'selected' : ''; ?>>Accelerator
    </option>
</select>
<?php
} 

// Save the 'founded in' year from the post edit screen
function cpm_investor_save_meta_box_data( $post_id ) {
if ( array_key_exists( 'cpm_investor_founded', $_POST ) ) {
update_post_meta(
$post_id,
'cpm_investor_founded',
sanitize_text_field( $_POST['cpm_investor_founded'] )
);
}
if (array_key_exists('cpm_investor_type', $_POST)) {
$investor_type = array_map('sanitize_text_field', $_POST['cpm_investor_type']);
update_post_meta(
$post_id,
'cpm_investor_type',
$investor_type
);
}
// Handle the logo upload and set it as the featured image
if (!empty($_FILES['cpm_investor_logo']['name'])) {
$file = $_FILES['cpm_investor_logo'];
$upload = wp_handle_upload($file, array('test_form' => false));

if (!isset($upload['error']) && isset($upload['file'])) {
$filetype = wp_check_filetype(basename($upload['file']), null);
$wp_upload_dir = wp_upload_dir();

$attachment = array(
'guid' => $wp_upload_dir['url'] . '/' . basename($upload['file']),
'post_mime_type' => $filetype['type'],
'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
'post_content' => '',
'post_status' => 'inherit'
);

$attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
require_once(ABSPATH . 'wp-admin/includes/image.php');
$attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
wp_update_attachment_metadata($attach_id, $attach_data);
set_post_thumbnail($post_id, $attach_id);
}
}
}

add_action( 'save_post', 'cpm_investor_save_meta_box_data' );

// Remove Custom Fields meta box for custom post type 'cpm_investor'
function cpm_investor_remove_custom_fields_meta_box() {
remove_meta_box( 'postcustom', 'cpm_investor', 'normal' );
}
add_action( 'do_meta_boxes', 'cpm_investor_remove_custom_fields_meta_box' );

//to load the single page
function load_investor_template($template) {
    if (is_singular('cpm_investor')) {
        $plugin_path = plugin_dir_path(__FILE__);
        $template_name = 'single-cpm_investor.php';
        $template = $plugin_path . $template_name;
    }
    return $template;
}
add_filter('template_include', 'load_investor_template');

//enqueue styles for single page
function my_custom_plugin_enqueue_styles() {
    if (is_singular('cpm_investor')) {
        wp_enqueue_style('single-investor-style', plugin_dir_url(__FILE__) . 'single-cpm_investor.css', array(), '1.0.0', 'all');
    }
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_enqueue_styles');


//to register the sidebar in widgets for single page

function cpm_investor_register_sidebar() {
    register_sidebar(array(
        'name'          => __('Investor Sidebar', 'textdomain'),
        'id'            => 'investor-sidebar',
        'description'   => __('Widgets in this area will be shown on the single investor pages.', 'textdomain'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'cpm_investor_register_sidebar');
?>
